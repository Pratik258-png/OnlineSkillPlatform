<?php

session_start();

/* Login check */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");

    exit;
}


/* Database */

include '../includes/db.php';


/* Get Courses */

$sql = "SELECT * FROM courses ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Course Catalog - OnlineSkill</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

<link rel="stylesheet" href="../style.css">


<style>

/* =====================================
   BASIC
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
   FLOATING STUDY ICONS
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

    font-size: 45px;

    opacity: 0.07;

    animation:
        floatingIcon
        12s ease-in-out infinite;

}


.study-icon:nth-child(1) {

    top: 12%;

    left: 7%;

}


.study-icon:nth-child(2) {

    top: 25%;

    right: 8%;

    animation-delay: 2s;

}


.study-icon:nth-child(3) {

    bottom: 20%;

    left: 10%;

    animation-delay: 4s;

}


.study-icon:nth-child(4) {

    bottom: 12%;

    right: 15%;

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
   MAIN CONTAINER
===================================== */

.catalog-container {

    position: relative;

    z-index: 2;

    max-width: 1200px;

    margin: auto;

    padding:
        35px 25px 60px;

}


/* =====================================
   NAVBAR
===================================== */

.top-bar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        17px 22px;

    margin-bottom: 40px;

    background:
        rgba(8,18,40,0.78);

    backdrop-filter:
        blur(15px);

    border:
        1px solid
        rgba(0,255,213,0.15);

    border-radius:
        18px;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,0.35);

    animation:
        topBarAnimation
        0.8s ease;

}


