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

/* =========================================================
   CAMERA MONITORING
   ========================================================= */
.camera-panel {
    margin: 20px 0;
    padding: 18px;
    background: #111827;
    border-radius: 14px;
    border: 1px solid #374151;
}

.camera-title {
    font-size: 17px;
    font-weight: bold;
    margin-bottom: 12px;
}

.camera-box {
    position: relative;
    width: 100%;
    max-width: 520px;
    margin: auto;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
}

#cameraVideo {
    width: 100%;
    display: block;
    transform: scaleX(-1);
    background: #000;
}

.camera-status {
    margin-top: 10px;
    color: #d1d5db;
    line-height: 1.5;
}

.camera-warning {
    margin-top: 8px;
    color: #fecaca;
    background: #7f1d1d;
    border: 1px solid #ef4444;
    padding: 10px 12px;
    border-radius: 8px;
    font-weight: bold;
    display: none;
}

.camera-warning:not(:empty) {
    display: block;
}

.tab-switch-alert {
    display: none;
    margin: 0 0 18px 0;
    padding: 16px 18px;
    background: #7f1d1d;
    border: 2px solid #ef4444;
    color: #fee2e2;
    border-radius: 12px;
    font-weight: bold;
    line-height: 1.5;
    box-shadow: 0 0 18px rgba(239,68,68,0.25);
}

.tab-switch-alert .alert-title {
    color: #ffffff;
    font-size: 18px;
    margin-bottom: 5px;
}

.monitoring-summary {
    margin-top: 15px;
    padding: 12px;
    background: #1f2937;
    border-radius: 10px;
    color: #d1d5db;
}

@media (max-width: 650px) {
    body { padding: 15px; }
    .card { padding: 20px; }
    .question { font-size: 19px; }
    .result-summary { grid-template-columns: 1fr; }
}
</style>

<!-- MediaPipe Face Detection for basic face-presence monitoring -->
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/face_detection.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
</head>

<body>
<div class="container">

<h1>🤖 AI Viva</h1>

<div class="course-name">
    Course:
    <b><?php echo htmlspecialchars($course_name); ?></b>
</div>

<div class="card">

<!-- =====================================================
     CAMERA MONITORING
     ===================================================== -->
<div class="camera-panel">
    <div class="camera-title">📷 Viva Camera Monitoring</div>

    <div class="camera-box">
        <video id="cameraVideo" autoplay muted playsinline></video>
    </div>

    <div class="camera-status" id="cameraStatus">
        Camera will start when you click Start Viva.
    </div>

    <div class="camera-warning" id="cameraWarning"></div>

    <div class="monitoring-summary" id="monitoringSummary" style="display:none;">
        Camera Monitoring Warnings: <b id="warningCount">0</b>
    </div>
</div>

<div class="tab-switch-alert" id="tabSwitchAlert">
    <div class="alert-title">🚨 TAB SWITCHING DETECTED</div>
    <div>Viva was terminated because you switched to another tab. Please click <b>Start Viva</b> to begin again.</div>
</div>

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

    <div class="monitoring-summary" id="finalMonitoringSummary">
        📷 Camera Monitoring Warnings: <b id="finalWarningCount">0</b>
    </div>

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
   CAMERA + TAB MONITORING
   ========================================================= */
let cameraStream = null;
let cameraController = null;
let faceDetector = null;
let cameraMonitoringActive = false;
let faceDetectionReady = false;
let faceWarningCount = 0;
let lastCameraWarningTime = 0;
let vivaStarted = false;
let vivaTerminatedByTabSwitch = false;
let vivaSessionId = 0;

/* =========================================================
   START VIVA
   ========================================================= */
