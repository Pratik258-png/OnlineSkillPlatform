<?php

session_start();

include '../includes/db.php';

/* Student information */

$name = $_SESSION['name'] ?? 'Student';
$email = $_SESSION['email'] ?? '';

/* Course ID */

$course_id = isset($_GET['course_id'])
    ? intval($_GET['course_id'])
    : 5;


/* Get course */

$course_query = mysqli_query(
    $conn,
    "SELECT * FROM courses WHERE id='$course_id'"
);

$course = mysqli_fetch_assoc($course_query);

$course_name = $course['course_name'] ?? 'Java & HTML Programming';


/* Student ID */

$user_id = $_SESSION['user_id'] ?? 1;


/* Get latest quiz result */

$result_query = mysqli_query(
    $conn,
    "SELECT * FROM results
     WHERE user_id='$user_id'
     AND course_id='$course_id'
     ORDER BY id DESC
     LIMIT 1"
);

$result_data = mysqli_fetch_assoc($result_query);


/* Score */

$score = 0;
$percentage = 0;

if ($result_data) {

    $score = isset($result_data['score'])
        ? $result_data['score']
        : 0;

    $percentage = ($score / 5) * 100;
}


/* Progress */

$progress = $percentage;


/* Certificate */

$certificate_status =
    ($percentage >= 80)
    ? "Available"
    : "Not Available";


?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>My Progress</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    min-height: 100vh;

    font-family: Arial, sans-serif;

    background:

        radial-gradient(
            circle at 10% 20%,
            rgba(59,130,246,0.18),
            transparent 25%
        ),

        radial-gradient(
            circle at 90% 80%,
            rgba(16,185,129,0.15),
            transparent 25%
        ),

        linear-gradient(
            135deg,
            #020617,
            #0f172a,
            #1e293b
        );

    color: white;

    padding:
        45px 20px;

}


/* MAIN */

.progress-container {

    max-width: 1100px;

    margin: auto;

}


/* HEADER */

.progress-header {

    text-align: center;

    margin-bottom: 35px;

    animation:
        headerAnimation
        1s ease;

}