@keyframes topBarAnimation {

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


.logo {

    font-size: 23px;

    font-weight: 800;

}


.logo span {

    color:
        #00ffd5;

    text-shadow:
        0 0 15px
        rgba(0,255,213,0.4);

}


.logout-btn {

    color: white;

    text-decoration: none;

    padding:
        10px 22px;

    border-radius:
        50px;

    font-weight: 600;

    background:
        linear-gradient(
            135deg,
            #dc2626,
            #ef4444,
            #991b1b
        );

    transition:
        all 0.3s ease;

}


.logout-btn:hover {

    color: white;

    transform:
        translateY(-3px)
        scale(1.05);

    box-shadow:
        0 10px 25px
        rgba(239,68,68,0.35);

}


/* =====================================
   HERO
===================================== */

.hero-section {

    text-align:
        center;

    margin-bottom:
        35px;

    animation:
        heroAnimation
        1s ease;

}


@keyframes heroAnimation {

    from {

        opacity: 0;

        transform:
            scale(.7)
            translateY(30px);

    }

    to {

        opacity: 1;

        transform:
            scale(1)
            translateY(0);

    }

}


.hero-icon {

    font-size:
        58px;

    display:
        inline-block;

    animation:
        graduationAnimation
        3s ease-in-out infinite;

}


@keyframes graduationAnimation {

    0% {

        transform:
            translateY(0)
            rotate(0deg);

    }

    50% {

        transform:
            translateY(-10px)
            rotate(5deg);

    }

    100% {

        transform:
            translateY(0)
            rotate(0deg);

    }

}


.hero-section h1 {

    font-size:
        36px;

    font-weight:
        800;

    margin:
        8px 0;

}


.hero-section h1 span {

    color:
        #00ffd5;

    text-shadow:
        0 0 20px
        rgba(0,255,213,0.35);

}


.hero-section p {

    color:
        #94a3b8;

    font-size:
        16px;

}


/* =====================================
   SEARCH
===================================== */

.search-container {

    max-width:
        650px;

    margin:
        0 auto 40px;

    position:
        relative;

    animation:
        searchAnimation
        1.2s ease;

}


@keyframes searchAnimation {

    from {

        opacity: 0;

        transform:
            translateY(25px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


.search-input {

    width:
        100%;

    padding:
        16px 55px 16px 22px;

    border:
        1px solid
        rgba(0,255,213,0.20);

    border-radius:
        50px;

    outline:
        none;

    color:
        white;

    background:
        rgba(8,18,40,0.85);

    backdrop-filter:
        blur(10px);

    font-size:
        15px;

    transition:
        .3s ease;

}


.search-input::placeholder {

    color:
        #64748b;

}


.search-input:focus {

    border-color:
        #00ffd5;

    box-shadow:
        0 0 25px
        rgba(0,255,213,0.15);

}


.search-icon {

    position:
        absolute;

    right:
        20px;

    top:
        50%;

    transform:
        translateY(-50%);

    font-size:
        21px;

    pointer-events:
        none;

}


/* =====================================
   COURSE GRID
===================================== */

.course-row {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        25px;

    justify-content:
        center;

}


/* =====================================
   COURSE CARD
===================================== */

.course-column {

    flex:
        1 1 320px;

    max-width:
        370px;

}


.course-card {

    height:
        100%;

    min-height:
        370px;

    position:
        relative;

    padding:
        30px 25px;

    border-radius:
        22px;

    background:
        rgba(8,18,40,0.88);

    backdrop-filter:
        blur(18px);

    border:
        1px solid
        rgba(255,255,255,0.09);

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.40);

    overflow:
        hidden;

    opacity:
        0;

    animation:
        courseAppear
        1s
        cubic-bezier(.17,.67,.32,1.25)
        forwards;

    transition:
        all .4s ease;

}


.course-column:nth-child(1)
.course-card {

    animation-delay:
        .2s;

}


.course-column:nth-child(2)
.course-card {

    animation-delay:
        .4s;

}


.course-column:nth-child(3)
.course-card {

    animation-delay:
        .6s;

}


.course-column:nth-child(4)
.course-card {

    animation-delay:
        .8s;

}


.course-column:nth-child(5)
.course-card {

    animation-delay:
        1s;

}


.course-column:nth-child(6)
.course-card {

    animation-delay:
        1.2s;

}


@keyframes courseAppear {

    from {

        opacity: 0;

        transform:
            translateY(100px)
            scale(.75)
            rotateX(20deg);

    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1)
            rotateX(0);

    }

}


/* TOP COLOR LINE */

.course-card::before {

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
        4px;

    background:
        linear-gradient(
            90deg,
            #00ffd5,
            #00aaff,
            #8b5cf6,
            #ff00aa,
            #00ffd5
        );

    background-size:
        300% 100%;

    animation:
        cardLine
        4s linear infinite;

}


@keyframes cardLine {

    from {

        left:
            -100%;

    }

    to {

        left:
            100%;

    }

}


/* CARD HOVER */

.course-card:hover {

    transform:
        translateY(-12px)
        scale(1.025);

    border-color:
        rgba(0,255,213,0.35);

    box-shadow:
        0 30px 70px
        rgba(0,0,0,0.55);

}


/* =====================================
   COURSE ICON
===================================== */

.course-icon {

    width:
        75px;

    height:
        75px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    margin:
        0 auto 18px;

    border-radius:
        50%;

    font-size:
        34px;

    background:
        linear-gradient(
            135deg,
            rgba(0,255,213,0.18),
            rgba(124,58,237,0.25)
        );

    border:
        1px solid
        rgba(255,255,255,0.10);

    transition:
        all .4s ease;

}


.course-card:hover
.course-icon {

    transform:
        rotate(10deg)
        scale(1.12);

    box-shadow:
        0 0 30px
        rgba(0,255,213,0.15);

}


/* =====================================
   COURSE CONTENT
===================================== */

.course-title {

    color:
        white;

    font-size:
        22px;

    font-weight:
        700;

    text-align:
        center;

    margin-bottom:
        13px;

}


.course-description {

    color:
        #94a3b8;

    font-size:
        14px;

    line-height:
        1.6;

    min-height:
        68px;

}


.course-info {

    display:
        flex;

    justify-content:
        space-between;

    gap:
        10px;

    margin:
        18px 0;

}


.info-box {

    flex:
        1;

    padding:
        10px 7px;

    text-align:
        center;

    border-radius:
        12px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid
        rgba(255,255,255,0.07);

}


.info-label {

    display:
        block;

    color:
        #64748b;

    font-size:
        11px;

    margin-bottom:
        3px;

}


.info-value {

    color:
        #cbd5e1;

    font-size:
        12px;

    font-weight:
        600;

}


/* =====================================
   VIEW COURSE BUTTON
===================================== */

.view-course-btn {

    display:
        block;

    width:
        100%;

    padding:
        12px;

    border-radius:
        50px;

    text-align:
        center;

    text-decoration:
        none;

    color:
        white;

    font-weight:
        700;

    position:
        relative;

    overflow:
        hidden;

    background:
        linear-gradient(
            135deg,
            #0984e3,
            #00cec9,
            #00b894
        );

    background-size:
        200% 200%;

    animation:
        buttonGradient
        4s ease infinite;

    transition:
        all .3s ease;

}


@keyframes buttonGradient {

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


.view-course-btn::before {

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
            rgba(255,255,255,.55),
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


.view-course-btn:hover {

    color:
        white;

    transform:
        translateY(-4px)
        scale(1.03);

    box-shadow:
        0 12px 30px
        rgba(0,206,201,0.30);

}


/* =====================================
   NO COURSE
===================================== */

.no-course {

    width:
        100%;

    text-align:
        center;

    padding:
        50px 20px;

    border-radius:
        20px;

    background:
        rgba(8,18,40,0.80);

    border:
        1px solid
        rgba(255,255,255,.08);

}


.no-course-icon {

    font-size:
        55px;

    margin-bottom:
        10px;

}


.no-course h3 {

    color:
        white;

}


.no-course p {

    color:
        #94a3b8;

}


/* =====================================
   FOOTER
===================================== */

.footer {

    text-align:
        center;

    margin-top:
        50px;

    color:
        #64748b;

    font-size:
        13px;

}


/* =====================================
   MOBILE
===================================== */

@media(max-width:768px) {

    .catalog-container {

        padding:
            25px 15px 40px;

    }

    .top-bar {

        flex-direction:
            column;

        gap:
            15px;

    }

    .hero-section h1 {

        font-size:
            28px;

    }

    .course-column {

        max-width:
            100%;

    }

}

</style>

</head>


<body>

        


<!-- =====================================
     FLOATING BACKGROUND
===================================== -->

<div class="study-background">

    <div class="study-icon">📚</div>

    <div class="study-icon">💻</div>

    <div class="study-icon">🎓</div>

    <div class="study-icon">📖</div>

    <div class="study-icon">💡</div>

</div>



<div class="catalog-container">


<!-- =====================================
     TOP BAR
===================================== -->

<div class="top-bar">

    <div class="logo">
        🎓 Online<span>Skill</span>
    </div>

    <div class="top-actions">

        <a href="dashboard.php" class="back-btn">
            ← Back
        </a>

        <a href="../logout.php" class="logout-btn">
            🚪 Logout
        </a>

    </div>

</div>



<!-- =====================================
     HERO
===================================== -->

<div class="hero-section">


    <div class="hero-icon">

        📚

    </div>


    <h1>

        Explore

        <span>Courses</span>

    </h1>


    <p>

        Learn new skills, improve your knowledge
        and build your future.

    </p>


</div>



<!-- =====================================
     SEARCH
===================================== -->

<div class="search-container">

    <input
        type="text"
        id="courseSearch"
        class="search-input"
        placeholder="🔍 Search for a course...">

    <span class="search-icon">

        🔎

    </span>

</div>



<!-- =====================================
     COURSE LIST
===================================== -->

<div
    class="course-row"
    id="courseList">


<?php

if (
    $result &&
    mysqli_num_rows($result) > 0
) {

    while (
        $row =
        mysqli_fetch_assoc($result)
    ) {

?>


<div
    class="course-column"
    data-course-name="<?php
        echo strtolower(
            htmlspecialchars(
                $row['course_name']
            )
        );
    ?>">


<div class="course-card">


    <!-- COURSE ICON -->

    <div class="course-icon">

        📘

    </div>


    <!-- COURSE NAME -->

    <h4 class="course-title">

        <?php

        echo htmlspecialchars(
            $row['course_name']
        );

        ?>

    </h4>


    <!-- DESCRIPTION -->

    <p class="course-description">

        <?php

        echo htmlspecialchars(
            $row['description']
        );

        ?>

    </p>


    <!-- INFO -->

    <div class="course-info">


        <div class="info-box">

            <span class="info-label">

                👨‍🏫 Instructor

            </span>

            <span class="info-value">

                <?php

                echo htmlspecialchars(
                    $row['instructor_name']
                );

                ?>

            </span>

        </div>


        <div class="info-box">

            <span class="info-label">

                ⏱️ Duration

            </span>

            <span class="info-value">

                <?php

                echo htmlspecialchars(
                    $row['duration']
                );

                ?>

            </span>

        </div>


    </div>


    <!-- VIEW COURSE -->

    <a
        href="course_view.php?id=<?php
            echo $row['id'];
        ?>"
        class="view-course-btn">

        🚀 View Course →

    </a>


</div>


</div>


<?php

    }

} else {

?>


<div class="no-course">


    <div class="no-course-icon">

        📚

    </div>


    <h3>

        No Courses Available

    </h3>


    <p>

        New learning courses will appear here soon.

    </p>


</div>


<?php

}

?>


</div>



<!-- =====================================
     FOOTER
===================================== -->

<div class="footer">

    🎓 OnlineSkill Certification Platform

    <br>

    Learn • Practice • Track • Certify

</div>


</div>



<!-- =====================================
     SEARCH SCRIPT
===================================== -->

<script>

const searchInput =
    document.getElementById("courseSearch");

const courseColumns =
    document.querySelectorAll(".course-column");


searchInput.addEventListener(
    "input",
    function () {

        const searchValue =
            this.value
                .toLowerCase()
                .trim();


        courseColumns.forEach(
            function (course) {

                const courseName =
                    course.dataset.courseName;


                if (
                    courseName.includes(
                        searchValue
                    )
                ) {

                    course.style.display =
                        "";

                }

                else {

                    course.style.display =
                        "none";

                }

            }
        );

    }
);

</script>


</body>

</html>