async function startViva() {
    const startBtn = document.getElementById("startBtn");
    const status = document.getElementById("status");

    startBtn.disabled = true;
    document.getElementById("tabSwitchAlert").style.display = "none";
    vivaTerminatedByTabSwitch = false;
    vivaSessionId++;
    const thisVivaSession = vivaSessionId;

    status.innerText =
        "📷 Starting camera monitoring...";

    const cameraStarted = await startCameraMonitoring();

    if (!cameraStarted) {
        startBtn.disabled = false;
        vivaStarted = false;
        status.innerText = "❌ Camera permission is required to start the Viva.";
        return;
    }

    vivaStarted = true;

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

        if (!vivaStarted || vivaTerminatedByTabSwitch || thisVivaSession !== vivaSessionId) {
            return;
        }

        if (!data.success) {
            stopCameraMonitoring();
            vivaStarted = false;
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
        faceWarningCount = 0;
        document.getElementById("warningCount").innerText = "0";
        document.getElementById("monitoringSummary").style.display = "block";

        document.getElementById("answer").disabled = false;
        document.getElementById("micBtn").disabled = false;
        document.getElementById("submitBtn").disabled = false;
        document.getElementById("finalResult").style.display = "none";
        document.getElementById("certificateBtn").style.display = "none";

        showQuestion();
        status.innerText = "✅ Viva started. Answer the question.";

    } catch (error) {
        console.error(error);
        stopCameraMonitoring();
        vivaStarted = false;
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
    const currentSessionId = vivaSessionId;

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

        if (!vivaStarted || vivaTerminatedByTabSwitch || currentSessionId !== vivaSessionId) {
            return;
        }

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
    if (!vivaStarted || vivaTerminatedByTabSwitch) return;

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

        if (vivaTerminatedByTabSwitch) {
            return;
        }

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

    document.getElementById("warningCount").innerText = faceWarningCount;
    document.getElementById("finalWarningCount").innerText = faceWarningCount;
    document.getElementById("monitoringSummary").style.display = "block";
    stopCameraMonitoring();
    vivaStarted = false;
}

/* =========================================================
   CAMERA MONITORING FUNCTIONS
   ========================================================= */
async function startCameraMonitoring() {
    const video = document.getElementById("cameraVideo");
    const cameraStatus = document.getElementById("cameraStatus");
    const cameraWarning = document.getElementById("cameraWarning");

    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            cameraStatus.innerText = "❌ Camera is not supported by this browser.";
            return false;
        }

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        });

        video.srcObject = cameraStream;
        await video.play();

        cameraStatus.innerText = "✅ Camera active. Face monitoring is running.";
        cameraWarning.innerText = "";
        cameraMonitoringActive = true;
        faceWarningCount = 0;
        lastCameraWarningTime = 0;

        const tracks = cameraStream.getVideoTracks();
        if (tracks.length > 0) {
            tracks[0].onended = function() {
                if (cameraMonitoringActive && vivaStarted && !vivaTerminatedByTabSwitch) {
                    addCameraWarning("⚠️ Camera was turned off or interrupted.");
                    cameraStatus.innerText = "❌ Camera is not active.";
                }
            };
        }

        await startFaceDetection(video);
        return true;

    } catch (error) {
        console.error("Camera error:", error);
        cameraMonitoringActive = false;
        cameraStatus.innerText = "❌ Camera permission denied or camera is unavailable.";
        cameraWarning.innerText = "Please allow camera access and click Start Viva again.";
        return false;
    }
}

