<?php
session_start();

require_once "../config.php";
require_once "../includes/db.php";

/* =========================================================
   LOGIN CHECK
   ========================================================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* =========================================================
   GET COURSE ID
   ========================================================= */
$course_id = 0;

if (isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];
}

if (isset($_POST['course_id'])) {
    $course_id = (int)$_POST['course_id'];
}

if ($course_id <= 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "Course ID not found."
        ]);
        exit();
    }
    die("Course ID not found.");
}

/* =========================================================
   GET COURSE DETAILS
   ========================================================= */
$course_stmt = $conn->prepare(
    "SELECT id, course_name, description
     FROM courses
     WHERE id = ?
     LIMIT 1"
);

if (!$course_stmt) {
    die("Course query error: " . $conn->error);
}

$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "Course not found."
        ]);
        exit();
    }
    die("Course not found.");
}

$course = $course_result->fetch_assoc();
$course_name = $course['course_name'];
$course_description = $course['description'] ?? '';
$course_stmt->close();

/* =========================================================
   CHECK ENROLLMENT
   ========================================================= */
$enroll_stmt = $conn->prepare(
    "SELECT id
     FROM enrollments
     WHERE user_id = ?
     AND course_id = ?
     LIMIT 1"
);

if (!$enroll_stmt) {
    die("Enrollment query error: " . $conn->error);
}

$enroll_stmt->bind_param("ii", $user_id, $course_id);
$enroll_stmt->execute();
$enroll_result = $enroll_stmt->get_result();

if ($enroll_result->num_rows === 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "You are not enrolled in this course."
        ]);
        exit();
    }
    die("You are not enrolled in this course.");
}
$enroll_stmt->close();

/* =========================================================
   GEMINI SETTINGS
   ========================================================= */
$gemini_url =
    "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent";

/* =========================================================
   POST REQUESTS
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    /* =====================================================
       GENERATE QUESTIONS
       ===================================================== */
    if (isset($_POST['action']) && $_POST['action'] === 'generate_questions') {

        if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
            echo json_encode([
                "success" => false,
                "message" => "Gemini API key is not configured."
            ]);
            exit();
        }

        $module_stmt = $conn->prepare(
            "SELECT title, content
             FROM modules_content
             WHERE course_id = ?
             ORDER BY id ASC"
        );

        $modules_text = "";

        if ($module_stmt) {
            $module_stmt->bind_param("i", $course_id);
            $module_stmt->execute();
            $module_result = $module_stmt->get_result();

            while ($module = $module_result->fetch_assoc()) {
                $modules_text .=
                    "\nModule: " . $module['title'] .
                    "\nContent: " . substr($module['content'], 0, 1500) .
                    "\n";
            }
            $module_stmt->close();
        }

        if (trim($modules_text) === '') {
            $modules_text =
                "Course Name: " . $course_name .
                "\nCourse Description: " . $course_description;
        }

        if (!isset($_SESSION['viva_question_history'])) {
            $_SESSION['viva_question_history'] = [];
        }

        if (!isset($_SESSION['viva_question_history'][$course_id])) {
            $_SESSION['viva_question_history'][$course_id] = [];
        }

        $previous_questions = $_SESSION['viva_question_history'][$course_id];
        $previous_text = "";

        if (!empty($previous_questions)) {
            $previous_text = "\nDo NOT repeat these previous questions:\n";
            foreach (array_slice($previous_questions, -20) as $old_question) {
                $previous_text .= "- " . $old_question . "\n";
            }
        }

        $random_seed = date("YmdHis") . rand(1000, 9999);

        $prompt = "
You are an AI Viva Examiner for an online learning platform.

Course:
$course_name

Course Description:
$course_description

Course Modules:
$modules_text

Attempt Seed:
$random_seed

$previous_text

Generate EXACTLY 10 different viva questions.