@keyframes headerAnimation {

    from {

        opacity: 0;

        transform:
            translateY(-40px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


.progress-header h1 {

    font-size: 42px;

    font-weight: 800;

    background:

        linear-gradient(
            90deg,
            #60a5fa,
            #a78bfa,
            #34d399
        );

    -webkit-background-clip: text;

    -webkit-text-fill-color: transparent;

}


.progress-header p {

    color: #94a3b8;

}


/* COURSE CARD */

.course-card {

    background:
        rgba(15,23,42,0.92);

    border:
        1px solid
        rgba(148,163,184,0.20);

    border-radius:
        25px;

    padding:
        35px;

    margin-bottom:
        25px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.35);

    animation:
        cardAnimation
        1s ease;

}


@keyframes cardAnimation {

    from {

        opacity: 0;

        transform:
            scale(0.90)
            translateY(40px);

    }

    to {

        opacity: 1;

        transform:
            scale(1)
            translateY(0);

    }

}


.course-title {

    font-size:
        28px;

    font-weight:
        700;

    color:
        #60a5fa;

}


.student-name {

    color:
        #94a3b8;

}


/* PROGRESS */

.progress-area {

    margin-top:
        25px;

}


.progress {

    height:
        25px;

    background:
        #1e293b;

    border-radius:
        50px;

    overflow:
        hidden;

}


.progress-bar {

    background:

        linear-gradient(
            90deg,
            #2563eb,
            #7c3aed,
            #10b981
        );

    background-size:
        200% 100%;

    animation:
        progressAnimation
        3s ease infinite;

}


@keyframes progressAnimation {

    0% {
        background-position:
            0% 50%;
    }

    50% {
        background-position:
            100% 50%;
    }

    100% {
        background-position:
            0% 50%;
    }

}


/* STAT CARDS */

.stat-card {

    background:
        rgba(30,41,59,0.90);

    border:
        1px solid
        rgba(148,163,184,0.15);

    border-radius:
        20px;

    padding:
        28px 15px;

    text-align:
        center;

    height:
        100%;

    transition:
        all 0.3s ease;

}


.stat-card:hover {

    transform:
        translateY(-8px)
        scale(1.03);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,0.35);

}


.stat-icon {

    font-size:
        40px;

    margin-bottom:
        10px;

}


.stat-number {

    font-size:
        32px;

    font-weight:
        800;

}


.stat-label {

    color:
        #94a3b8;

}


/* STATUS */

.status {

    display:
        inline-block;

    padding:
        8px 18px;

    border-radius:
        50px;

    font-weight:
        700;

}


.status-success {

    background:
        rgba(16,185,129,0.15);

    color:
        #34d399;

}


.status-warning {

    background:
        rgba(245,158,11,0.15);

    color:
        #fbbf24;

}


/* BUTTONS */

.action-btn {

    display:
        inline-block;

    padding:
        13px 28px;

    border-radius:
        50px;

    text-decoration:
        none;

    color:
        white;

    font-weight:
        700;

    margin:
        8px;

    transition:
        all 0.3s ease;

}


.action-btn:hover {

    color:
        white;

    transform:
        translateY(-5px)
        scale(1.04);

}


.quiz-btn {

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

}


.certificate-btn {

    background:
        linear-gradient(
            135deg,
            #d97706,
            #f59e0b
        );

}


.back-btn {

    background:
        linear-gradient(
            135deg,
            #475569,
            #334155
        );

}


.button-area {

    text-align:
        center;

    margin-top:
        35px;

}


/* MOBILE */

@media(max-width:768px) {

    .progress-header h1 {

        font-size:
            32px;

    }

    .course-card {

        padding:
            25px 18px;

    }

}

</style>

</head>


<body>


<div class="progress-container">


    <!-- HEADER -->

    <div class="progress-header">

        <h1>

            📊 My Learning Progress

        </h1>

        <p>

            Track your course progress,
            quiz performance and certificate status.

        </p>

    </div>


    <!-- COURSE -->

    <div class="course-card">


        <div class="course-title">

            📚 <?php echo htmlspecialchars($course_name); ?>

        </div>


        <div class="student-name">

            Student:
            <?php echo htmlspecialchars($name); ?>

        </div>


        <!-- PROGRESS -->

        <div class="progress-area">

            <div class="d-flex justify-content-between mb-2">

                <strong>

                    Course Progress

                </strong>

                <strong>

                    <?php echo round($progress); ?>%

                </strong>

            </div>


            <div class="progress">

                <div
                    class="progress-bar"
                    role="progressbar"
                    style="width: <?php echo $progress; ?>%;">

                </div>

            </div>

        </div>


    </div>


    <!-- STATISTICS -->

    <div class="row g-4">


        <!-- QUIZ -->

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">

                    📝

                </div>

                <div class="stat-number">

                    <?php echo $score; ?>/5

                </div>

                <div class="stat-label">

                    Quiz Score

                </div>

            </div>

        </div>


        <!-- PERCENTAGE -->

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">

                    🎯

                </div>

                <div class="stat-number">

                    <?php echo round($percentage); ?>%

                </div>

                <div class="stat-label">

                    Quiz Percentage

                </div>

            </div>

        </div>


        <!-- CERTIFICATE -->

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">

                    🏆

                </div>


                <div class="mt-2">

                    <?php if ($certificate_status == "Available") { ?>

                        <span class="status status-success">

                            ✓ Available

                        </span>

                    <?php } else { ?>

                        <span class="status status-warning">

                            🔒 Not Available

                        </span>

                    <?php } ?>

                </div>


                <div class="stat-label mt-2">

                    Certificate Status

                </div>

            </div>

        </div>


    </div>


    <!-- BUTTONS -->

    <div class="button-area">


        <a
            href="quiz.php?course_id=<?php echo $course_id; ?>"
            class="action-btn quiz-btn">

            📝 Take Quiz Again

        </a>


        <?php if ($certificate_status == "Available") { ?>

        <a
            href="../certificates/certificate.php?course_id=<?php echo $course_id; ?>"
            class="action-btn certificate-btn">

            🏆 View Certificate

        </a>

        <?php } ?>


        <a
            href="dashboard.php"
            class="action-btn back-btn">

            ← Back to Dashboard

        </a>


    </div>


</div>


</body>

</html>