async function startFaceDetection(video) {
    if (typeof FaceDetection === "undefined" || typeof Camera === "undefined") {
        faceDetectionReady = false;
        document.getElementById("cameraStatus").innerText =
            "✅ Camera active. Basic camera monitoring is running.";
        return;
    }

    faceDetector = new FaceDetection({
        locateFile: function(file) {
            return "https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/" + file;
        }
    });

    faceDetector.setOptions({
        model: "short",
        minDetectionConfidence: 0.5
    });

    faceDetector.onResults(function(results) {
        if (!cameraMonitoringActive || !vivaStarted || vivaTerminatedByTabSwitch) return;

        const faces = results && results.detections ? results.detections.length : 0;

        if (faces === 0) {
            addCameraWarning("⚠️ No face detected. Please stay in front of the camera.");
        } else if (faces > 1) {
            addCameraWarning("⚠️ Multiple faces detected. Only the Viva student should be visible.");
        } else {
            document.getElementById("cameraWarning").innerText = "";
            document.getElementById("cameraStatus").innerText = "✅ Camera active • 1 face detected.";
        }
    });

    cameraController = new Camera(video, {
        onFrame: async function() {
            if (faceDetector && cameraMonitoringActive) {
                await faceDetector.send({ image: video });
            }
        },
        width: 640,
        height: 480
    });

    faceDetectionReady = true;
    document.getElementById("cameraStatus").innerText = "🔍 Face monitoring active. Checking camera...";
    cameraController.start();
}

function addCameraWarning(message) {
    const now = Date.now();

    /* Avoid counting the same monitoring condition continuously. */
    if (now - lastCameraWarningTime < 3000) return;

    lastCameraWarningTime = now;
    faceWarningCount++;

    document.getElementById("cameraWarning").innerText = message;
    document.getElementById("warningCount").innerText = faceWarningCount;
    document.getElementById("cameraStatus").innerText =
        "⚠️ Monitoring warning detected.";
}

function stopCameraMonitoring() {
    cameraMonitoringActive = false;
    faceDetectionReady = false;

    if (cameraController && typeof cameraController.stop === "function") {
        try {
            cameraController.stop();
        } catch (e) {}
    }
    cameraController = null;

    if (faceDetector && typeof faceDetector.close === "function") {
        try {
            faceDetector.close();
        } catch (e) {}
    }
    faceDetector = null;

    if (cameraStream) {
        cameraStream.getTracks().forEach(function(track) {
            track.stop();
        });
    }
    cameraStream = null;

    const video = document.getElementById("cameraVideo");
    if (video) video.srcObject = null;

    document.getElementById("cameraStatus").innerText = "Camera monitoring stopped.";
}

/* =========================================================
   TAB SWITCH = RESTART VIVA
   ========================================================= */
document.addEventListener("visibilitychange", function() {
    if (document.hidden && vivaStarted && !vivaTerminatedByTabSwitch) {
        vivaTerminatedByTabSwitch = true;
        vivaSessionId++;
        terminateVivaForTabSwitch();
    }
});

function terminateVivaForTabSwitch() {
    vivaStarted = false;

    const tabAlert = document.getElementById("tabSwitchAlert");
    if (tabAlert) {
        tabAlert.style.display = "block";
    }

    questions = [];
    currentQuestion = 0;
    totalScore = 0;
    attemptedQuestions = 0;
    correctAnswers = 0;

    if (window.speechSynthesis) {
        window.speechSynthesis.cancel();
    }

    stopCameraMonitoring();

    document.getElementById("question").innerHTML =
        "⚠️ Viva Restarted<br><small>Tab switching was detected. Your current Viva was terminated. Please click Start Viva to begin again.</small>";

    document.getElementById("progress").innerText = "";
    document.getElementById("answer").value = "";
    document.getElementById("answer").disabled = true;
    document.getElementById("micBtn").disabled = true;
    document.getElementById("submitBtn").disabled = true;
    document.getElementById("nextBtn").style.display = "none";
    document.getElementById("result").style.display = "none";
    document.getElementById("finalResult").style.display = "none";
    document.getElementById("certificateBtn").style.display = "none";
    document.getElementById("startBtn").disabled = false;
    document.getElementById("monitoringSummary").style.display = "none";

    document.getElementById("cameraWarning").innerText = "";
    document.getElementById("warningCount").innerText = "0";

    document.getElementById("status").innerText =
        "⚠️ Tab switching detected. Current Viva was terminated. Please start Viva again.";
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