Rules:
1. Questions must be related ONLY to this course.
2. Questions must be based mainly on the course modules.
3. Mix easy, medium and practical questions.
4. Do not ask questions from unrelated subjects.
5. Make every question different.
6. Do not repeat previous questions.
7. Questions should be suitable for a college student.
8. Keep questions short and clear.

Return ONLY valid JSON.

Exact format:
{
  \"questions\": [
    \"Question 1\",
    \"Question 2\",
    \"Question 3\",
    \"Question 4\",
    \"Question 5\",
    \"Question 6\",
    \"Question 7\",
    \"Question 8\",
    \"Question 9\",
    \"Question 10\"
  ]
}
";

        $data = [
            "contents" => [[
                "parts" => [["text" => $prompt]]
            ]]
        ];

        $ch = curl_init($gemini_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-goog-api-key: " . GEMINI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            echo json_encode([
                "success" => false,
                "message" => "Gemini connection error: " . curl_error($ch)
            ]);
            curl_close($ch);
            exit();
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($http_code !== 200) {
            $error_message = $result['error']['message'] ?? "Unknown Gemini API error.";
            echo json_encode([
                "success" => false,
                "message" => "Gemini API Error: " . $error_message
            ]);
            exit();
        }

        $ai_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $ai_text = trim($ai_text);
        $ai_text = preg_replace('/^```json\s*/', '', $ai_text);
        $ai_text = preg_replace('/\s*```$/', '', $ai_text);

        $questions_data = json_decode(trim($ai_text), true);

        if (!$questions_data || !isset($questions_data['questions']) || !is_array($questions_data['questions'])) {
            echo json_encode([
                "success" => false,
                "message" => "AI returned invalid questions."
            ]);
            exit();
        }

        $questions = [];
        foreach ($questions_data['questions'] as $question) {
            $question = trim((string)$question);
            if ($question !== '') {
                $questions[] = $question;
            }
        }

        $questions = array_slice($questions, 0, 10);

        if (count($questions) < 10) {
            echo json_encode([
                "success" => false,
                "message" => "AI did not generate 10 questions."
            ]);
            exit();
        }

        $_SESSION['viva_question_history'][$course_id] = array_slice(
            array_merge($_SESSION['viva_question_history'][$course_id], $questions),
            -30
        );

        $_SESSION['current_viva'] = [
            "course_id" => $course_id,
            "questions" => $questions,
            "scores" => [],
            "started_at" => time()
        ];

        echo json_encode([
            "success" => true,
            "questions" => $questions,
            "course_name" => $course_name
        ]);
        exit();
    }

    /* =====================================================
       EVALUATE ANSWER
       ===================================================== */
    if (isset($_POST['action']) && $_POST['action'] === 'evaluate_answer') {

        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');

        if ($question === '' || $answer === '') {
            echo json_encode([
                "success" => false,
                "message" => "Question or answer is missing."
            ]);
            exit();
        }

        if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
            echo json_encode([
                "success" => false,
                "message" => "Gemini API key is not configured."
            ]);
            exit();
        }

        $prompt = "
You are an AI Viva Examiner.

Course:
$course_name

Question:
$question

Student Answer:
$answer

Evaluate the student's answer as a college viva examiner.

Give:
1. Score out of 10
2. Status: EXACTLY one of \"Correct\", \"Partially Correct\", or \"Incorrect\"
3. Short feedback
4. One improvement suggestion

Scoring guide:
- 0-2: Completely wrong, irrelevant, or no meaningful understanding.
- 3-4: Very limited understanding; major errors or missing key points.
- 5-6: Partially correct understanding; some important points are present.
- 7-8: Mostly correct and clear answer with minor missing details.
- 9: Very strong answer with almost all important points.
- 10: Fully correct, technically accurate, complete and well explained.

Important:
- Give a fair score based on the actual meaning of the answer.
- Do not require exact wording.
- Accept technically correct answers written in simple English.
- Do not mark a partially correct answer as Correct.
- Score MUST be between 0 and 10.

