<?php

session_start();

include 'includes/db.php';

/* ==========================================
   LOGIN CHECK
========================================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

/* ==========================================
   GET COURSE ID
========================================== */

$course_id = 0;

if (isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
}

if ($course_id <= 0 && isset($_SESSION['quiz_course_id'])) {
    $course_id = intval($_SESSION['quiz_course_id']);
}

if ($course_id <= 0 && isset($_SESSION['current_course_id'])) {
    $course_id = intval($_SESSION['current_course_id']);
}

if ($course_id <= 0) {
    die("Invalid course ID.");
}

/* ==========================================
   GET COURSE NAME
========================================== */

$course_sql = "
    SELECT course_name
    FROM courses
    WHERE id = $course_id
    LIMIT 1
";

$course_result = mysqli_query($conn, $course_sql);

if (!$course_result) {
    die(
        "COURSE QUERY ERROR: " .
        mysqli_error($conn)
    );
}

if (mysqli_num_rows($course_result) == 0) {
    die("Course not found.");
}

$course_data = mysqli_fetch_assoc($course_result);

$course_name = $course_data['course_name'];

$course_lower = strtolower(trim($course_name));

/* ==========================================
   GET CORRECT ANSWERS FROM SESSION
========================================== */

$correct_answers = [];

if (
    isset($_SESSION['quiz_answers']) &&
    is_array($_SESSION['quiz_answers'])
) {
    $correct_answers = $_SESSION['quiz_answers'];
}

/* ==========================================
   CHECK ANSWERS
========================================== */

$score = 0;
$total_questions = count($correct_answers);

$wrong_answers = [];
$unanswered = [];

if ($total_questions > 0) {

    foreach ($correct_answers as $question_key => $correct_answer) {

        $user_answer = "";

        if (isset($_POST[$question_key])) {
            $user_answer = trim($_POST[$question_key]);
        }

        /* UNANSWERED */

        if ($user_answer === "") {

            $unanswered[] = $question_key;

            $wrong_answers[] = [
                'question' => $question_key,
                'user_answer' => 'Not Answered',
                'correct_answer' => $correct_answer
            ];

            continue;
        }

        /* CORRECT */

        if (
            strtolower(trim($user_answer)) ===
            strtolower(trim($correct_answer))
        ) {

            $score++;

        }
        else {

            $wrong_answers[] = [
                'question' => $question_key,
                'user_answer' => $user_answer,
                'correct_answer' => $correct_answer
            ];
        }
    }
}

/* ==========================================
   CALCULATE PERCENTAGE
========================================== */

if ($total_questions > 0) {

    $percentage =
        ($score / $total_questions) * 100;

}
else {

    $percentage = 0;
}

$percentage = round($percentage, 2);

/* ==========================================
   SAVE RESULT IN DATABASE
========================================== */

$user_id = intval($user_id);
$course_id = intval($course_id);
$score = intval($score);
$percentage = floatval($percentage);

/* CHECK EXISTING RESULT */

$check_result_sql = "
    SELECT id
    FROM results
    WHERE user_id = $user_id
    AND course_id = $course_id
    LIMIT 1
";

$check_result = mysqli_query(
    $conn,
    $check_result_sql
);

if (!$check_result) {

    die(
        "CHECK RESULT ERROR: " .
        mysqli_error($conn)
    );
}

/* UPDATE EXISTING RESULT */

if (mysqli_num_rows($check_result) > 0) {

    $existing = mysqli_fetch_assoc(
        $check_result
    );

    $result_id = intval(
        $existing['id']
    );

    $update_sql = "
        UPDATE results
        SET
            score = $score,
            percentage = $percentage
        WHERE id = $result_id
    ";

    $update_result = mysqli_query(
        $conn,
        $update_sql
    );

    if (!$update_result) {

        die(
            "UPDATE RESULT ERROR: " .
            mysqli_error($conn)
        );
    }

}

