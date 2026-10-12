<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';


/* Get Course ID */

if (!isset($_GET['id'])) {

    header("Location: course_catalog.php");

    exit;
}


$course_id = intval($_GET['id']);


/* Get Course */

$sql = "SELECT * FROM courses WHERE id='$course_id'";

$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) == 0) {

    die("Course not found!");

}


$course = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    <?php echo htmlspecialchars($course['course_name']); ?>
    - OnlineSkill
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">


<style>

/* =====================================
   BODY
===================================== */

* {
    box-sizing: border-box;
}


html,
body {

    margin: 0;

    min-height: 100%;

}


body {

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: white;

    overflow-x: hidden;


    background:

        radial-gradient(
            circle at 10% 15%,
            rgba(0,255,213,0.16),
            transparent 25%
        ),

        radial-gradient(
            circle at 90% 80%,
            rgba(124,58,237,0.20),
            transparent 28%
        ),

        linear-gradient(
            135deg,
            #020617,
            #07152f,
            #111827,
            #1e1b4b,
            #020617
        );

    background-size:
        auto,
        auto,
        500% 500%;

    animation:
        backgroundMove 16s ease infinite;

}


@keyframes backgroundMove {

    0% {

        background-position:
            0% 0%,
            100% 100%,
            0% 50%;

    }

    50% {

        background-position:
            100% 100%,
            0% 0%,
            100% 50%;

    }

    100% {

        background-position:
            0% 0%,
            100% 100%,
            0% 50%;

    }

}


/* =====================================
   FLOATING ICONS
===================================== */

.study-background {

    position: fixed;

    inset: 0;

    overflow: hidden;

    pointer-events: none;

    z-index: 0;

}


.study-icon {

    position: absolute;

    font-size: 48px;

    opacity: 0.07;

    animation:
        floatingIcon
        12s ease-in-out infinite;

}


.study-icon:nth-child(1) {

    top: 10%;

    left: 7%;

}


.study-icon:nth-child(2) {

    top: 30%;

    right: 8%;

    animation-delay: 2s;

}


.study-icon:nth-child(3) {

    bottom: 18%;

    left: 10%;

    animation-delay: 4s;

}


.study-icon:nth-child(4) {

    bottom: 10%;

    right: 14%;

    animation-delay: 6s;

}


.study-icon:nth-child(5) {

    top: 55%;

    left: 48%;

    animation-delay: 8s;

}


@keyframes floatingIcon {

    0% {

        transform:
            translateY(30px)
            rotate(0deg);

    }

    50% {

        transform:
            translateY(-40px)
            rotate(12deg);

    }

    100% {

        transform:
            translateY(30px)
            rotate(0deg);

    }

}


/* =====================================
   CONTAINER
===================================== */

.course-container {

    position: relative;

    z-index: 2;

    max-width: 1000px;

    margin: auto;

    padding:
        30px 20px 60px;

}


/* =====================================
   NAVBAR
===================================== */

.course-navbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        17px 22px;

    margin-bottom: 35px;

    border-radius: 18px;

    background:
        rgba(8,18,40,0.82);

    backdrop-filter:
        blur(15px);

    border:
        1px solid
        rgba(0,255,213,0.15);

    box-shadow:
        0 15px 40px
        rgba(0,0,0,0.35);

    animation:
        navbarAnimation
        .8s ease;

}