Return ONLY valid JSON in this exact format:
{
  \"score\": 0,
  \"status\": \"Correct\",
  \"feedback\": \"Short feedback\",
  \"improvement\": \"One improvement suggestion\"
}
";

        $data = [
            "contents" => [[
                "parts" => [["text" => $prompt]]
            ]]
        ];

        $ch = curl_init($gemini_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-goog-api-key: " . GEMINI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            echo json_encode([
                "success" => false,
                "message" => "Gemini connection error: " . curl_error($ch)
            ]);
            curl_close($ch);
            exit();
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($http_code !== 200) {
            $error_message = $result['error']['message'] ?? "Unknown Gemini API error.";
            echo json_encode([
                "success" => false,
                "message" => "Gemini API Error: " . $error_message
            ]);
            exit();
        }

        $ai_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $ai_text = trim($ai_text);
        $ai_text = preg_replace('/^```json\s*/', '', $ai_text);
        $ai_text = preg_replace('/\s*```$/', '', $ai_text);

        $evaluation = json_decode(trim($ai_text), true);

        if (!$evaluation || !isset($evaluation['score'])) {
            echo json_encode([
                "success" => false,
                "message" => "AI returned an invalid evaluation."
            ]);
            exit();
        }

        $score = (float)$evaluation['score'];
        $score = max(0, min(10, $score));

        $status = $evaluation['status'] ?? "Evaluated";
        $feedback = $evaluation['feedback'] ?? "Answer evaluated.";
        $improvement = $evaluation['improvement'] ?? "Try to explain the concept with more detail.";

        echo json_encode([
            "success" => true,
            "score" => $score,
            "status" => $status,
            "feedback" => $feedback,
            "improvement" => $improvement
        ]);
        exit();
    }

    /* =====================================================
       SAVE FINAL VIVA RESULT
       ===================================================== */
    if (isset($_POST['action']) && $_POST['action'] === 'save_result') {

        $attempted = (int)($_POST['attempted_questions'] ?? 0);
        $total_score = (float)($_POST['total_score'] ?? 0);
        $correct_answers = (int)($_POST['correct_answers'] ?? 0);

        $attempted = max(0, min(10, $attempted));
        $correct_answers = max(0, min(10, $correct_answers));
        $total_score = max(0, min(100, $total_score));

        /* =================================================
           FINAL MARKING RULE
           10 questions x 10 marks = 100 marks.
           Percentage = marks obtained out of 100.
           35 or more marks = PASS.
           ================================================= */
        $percentage = $total_score;
        $status = "FAIL";

        if ($attempted >= 10 && $total_score >= 35) {
            $status = "PASS";
        }

        /* Create table if it does not exist. */
        $create_table = "
        CREATE TABLE IF NOT EXISTS viva_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            total_questions INT DEFAULT 10,
            attempted_questions INT DEFAULT 0,
            correct_answers INT DEFAULT 0,
            total_score DECIMAL(5,2) DEFAULT 0,
            percentage DECIMAL(5,2) DEFAULT 0,
            status VARCHAR(20) DEFAULT 'PENDING',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (!$conn->query($create_table)) {
            echo json_encode([
                "success" => false,
                "message" => "Could not create/check viva_results table: " . $conn->error
            ]);
            exit();
        }

        /* Add missing correct_answers column for old tables. */
        $column_check = $conn->query(
            "SHOW COLUMNS FROM viva_results LIKE 'correct_answers'"
        );

        if ($column_check && $column_check->num_rows === 0) {
            $conn->query(
                "ALTER TABLE viva_results
                 ADD COLUMN correct_answers INT DEFAULT 0
                 AFTER attempted_questions"
            );
        }

        /* Add total_score if an old table does not have it. */
        $score_column_check = $conn->query(
            "SHOW COLUMNS FROM viva_results LIKE 'total_score'"
        );

        if ($score_column_check && $score_column_check->num_rows === 0) {
            $conn->query(
                "ALTER TABLE viva_results
                 ADD COLUMN total_score DECIMAL(5,2) DEFAULT 0
                 AFTER correct_answers"
            );
        }

        /* Add percentage if an old table does not have it. */
        $percentage_column_check = $conn->query(
            "SHOW COLUMNS FROM viva_results LIKE 'percentage'"
        );

        if ($percentage_column_check && $percentage_column_check->num_rows === 0) {
            $conn->query(
                "ALTER TABLE viva_results
                 ADD COLUMN percentage DECIMAL(5,2) DEFAULT 0
                 AFTER total_score"
            );
        }

        $save_stmt = $conn->prepare(
            "INSERT INTO viva_results
            (
                user_id,
                course_id,
                total_questions,
                attempted_questions,
                correct_answers,
                total_score,
                percentage,
                status
            )
            VALUES (?, ?, 10, ?, ?, ?, ?, ?)"
        );

        if (!$save_stmt) {
            echo json_encode([
                "success" => false,
                "message" => "Could not prepare result query: " . $conn->error
            ]);
            exit();
        }

        $save_stmt->bind_param(
            "iiiidds",
            $user_id,
            $course_id,
            $attempted,
            $correct_answers,
            $total_score,
            $percentage,
            $status
        );

        if (!$save_stmt->execute()) {
            echo json_encode([
                "success" => false,
                "message" => "Could not save Viva result: " . $save_stmt->error
            ]);
            $save_stmt->close();
            exit();
        }

        $save_stmt->close();

        echo json_encode([
            "success" => true,
            "status" => $status,
            "total_score" => $total_score,
            "correct_answers" => $correct_answers,
            "percentage" => $percentage,
            "message" => "Viva result saved successfully."
        ]);
        exit();
    }

    echo json_encode([
        "success" => false,
        "message" => "Invalid action."
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Viva - <?php echo htmlspecialchars($course_name); ?></title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #111827;
    color: white;
    padding: 30px;
}

.container { max-width: 850px; margin: auto; }

h1 { text-align: center; margin-bottom: 10px; }

.course-name {
    text-align: center;
    color: #9ca3af;
    margin-bottom: 25px;
    font-size: 17px;
}

.card {
    background: #1f2937;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}

.question {
    background: #374151;
    padding: 22px;
    border-radius: 12px;
    font-size: 22px;
    line-height: 1.5;
    margin-bottom: 20px;
}

textarea {
    width: 100%;
    min-height: 150px;
    padding: 15px;
    border-radius: 10px;
    border: none;
    font-size: 16px;
    resize: vertical;
}

button {
    border: none;
    padding: 13px 22px;
    margin-top: 15px;
    margin-right: 8px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
}

button:hover { opacity: 0.9; }
button:disabled { opacity: 0.5; cursor: not-allowed; }

.start { background: #2563eb; color: white; }
.mic { background: #7c3aed; color: white; }
.submit { background: #16a34a; color: white; }
.next { background: #f59e0b; color: white; }

.back {
    background: #4b5563;
    color: white;
    text-decoration: none;
    display: inline-block;
    padding: 13px 22px;
    margin-top: 15px;
    margin-right: 8px;
    border-radius: 8px;
    font-size: 16px;
}

.certificate-btn {
    display: none;
    background: #c9a227;
    color: white;
    text-decoration: none;
    padding: 15px 28px;
    margin-top: 18px;
    border-radius: 9px;
    font-size: 17px;
    font-weight: bold;
    box-shadow: 0 5px 15px rgba(201,162,39,0.30);
}

.status {
    margin-top: 15px;
    color: #d1d5db;
    line-height: 1.5;
}

.progress {
    margin-top: 10px;
    color: #9ca3af;
    font-size: 14px;
}

.result {
    display: none;
    margin-top: 25px;
    background: #374151;
    padding: 20px;
    border-radius: 12px;
}

.score {
    font-size: 30px;
    font-weight: bold;
    margin-bottom: 10px;
}

.feedback { margin-top: 10px; line-height: 1.6; }

.final-result {
    display: none;
    margin-top: 25px;
    padding: 25px;
    background: #111827;
    border-radius: 14px;
    text-align: center;
}

.final-score {
    font-size: 38px;
    font-weight: bold;
    margin: 15px 0;
}

.result-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin: 20px 0;
}

.summary-box {
    background: #1f2937;
    padding: 15px 10px;
    border-radius: 10px;
}

.summary-box span {
    display: block;
    color: #9ca3af;
    font-size: 13px;
    margin-bottom: 7px;
}

.summary-box strong { font-size: 19px; }
.pass { color: #22c55e; }
.fail { color: #ef4444; }

@media (max-width: 650px) {
    body { padding: 15px; }
    .card { padding: 20px; }
    .question { font-size: 19px; }
    .result-summary { grid-template-columns: 1fr; }
}
</style>
</head>

<body>
<div class="container">

<h1>🤖 AI Viva</h1>

<div class="course-name">
    Course:
    <b><?php echo htmlspecialchars($course_name); ?></b>
</div>

<div class="card">

<div class="question" id="question">
    Click <b>Start Viva</b> to generate your AI viva questions.
</div>

<div class="progress" id="progress"></div>

<button class="start" id="startBtn" onclick="startViva()">
    ▶ Start Viva
</button>

<br>

<textarea id="answer" placeholder="Type your answer here..." disabled></textarea>

<br>

<button class="mic" id="micBtn" onclick="startListening()" disabled>
    🎤 Speak Answer
</button>

<button class="submit" id="submitBtn" onclick="submitAnswer()" disabled>
    🤖 Evaluate Answer
</button>

<div class="status" id="status"></div>

<div class="result" id="result">
    <div class="score" id="score"></div>
    <div id="answerStatus"></div>

    <div class="feedback">
        <b>AI Feedback:</b>
        <p id="feedback"></p>

        <b>Improvement:</b>
        <p id="improvement"></p>
    </div>
</div>

<button class="next" id="nextBtn" onclick="nextQuestion()" style="display:none;">
    ➡ Next Question
</button>

<div class="final-result" id="finalResult">
    <h2>🎉 Viva Completed!</h2>

    <div class="final-score" id="finalScore"></div>

    <div class="result-summary">
        <div class="summary-box">
            <span>Marks Obtained</span>
            <strong id="marksObtained">0 / 100</strong>
        </div>

        <div class="summary-box">
            <span>Percentage</span>
            <strong id="finalPercentage">0%</strong>
        </div>

        <div class="summary-box">
            <span>Passing Marks</span>
            <strong>35 / 100</strong>
        </div>
    </div>

    <div id="finalStatus"></div>
    <p id="finalMessage"></p>

    <a
        id="certificateBtn"
        class="certificate-btn"
        href="../certificates/certificate.php?course_id=<?php echo urlencode($course_id); ?>">
        🎓 Download Certificate
    </a>
</div>

<br>

<a
    class="back"
    href="../certificates/certificate.php?course_id=<?php echo urlencode($course_id); ?>">
    ← Back to Course
</a>

</div>
</div>

<script>
/* =========================================================
   COURSE ID
   ========================================================= */
const courseId = <?php echo (int)$course_id; ?>;

/* =========================================================
   VARIABLES
   ========================================================= */
let questions = [];
let currentQuestion = 0;
let totalScore = 0;
let attemptedQuestions = 0;
let correctAnswers = 0;

/* =========================================================
   START VIVA
   ========================================================= */
async function startViva() {
    const startBtn = document.getElementById("startBtn");
    const status = document.getElementById("status");

    startBtn.disabled = true;
    status.innerText =
        "🤖 AI is preparing a fresh Viva for <?php echo htmlspecialchars($course_name); ?>...";

    const formData = new FormData();
    formData.append("action", "generate_questions");
    formData.append("course_id", courseId);

    try {
        const response = await fetch(
            "ai_viva.php?course_id=" + encodeURIComponent(courseId),
            { method: "POST", body: formData }
        );

        const data = await response.json();

        if (!data.success) {
            alert(data.message || "Could not generate questions.");
            startBtn.disabled = false;
            status.innerText = "";
            return;
        }

        questions = data.questions;
        currentQuestion = 0;
        totalScore = 0;
        attemptedQuestions = 0;
        correctAnswers = 0;

        document.getElementById("answer").disabled = false;
        document.getElementById("micBtn").disabled = false;
        document.getElementById("submitBtn").disabled = false;
        document.getElementById("finalResult").style.display = "none";
        document.getElementById("certificateBtn").style.display = "none";

        showQuestion();
        status.innerText = "✅ Viva started. Answer the question.";

    } catch (error) {
        console.error(error);
        alert("Something went wrong while generating Viva.");
        startBtn.disabled = false;
        status.innerText = "";
    }
}

/* =========================================================
   SHOW QUESTION
   ========================================================= */
function showQuestion() {
    if (currentQuestion >= questions.length) return;

    const questionText = questions[currentQuestion];

    document.getElementById("question").innerHTML =
        "🤖 AI: " + escapeHtml(questionText);

    document.getElementById("answer").value = "";
    document.getElementById("result").style.display = "none";
    document.getElementById("nextBtn").style.display = "none";

    document.getElementById("progress").innerText =
        "Question " + (currentQuestion + 1) + " of " + questions.length +
        " • Each Question: 10 Marks";

    speakQuestion(questionText);
}

/* =========================================================
   SPEAK QUESTION
   ========================================================= */
function speakQuestion(text) {
    if (!("speechSynthesis" in window)) return;

    window.speechSynthesis.cancel();

    const speech = new SpeechSynthesisUtterance(text);
    speech.lang = "en-IN";
    speech.rate = 0.95;
    speech.pitch = 1;

    window.speechSynthesis.speak(speech);
}

/* =========================================================
   SPEECH RECOGNITION
   ========================================================= */
function startListening() {
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        alert("Speech recognition is not supported. Please use Google Chrome.");
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = "en-IN";
    recognition.continuous = false;
    recognition.interimResults = false;

    document.getElementById("status").innerText =
        "🎤 Listening... Speak your answer.";

    recognition.start();

    recognition.onresult = function(event) {
        const text = event.results[0][0].transcript;
        document.getElementById("answer").value = text;
        document.getElementById("status").innerText =
            "✅ Voice converted to text. Click Evaluate Answer.";
    };

    recognition.onerror = function(event) {
        document.getElementById("status").innerText =
            "❌ Microphone error: " + event.error;
    };
}

/* =========================================================
   EVALUATE ANSWER
   ========================================================= */
async function submitAnswer() {
    const answer = document.getElementById("answer").value.trim();

    if (answer === "") {
        alert("Please answer the question first.");
        return;
    }

    const question = questions[currentQuestion];

    document.getElementById("status").innerText =
        "🤖 AI is evaluating your answer...";

    document.getElementById("submitBtn").disabled = true;

    const formData = new FormData();
    formData.append("action", "evaluate_answer");
    formData.append("course_id", courseId);
    formData.append("question", question);
    formData.append("answer", answer);

    try {
        const response = await fetch(
            "ai_viva.php?course_id=" + encodeURIComponent(courseId),
            { method: "POST", body: formData }
        );

        const data = await response.json();

        if (!data.success) {
            alert(data.message || "AI evaluation failed.");
            document.getElementById("submitBtn").disabled = false;
            return;
        }

        document.getElementById("result").style.display = "block";

        document.getElementById("score").innerText =
            "⭐ Score: " + Number(data.score).toFixed(1) + " / 10";

        document.getElementById("answerStatus").innerText =
            "Status: " + data.status;

        document.getElementById("feedback").innerText = data.feedback;
        document.getElementById("improvement").innerText = data.improvement;

        /* IMPORTANT: final result is based on AI marks, not correct-answer count. */
        totalScore += Number(data.score);
        totalScore = Math.min(100, Math.max(0, totalScore));

        attemptedQuestions++;

        /* Kept only for information/backward compatibility. It does NOT decide PASS. */
        if (String(data.status || "").trim().toLowerCase() === "correct") {
            correctAnswers++;
        }

        document.getElementById("status").innerText =
            "✅ AI evaluation completed. Marks added: " +
            Number(data.score).toFixed(1) + " / 10";

        document.getElementById("nextBtn").style.display = "inline-block";

    } catch (error) {
        console.error(error);
        alert("Something went wrong while connecting to AI.");
        document.getElementById("submitBtn").disabled = false;
    }
}

/* =========================================================
   NEXT QUESTION
   ========================================================= */
async function nextQuestion() {
    currentQuestion++;

    if (currentQuestion >= questions.length) {
        await finishViva();
        return;
    }

    document.getElementById("submitBtn").disabled = false;
    showQuestion();
}

/* =========================================================
   FINISH VIVA
   ========================================================= */
async function finishViva() {
    document.getElementById("question").innerHTML = "🎉 Viva Completed!";
    document.getElementById("answer").disabled = true;
    document.getElementById("micBtn").disabled = true;
    document.getElementById("submitBtn").disabled = true;
    document.getElementById("nextBtn").style.display = "none";

    /* =====================================================
       MARKS-BASED PASS RULE
       10 questions x 10 marks = 100 marks
       35 marks or more = PASS
       ===================================================== */
    const percentage = totalScore;

    const status = (
        attemptedQuestions >= 10 &&
        totalScore >= 35
    ) ? "PASS" : "FAIL";

    document.getElementById("finalResult").style.display = "block";

    document.getElementById("finalScore").innerText =
        totalScore.toFixed(1) + " / 100 Marks";

    document.getElementById("marksObtained").innerText =
        totalScore.toFixed(1) + " / 100";

    document.getElementById("finalPercentage").innerText =
        percentage.toFixed(1) + "%";

    const finalStatus = document.getElementById("finalStatus");
    finalStatus.innerText = status;

    if (status === "PASS") {
        finalStatus.className = "pass";

        document.getElementById("finalMessage").innerText =
            "🎓 Congratulations! You passed the AI Viva with " +
            totalScore.toFixed(1) +
            " / 100 marks. Your certificate is now unlocked.";

        document.getElementById("certificateBtn").style.display =
            "inline-block";

    } else {
        finalStatus.className = "fail";

        document.getElementById("finalMessage").innerText =
            "❌ You need at least 35 / 100 marks to pass the AI Viva. Please try the Viva again.";

        document.getElementById("certificateBtn").style.display = "none";
    }

    document.getElementById("status").innerText =
        "🏆 Final Viva Result: " +
        totalScore.toFixed(1) +
        " / 100 marks (" +
        percentage.toFixed(1) +
        "%)";

    /* =====================================================
       SAVE RESULT TO DATABASE
       ===================================================== */
    const formData = new FormData();

    formData.append("action", "save_result");
    formData.append("course_id", courseId);
    formData.append("attempted_questions", attemptedQuestions);
    formData.append("correct_answers", correctAnswers);
    formData.append("total_score", totalScore.toFixed(2));

    try {
        const response = await fetch(
            "ai_viva.php?course_id=" + encodeURIComponent(courseId),
            { method: "POST", body: formData }
        );

        const data = await response.json();

        if (!data.success) {
            console.error("Result save failed:", data);
            return;
        }

        /* Use database-confirmed status for the certificate button. */
        if (data.status === "PASS") {
            document.getElementById("certificateBtn").style.display =
                "inline-block";
        } else {
            document.getElementById("certificateBtn").style.display = "none";
        }

        console.log("Viva result saved:", data);

    } catch (error) {
        console.error("Could not save Viva result:", error);
    }
}

/* =========================================================
   HTML ESCAPE
   ========================================================= */
function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
</script>
</body>
</html>