/* INSERT NEW RESULT */

else {

    $insert_sql = "
        INSERT INTO results
        (
            user_id,
            course_id,
            score,
            percentage
        )
        VALUES
        (
            $user_id,
            $course_id,
            $score,
            $percentage
        )
    ";

    $insert_result = mysqli_query(
        $conn,
        $insert_sql
    );

    if (!$insert_result) {

        die(
            "INSERT RESULT ERROR: " .
            mysqli_error($conn)
        );
    }
}

/* ==========================================
   VERIFY RESULT
========================================== */

$verify_sql = "
    SELECT
        id,
        user_id,
        course_id,
        score,
        percentage
    FROM results
    WHERE user_id = $user_id
    AND course_id = $course_id
    LIMIT 1
";

$verify_result = mysqli_query(
    $conn,
    $verify_sql
);

if (!$verify_result) {

    die(
        "VERIFY RESULT ERROR: " .
        mysqli_error($conn)
    );
}

if (mysqli_num_rows($verify_result) == 0) {

    die(
        "ERROR: Quiz result was not saved in database."
    );
}

/* ==========================================
   AUTOMATIC STUDENT SKILL UPDATE
========================================== */

$skill_score_value = floatval($percentage);

$skill_name = "";

/* PYTHON */

if (
    strpos($course_lower, 'python') !== false
) {

    $skill_name = "Python Basics";
}

/* MYSQL / SQL */

elseif (
    strpos($course_lower, 'mysql') !== false ||
    strpos($course_lower, 'sql') !== false
) {

    $skill_name = "MySQL";
}

/* JAVA */

elseif (
    strpos($course_lower, 'java') !== false
) {

    $skill_name = "Java Basics";
}

/* HTML */

elseif (
    strpos($course_lower, 'html') !== false
) {

    $skill_name = "HTML";
}

/* CSS */

elseif (
    strpos($course_lower, 'css') !== false
) {

    $skill_name = "CSS";
}

/* JAVASCRIPT */

elseif (
    strpos($course_lower, 'javascript') !== false ||
    strpos($course_lower, 'js') !== false
) {

    $skill_name = "JavaScript";
}

/* FLASK */

elseif (
    strpos($course_lower, 'flask') !== false
) {

    $skill_name = "Flask";
}

/* REST API */

elseif (
    strpos($course_lower, 'rest api') !== false ||
    strpos($course_lower, 'api') !== false
) {

    $skill_name = "REST API";
}

/* ==========================================
   SKILL LEVEL
========================================== */

$skill_level = "Beginner";

if ($skill_score_value >= 80) {

    $skill_level = "Advanced";

}
elseif ($skill_score_value >= 60) {

    $skill_level = "Intermediate";

}
else {

    $skill_level = "Beginner";
}

/* ==========================================
   SAVE / UPDATE STUDENT SKILL
========================================== */

if ($skill_name !== "") {

    $safe_skill_name =
        mysqli_real_escape_string(
            $conn,
            $skill_name
        );

    $safe_skill_level =
        mysqli_real_escape_string(
            $conn,
            $skill_level
        );

    /* CHECK SKILL */

    $check_skill_sql = "
        SELECT id
        FROM student_skills
        WHERE user_id = $user_id
        AND skill_name = '$safe_skill_name'
        LIMIT 1
    ";

    $check_skill = mysqli_query(
        $conn,
        $check_skill_sql
    );

    if (!$check_skill) {

        die(
            "CHECK SKILL ERROR: " .
            mysqli_error($conn)
        );
    }

    /* UPDATE SKILL */

    if (mysqli_num_rows($check_skill) > 0) {

        $existing_skill =
            mysqli_fetch_assoc(
                $check_skill
            );

        $skill_id =
            intval(
                $existing_skill['id']
            );

        $update_skill_sql = "
            UPDATE student_skills
            SET
                skill_score = $skill_score_value,
                skill_level = '$safe_skill_level',
                source = 'Quiz'
            WHERE id = $skill_id
        ";

        $update_skill =
            mysqli_query(
                $conn,
                $update_skill_sql
            );

        if (!$update_skill) {

            die(
                "UPDATE SKILL ERROR: " .
                mysqli_error($conn)
            );
        }

    }

    /* INSERT SKILL */

    else {

        $insert_skill_sql = "
            INSERT INTO student_skills
            (
                user_id,
                skill_name,
                skill_score,
                skill_level,
                source
            )
            VALUES
            (
                $user_id,
                '$safe_skill_name',
                $skill_score_value,
                '$safe_skill_level',
                'Quiz'
            )
        ";

        $insert_skill =
            mysqli_query(
                $conn,
                $insert_skill_sql
            );

        if (!$insert_skill) {

            die(
                "INSERT SKILL ERROR: " .
                mysqli_error($conn)
            );
        }
    }
}