@keyframes navbarAnimation {

    from {

        opacity: 0;

        transform:
            translateY(-30px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


.brand {

    color: white;

    text-decoration: none;

    font-size: 21px;

    font-weight: 800;

}


.brand span {

    color:
        #00ffd5;

}


.back-btn {

    color: #0f172a;

    background: white;

    text-decoration: none;

    padding:
        9px 20px;

    border-radius:
        50px;

    font-weight:
        600;

}


.back-btn:hover {

    color:
        #0f172a;

    transform:
        translateX(-5px)
        scale(1.04);

    box-shadow:
        0 10px 25px
        rgba(255,255,255,.15);

}


/* =====================================
   MAIN COURSE CARD
===================================== */

.main-course-card {

    position:
        relative;

    overflow:
        hidden;

    padding:
        45px 40px;

    border-radius:
        25px;

    background:
        rgba(8,18,40,0.88);

    backdrop-filter:
        blur(18px);

    border:
        1px solid
        rgba(255,255,255,0.10);

    box-shadow:
        0 25px 70px
        rgba(0,0,0,.50);

    animation:
        courseEnter
        1.2s
        cubic-bezier(.17,.67,.32,1.25);

}


@keyframes courseEnter {

    from {

        opacity:
            0;

        transform:
            translateY(100px)
            scale(.8)
            rotateX(15deg);

    }

    to {

        opacity:
            1;

        transform:
            translateY(0)
            scale(1)
            rotateX(0);

    }

}


/* TOP ANIMATED LINE */

.main-course-card::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    left:
        -100%;

    width:
        100%;

    height:
        5px;

    background:
        linear-gradient(
            90deg,
            #00ffd5,
            #00aaff,
            #8b5cf6,
            #ff00aa,
            #00ffd5
        );

    animation:
        topLine
        4s linear infinite;

}


@keyframes topLine {

    from {

        left:
            -100%;

    }

    to {

        left:
            100%;

    }

}


/* =====================================
   COURSE ICON
===================================== */

.course-icon {

    width:
        105px;

    height:
        105px;

    margin:
        0 auto 20px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        50%;

    font-size:
        50px;

    background:
        linear-gradient(
            135deg,
            rgba(0,255,213,.18),
            rgba(124,58,237,.28)
        );

    border:
        1px solid
        rgba(0,255,213,.20);

    animation:
        courseIconAnimation
        3s ease-in-out infinite;

}


@keyframes courseIconAnimation {

    0% {

        transform:
            translateY(0)
            rotate(0deg);

        box-shadow:
            0 0 0
            rgba(0,255,213,0);

    }

    50% {

        transform:
            translateY(-10px)
            rotate(8deg);

        box-shadow:
            0 0 35px
            rgba(0,255,213,.18);

    }

    100% {

        transform:
            translateY(0)
            rotate(0deg);

        box-shadow:
            0 0 0
            rgba(0,255,213,0);

    }

}


/* =====================================
   COURSE TITLE
===================================== */

.course-title {

    text-align:
        center;

    font-size:
        38px;

    font-weight:
        800;

    margin-bottom:
        12px;

}


.course-title span {

    color:
        #00ffd5;

    text-shadow:
        0 0 20px
        rgba(0,255,213,.25);

}


.course-subtitle {

    text-align:
        center;

    color:
        #94a3b8;

    margin-bottom:
        35px;

}


/* =====================================
   DESCRIPTION
===================================== */

.description-box {

    padding:
        25px;

    margin-bottom:
        25px;

    border-radius:
        18px;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid
        rgba(255,255,255,.08);

}


.description-box h4 {

    color:
        #00ffd5;

    margin-bottom:
        12px;

}


.description-box p {

    color:
        #cbd5e1;

    line-height:
        1.8;

    margin:
        0;

}


/* =====================================
   INFO BOXES
===================================== */

.info-row {

    display:
        flex;

    gap:
        20px;

    margin-bottom:
        30px;

}


.info-card {

    flex:
        1;

    padding:
        22px;

    text-align:
        center;

    border-radius:
        18px;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid
        rgba(255,255,255,.08);

    transition:
        .4s ease;

}


.info-card:hover {

    transform:
        translateY(-7px);

    border-color:
        rgba(0,255,213,.25);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,.25);

}


.info-icon {

    font-size:
        32px;

    margin-bottom:
        8px;

}


.info-label {

    color:
        #64748b;

    font-size:
        12px;

    display:
        block;

    margin-bottom:
        5px;

}


.info-value {

    color:
        white;

    font-weight:
        700;

}


/* =====================================
   LEARNING BENEFITS
===================================== */

.benefits {

    padding:
        25px;

    border-radius:
        18px;

    margin-bottom:
        30px;

    background:
        linear-gradient(
            135deg,
            rgba(0,255,213,.05),
            rgba(124,58,237,.08)
        );

    border:
        1px solid
        rgba(0,255,213,.10);

}


.benefits h4 {

    color:
        #a78bfa;

    margin-bottom:
        18px;

}


.benefit-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap:
        12px;

}


.benefit-item {

    color:
        #cbd5e1;

    padding:
        10px;

    border-radius:
        10px;

    background:
        rgba(255,255,255,.03);

}


.benefit-item span {

    color:
        #00ffd5;

    margin-right:
        7px;

}


/* =====================================
   ENROLL AREA
===================================== */

.enroll-area {

    text-align:
        center;

    padding-top:
        10px;

}


.enroll-text {

    color:
        #94a3b8;

    margin-bottom:
        18px;

}


.enroll-btn {

    display:
        inline-block;

    position:
        relative;

    overflow:
        hidden;

    padding:
        15px 50px;

    border-radius:
        50px;

    color:
        white;

    text-decoration:
        none;

    font-size:
        17px;

    font-weight:
        800;

    background:
        linear-gradient(
            135deg,
            #00b894,
            #00cec9,
            #0984e3,
            #8b5cf6
        );

    background-size:
        250% 250%;

    animation:
        enrollGradient
        4s ease infinite;

    transition:
        .35s ease;

}