/* ==========================================
   GET ALL STUDENT SKILLS
========================================== */

$final_skill_sql = "
    SELECT
        skill_name,
        skill_score,
        skill_level,
        source
    FROM student_skills
    WHERE user_id = $user_id
    ORDER BY id DESC
";

$final_skill_result =
    mysqli_query(
        $conn,
        $final_skill_sql
    );

if (!$final_skill_result) {

    die(
        "FINAL SKILL CHECK ERROR: " .
        mysqli_error($conn)
    );
}

/* ==========================================
   RESULT MESSAGE
========================================== */

if ($percentage >= 80) {

    $message = "Excellent Performance!";

}
elseif ($percentage >= 60) {

    $message = "Good Performance!";

}
elseif ($percentage >= 40) {

    $message = "Keep Improving!";

}
else {

    $message = "You Need More Practice.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Quiz Result - <?php echo htmlspecialchars($course_name); ?>
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2,
            #6dd5ed
        );

    padding: 40px 20px;

}

.container {

    max-width: 900px;

    margin: auto;

}

.result-card {

    background: white;

    border-radius: 25px;

    padding: 40px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.25);

    text-align: center;

}

.icon {

    width: 90px;

    height: 90px;

    margin: auto;

    border-radius: 50%;

    background: #667eea;

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 45px;

}

h1 {

    margin-top: 20px;

    color: #222;

}

.course {

    color: #667eea;

    font-size: 20px;

    font-weight: bold;

}

.message {

    font-size: 20px;

    margin: 15px 0 30px;

    font-weight: bold;

}

.score-box {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin: 30px 0;

}

.score-item {

    background: #f5f7ff;

    border-radius: 18px;

    padding: 25px;

}

.score-number {

    font-size: 32px;

    font-weight: bold;

    color: #667eea;

}

.score-label {

    color: #666;

    margin-top: 8px;

}

.progress {

    width: 100%;

    height: 18px;

    background: #eee;

    border-radius: 20px;

    overflow: hidden;

    margin: 25px 0;

}

.progress-bar {

    height: 100%;

    width: <?php echo min(100, $percentage); ?>%;

    background:
        linear-gradient(
            90deg,
            #667eea,
            #764ba2
        );

    border-radius: 20px;

}

.section {

    margin-top: 35px;

    text-align: left;

}

.section h2 {

    color: #333;

    border-bottom: 2px solid #eee;

    padding-bottom: 10px;

}

.answer {

    background: #f8f9ff;

    padding: 15px;

    border-radius: 12px;

    margin: 12px 0;

}

.correct {

    color: green;

    font-weight: bold;

}

.wrong {

    color: red;

    font-weight: bold;

}

.buttons {

    display: flex;

    justify-content: center;

    gap: 15px;

    flex-wrap: wrap;

    margin-top: 35px;

}

.btn {

    text-decoration: none;

    padding: 14px 25px;

    border-radius: 12px;

    font-weight: bold;

    display: inline-block;

}

.btn-primary {

    background: #667eea;

    color: white;

}

.btn-success {

    background: #28a745;

    color: white;

}

.btn-dark {

    background: #222;

    color: white;

}

.btn:hover {

    opacity: 0.85;

}

.skill-card {

    background: #f5f7ff;

    padding: 20px;

    border-radius: 15px;

    margin: 10px 0;

    text-align: left;

}

.skill-name {

    font-weight: bold;

    font-size: 18px;

}

.skill-info {

    margin-top: 7px;

    color: #555;

}

@media(max-width: 700px) {

    .score-box {

        grid-template-columns: 1fr;

    }

    .result-card {

        padding: 25px;

    }

}

</style>

</head>

<body>

<div class="container">

<div class="result-card">

    <div class="icon">
        ✓
    </div>

    <h1>
        Quiz Completed!
    </h1>

    <div class="course">
        <?php
        echo htmlspecialchars($course_name);
        ?>
    </div>

    <div class="message">
        <?php
        echo $message;
        ?>
    </div>

    <div class="score-box">

        <div class="score-item">

            <div class="score-number">
                <?php echo $score; ?>
            </div>

            <div class="score-label">
                Correct Answers
            </div>

        </div>

        <div class="score-item">

            <div class="score-number">
                <?php echo $total_questions; ?>
            </div>

            <div class="score-label">
                Total Questions
            </div>

        </div>

        <div class="score-item">

            <div class="score-number">
                <?php echo $percentage; ?>%
            </div>

            <div class="score-label">
                Final Score
            </div>

        </div>

    </div>

    <div class="progress">

        <div class="progress-bar"></div>

    </div>

    <!-- ==========================================
         WRONG / UNANSWERED
    =========================================== -->

    <?php if (count($wrong_answers) > 0): ?>

    <div class="section">

        <h2>
            Review Your Answers
        </h2>

        <?php foreach ($wrong_answers as $wrong): ?>

        <div class="answer">

            <strong>
                <?php
                echo strtoupper(
                    htmlspecialchars(
                        $wrong['question']
                    )
                );
                ?>
            </strong>

            <br><br>

            Your Answer:

            <span class="wrong">
                <?php
                echo htmlspecialchars(
                    $wrong['user_answer']
                );
                ?>
            </span>

            <br>

            Correct Answer:

            <span class="correct">
                <?php
                echo htmlspecialchars(
                    $wrong['correct_answer']
                );
                ?>
            </span>

        </div>

        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <!-- ==========================================
         SKILLS
    =========================================== -->

    <?php if (mysqli_num_rows($final_skill_result) > 0): ?>

    <div class="section">

        <h2>
            Your Updated Skills
        </h2>

        <?php while (
            $skill =
            mysqli_fetch_assoc(
                $final_skill_result
            )
        ): ?>

        <div class="skill-card">

            <div class="skill-name">

                <?php
                echo htmlspecialchars(
                    $skill['skill_name']
                );
                ?>

            </div>

            <div class="skill-info">

                Score:
                <strong>
                    <?php
                    echo $skill['skill_score'];
                    ?>%
                </strong>

                &nbsp; | &nbsp;

                Level:
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $skill['skill_level']
                    );
                    ?>
                </strong>

                &nbsp; | &nbsp;

                Source:
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $skill['source']
                    );
                    ?>
                </strong>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

    <?php endif; ?>

    <!-- ==========================================
         BUTTONS
    =========================================== -->

    <div class="buttons">

        <a
            href="quiz.php?course_id=<?php
            echo $course_id;
            ?>"
            class="btn btn-primary">

            Try Again

        </a>

        <a
            href="student/career_intelligence.php"
            class="btn btn-success">

            View Career Intelligence

        </a>

        <a
            href="student/course_content.php?course_id=<?php
            echo $course_id;
            ?>"
            class="btn btn-dark">

            Continue Course

        </a>

    </div>

</div>

</div>

</body>

</html>