@keyframes enrollGradient {

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


.enroll-btn::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    left:
        -120%;

    width:
        60%;

    height:
        100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.60),
            transparent
        );

    transform:
        skewX(-20deg);

    animation:
        buttonShine
        2.5s infinite;

}


@keyframes buttonShine {

    0% {

        left:
            -120%;

    }

    50% {

        left:
            140%;

    }

    100% {

        left:
            140%;

    }

}


.enroll-btn:hover {

    color:
        white;

    transform:
        translateY(-6px)
        scale(1.06);

    box-shadow:
        0 15px 40px
        rgba(0,206,201,.30);

}


/* =====================================
   FOOTER
===================================== */

.footer {

    text-align:
        center;

    margin-top:
        35px;

    color:
        #64748b;

    font-size:
        13px;

}


/* =====================================
   MOBILE
===================================== */

@media(max-width:700px) {

    .course-container {

        padding:
            20px 12px 40px;

    }


    .course-navbar {

        flex-direction:
            column;

        gap:
            15px;

    }


    .main-course-card {

        padding:
            30px 20px;

    }


    .course-title {

        font-size:
            28px;

    }


    .info-row {

        flex-direction:
            column;

    }


    .benefit-grid {

        grid-template-columns:
            1fr;

    }


    .enroll-btn {

        width:
            100%;

    }

}

</style>

</head>


<body>


<!-- =====================================
     FLOATING STUDY BACKGROUND
===================================== -->

<div class="study-background">

    <div class="study-icon">📚</div>

    <div class="study-icon">💻</div>

    <div class="study-icon">🎓</div>

    <div class="study-icon">📖</div>

    <div class="study-icon">💡</div>

</div>



<div class="course-container">


<!-- =====================================
     NAVBAR
===================================== -->

<div class="course-navbar">


    <a
        href="course_catalog.php"
        class="brand">

        🎓 Online<span>Skill</span>

    </a>


    <a
        href="course_catalog.php"
        class="back-btn">

        ← Back to Courses

    </a>


</div>



<!-- =====================================
     COURSE
===================================== -->

<div class="main-course-card">


    <!-- COURSE ICON -->

    <div class="course-icon">

        📘

    </div>


    <!-- TITLE -->

    <h1 class="course-title">

        <?php

        echo htmlspecialchars(
            $course['course_name']
        );

        ?>

    </h1>


    <p class="course-subtitle">

        🚀 Start learning and build your skills

    </p>



    <!-- DESCRIPTION -->

    <div class="description-box">


        <h4>

            📖 Course Description

        </h4>


        <p>

            <?php

            echo htmlspecialchars(
                $course['description']
            );

            ?>

        </p>


    </div>



    <!-- COURSE INFORMATION -->

    <div class="info-row">


        <div class="info-card">


            <div class="info-icon">

                👨‍🏫

            </div>


            <span class="info-label">

                Instructor

            </span>


            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $course['instructor_name']
                );

                ?>

            </div>


        </div>



        <div class="info-card">


            <div class="info-icon">

                ⏱️

            </div>


            <span class="info-label">

                Course Duration

            </span>


            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $course['duration']
                );

                ?>

            </div>


        </div>



        <div class="info-card">


            <div class="info-icon">

                🎓

            </div>


            <span class="info-label">

                Certification

            </span>


            <div class="info-value">

                Available

            </div>


        </div>


    </div>



    <!-- BENEFITS -->

    <div class="benefits">


        <h4>

            ✨ What You Will Get

        </h4>


        <div class="benefit-grid">


            <div class="benefit-item">

                <span>✓</span>

                Course Learning Material

            </div>


            <div class="benefit-item">

                <span>✓</span>

                Practice & Quiz

            </div>


            <div class="benefit-item">

                <span>✓</span>

                Progress Tracking

            </div>


            <div class="benefit-item">

                <span>✓</span>

                Completion Certificate

            </div>


        </div>


    </div>



    <!-- ENROLL AREA -->

    <div class="enroll-area">


        <p class="enroll-text">

            🎯 Ready to start your learning journey?

        </p>


        <!-- ENROLL BUTTON -->

        <a
            href="enroll_course.php?id=<?php echo $course['id']; ?>"
            class="enroll-btn">

            🚀 Enroll Now

        </a>


    </div>


</div>



<!-- FOOTER -->

<div class="footer">

    🎓 OnlineSkill Certification Platform

    <br>

    Learn • Practice • Track • Certify

</div>


</div>


</body>

</html>