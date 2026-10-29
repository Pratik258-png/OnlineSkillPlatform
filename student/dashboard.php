<?php

session_start();

/* ==============================
   LOGIN CHECK
============================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$name  = $_SESSION['name'] ?? 'Student';
$email = $_SESSION['email'] ?? 'student@example.com';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Dashboard - OnlineSkill</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>

/* ==============================
   RESET
============================== */

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

    font-family: Arial, Helvetica, sans-serif;

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


/* ==============================
   BACKGROUND ANIMATION
============================== */

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


/* ==============================
   FLOATING ICONS
============================== */

.study-symbols {

    position: fixed;

    inset: 0;

    pointer-events: none;

    overflow: hidden;

    z-index: 0;
}

.symbol {

    position: absolute;

    font-size: 45px;

    opacity: 0.08;

    animation:
        floatSymbol 12s ease-in-out infinite;
}

.symbol:nth-child(1) {
    top: 10%;
    left: 8%;
}

.symbol:nth-child(2) {
    top: 25%;
    right: 10%;
    animation-delay: 2s;
}

.symbol:nth-child(3) {
    bottom: 20%;
    left: 12%;
    animation-delay: 4s;
}

.symbol:nth-child(4) {
    bottom: 10%;
    right: 15%;
    animation-delay: 6s;
}

.symbol:nth-child(5) {
    top: 50%;
    left: 48%;
    animation-delay: 8s;
}


@keyframes floatSymbol {

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


/* ==============================
   MAIN CONTAINER
============================== */

.dashboard-container {

    position: relative;

    z-index: 2;

    max-width: 1200px;

    margin: auto;

    padding:
        35px 20px 60px;
}


/* ==============================
   TOP BAR
============================== */

.top-bar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        18px 22px;

    margin-bottom: 45px;

    background:
        rgba(8,18,40,0.82);

    backdrop-filter:
        blur(15px);

    border:
        1px solid
        rgba(0,255,213,0.15);

    border-radius: 18px;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,0.35);

    animation:
        topBarEnter .9s ease;
}


@keyframes topBarEnter {

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

    font-size: 24px;

    font-weight: 800;
}

.brand span {

    color: #00ffd5;
}


/* ==============================
   LOGOUT
============================== */

.logout-btn {

    color: white;

    text-decoration: none;

    padding:
        10px 22px;

    border-radius: 50px;

    font-weight: 600;

    background:
        linear-gradient(
            135deg,
            #dc2626,
            #ef4444,
            #991b1b
        );

    transition: .3s ease;
}

.logout-btn:hover {

    color: white;

    transform:
        translateY(-3px)
        scale(1.05);

    box-shadow:
        0 10px 25px
        rgba(239,68,68,.35);
}


/* ==============================
   WELCOME
============================== */

.welcome-section {

    text-align: center;

    margin-bottom: 45px;

    animation:
        welcomeAnimation 1.2s ease;
}


@keyframes welcomeAnimation {

    from {
        opacity: 0;

        transform:
            scale(.75)
            translateY(30px);
    }

    to {
        opacity: 1;

        transform:
            scale(1)
            translateY(0);
    }
}


.welcome-icon {

    font-size: 55px;

    display: inline-block;

    animation:
        graduationFloat 3s ease-in-out infinite;
}


@keyframes graduationFloat {

    0% {
        transform:
            translateY(0)
            rotate(0);
    }

    50% {
        transform:
            translateY(-10px)
            rotate(5deg);
    }

    100% {
        transform:
            translateY(0)
            rotate(0);
    }
}


.welcome-section h2 {

    font-size: 34px;

    font-weight: 700;
}


.welcome-section h2 span {

    color: #00ffd5;

    text-shadow:
        0 0 20px
        rgba(0,255,213,.35);
}


.student-email {

    color: #94a3b8;
}


.student-role {

    color: #cbd5e1;
}


.student-role b {

    color: #8b5cf6;
}


/* ==============================
   CARDS ROW
============================== */

.cards-row {

    display: flex;

    flex-wrap: wrap;

    justify-content: center;

    gap: 25px;
}


/* ==============================
   CARD
============================== */

.dashboard-card {

    flex:
        1 1 260px;

    max-width: 350px;

    min-height: 285px;

    position: relative;

    background:
        rgba(8,18,40,.88);

    backdrop-filter:
        blur(18px);

    border:
        1px solid
        rgba(255,255,255,.10);

    border-radius: 22px;

    overflow: hidden;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,.45);

    transition:
        all .4s ease;
}


/* TOP ANIMATED LINE */

.dashboard-card::before {

    content: "";

    position: absolute;

    top: 0;

    left: -100%;

    width: 100%;

    height: 4px;

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
        cardLine 4s linear infinite;
}


@keyframes cardLine {

    from {
        left: -100%;
    }

    to {
        left: 100%;
    }
}


.dashboard-card:hover {

    transform:
        translateY(-10px)
        scale(1.03);

    box-shadow:
        0 30px 65px
        rgba(0,0,0,.55);
}


/* ==============================
   CARD ANIMATIONS
============================== */

.card-one {

    opacity: 0;

    animation:
        cardFromLeft
        1.2s
        ease
        .2s
        forwards;
}


.card-two {

    opacity: 0;

    animation:
        cardFromBottom
        1.2s
        ease
        .4s
        forwards;
}


.card-three {

    opacity: 0;

    animation:
        cardFromRight
        1.2s
        ease
        .6s
        forwards;
}


.card-four {

    opacity: 0;

    animation:
        cardFromBottom
        1.2s
        ease
        .8s
        forwards;
}


@keyframes cardFromLeft {

    from {

        opacity: 0;

        transform:
            translateX(-400px)
            rotate(-10deg)
            scale(.7);
    }

    to {

        opacity: 1;

        transform:
            translateX(0)
            rotate(0)
            scale(1);
    }
}


@keyframes cardFromRight {

    from {

        opacity: 0;

        transform:
            translateX(400px)
            rotate(10deg)
            scale(.7);
    }

    to {

        opacity: 1;

        transform:
            translateX(0)
            rotate(0)
            scale(1);
    }
}


@keyframes cardFromBottom {

    from {

        opacity: 0;

        transform:
            translateY(300px)
            scale(.7);
    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }
}


/* ==============================
   CARD BODY
============================== */

.card-body {

    padding:
        35px 25px;

    text-align: center;

    min-height: 285px;
}


/* ==============================
   CARD ICON
============================== */

.card-icon {

    width: 75px;

    height: 75px;

    margin:
        0 auto 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    font-size: 34px;

    background:
        linear-gradient(
            135deg,
            rgba(0,255,213,.20),
            rgba(124,58,237,.25)
        );

    border:
        1px solid
        rgba(255,255,255,.10);

    transition: .4s ease;
}


.dashboard-card:hover .card-icon {

    transform:
        rotate(10deg)
        scale(1.12);
}


.dashboard-card h3 {

    font-size: 23px;

    margin-bottom: 10px;
}


.dashboard-card p {

    color: #94a3b8;

    margin-bottom: 22px;
}


/* ==============================
   BUTTON
============================== */

.action-btn {

    display: inline-block;

    border: none;

    border-radius: 50px;

    padding:
        11px 23px;

    color: white;

    font-weight: 600;

    text-decoration: none;

    cursor: pointer;

    transition: .3s ease;
}


.action-btn:hover {

    color: white;

    transform:
        translateY(-4px)
        scale(1.05);
}


/* ==============================
   COURSE BUTTON
============================== */

.course-btn {

    background:
        linear-gradient(
            135deg,
            #0984e3,
            #00cec9,
            #00b894
        );
}


/* ==============================
   PROGRESS BUTTON
============================== */

.progress-btn {

    background:
        linear-gradient(
            135deg,
            #00b894,
            #00cec9,
            #0984e3
        );
}


/* ==============================
   CERTIFICATE BUTTON
============================== */

.certificate-btn {

    background:
        linear-gradient(
            135deg,
            #f59e0b,
            #f97316,
            #eab308
        );
}


/* ==============================
   AI BUTTON
============================== */

.ai-btn {

    background:
        linear-gradient(
            135deg,
            #06b6d4,
            #2563eb,
            #7c3aed
        );
}


/* ==============================
   PROGRESS DETAILS
============================== */

.progress-details {

    max-height: 0;

    opacity: 0;

    overflow: hidden;

    margin-top: 0;

    transition:
        max-height .8s ease,
        opacity .5s ease,
        margin-top .5s ease;
}


.progress-details.show {

    max-height: 500px;

    opacity: 1;

    margin-top: 22px;
}


.progress-title {

    color: #00ffd5;

    font-size: 18px;

    font-weight: 700;

    margin-bottom: 15px;
}


.progress-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 9px 4px;

    border-bottom:
        1px solid
        rgba(255,255,255,.10);

    color: #cbd5e1;

    font-size: 13px;
}


.progress-row strong {

    color: #00ffd5;
}


.progress-bar-box {

    width: 100%;

    height: 11px;

    background:
        #1e293b;

    border-radius: 20px;

    overflow: hidden;

    margin:
        10px 0;
}


.progress-fill {

    width: 60%;

    height: 100%;

    background:
        linear-gradient(
            90deg,
            #00b894,
            #00cec9,
            #0984e3
        );

    border-radius: 20px;
}


/* ==============================
   CERTIFICATE DETAILS
============================== */

.certificate-details {

    max-height: 0;

    opacity: 0;

    overflow: hidden;

    margin-top: 0;

    transition:
        max-height .9s ease,
        opacity .5s ease,
        margin-top .5s ease;
}


.certificate-details.show {

    max-height: 550px;

    opacity: 1;

    margin-top: 22px;
}


.certificate-box {

    padding:
        20px 15px;

    border-radius: 16px;

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,.08),
            rgba(245,158,11,.08)
        );

    border:
        1px solid
        rgba(245,158,11,.35);
}


.certificate-icon {

    font-size: 45px;

    margin-bottom: 8px;
}


.certificate-title {

    color: #fbbf24;

    font-weight: 800;

    font-size: 18px;
}


.certificate-row {

    display: flex;

    justify-content: space-between;

    padding: 8px 0;

    border-bottom:
        1px solid
        rgba(255,255,255,.08);

    font-size: 13px;
}


.certificate-ready {

    color: #22c55e;

    font-weight: 700;
}


.generate-btn {

    display: inline-block;

    margin-top: 15px;

    padding:
        10px 20px;

    border-radius: 50px;

    color: white;

    text-decoration: none;

    font-weight: 700;

    background:
        linear-gradient(
            135deg,
            #f59e0b,
            #ef4444
        );

    transition: .3s ease;
}


.generate-btn:hover {

    color: white;

    transform:
        translateY(-4px)
        scale(1.05);
}


/* ==============================
   FOOTER
============================== */

.dashboard-footer {

    text-align: center;

    margin-top: 50px;

    color: #64748b;

    font-size: 13px;
}


/* ==============================
   MOBILE
============================== */

@media(max-width:768px) {

    .dashboard-container {

        padding:
            20px 15px 40px;
    }

    .top-bar {

        flex-direction: column;

        gap: 15px;
    }

    .welcome-section h2 {

        font-size: 27px;
    }

    .dashboard-card {

        max-width: 100%;
    }

}

</style>

</head>


<body>


<!-- ==============================
     FLOATING ICONS
============================== -->

<div class="study-symbols">

    <div class="symbol">📚</div>

    <div class="symbol">💻</div>

    <div class="symbol">🎓</div>

    <div class="symbol">📖</div>

    <div class="symbol">💡</div>

</div>



<div class="dashboard-container">


<!-- ==============================
     TOP BAR
============================== -->

<div class="top-bar">

    <div class="brand">

        🎓 Online<span>Skill</span>

    </div>


    <a
        href="../logout.php"
        class="logout-btn">

        🚪 Logout

    </a>

</div>



<!-- ==============================
     WELCOME
============================== -->

<div class="welcome-section">

    <div class="welcome-icon">

        🎓

    </div>


    <h2>

        Welcome,

        <span>

            <?php

            echo htmlspecialchars($name);

            ?>

        </span>

        👋

    </h2>


    <p class="student-email">

        <?php

        echo htmlspecialchars($email);

        ?>

    </p>


    <p class="student-role">

        You are logged in as

        <b>Student</b>.

    </p>

</div>



<!-- ==============================
     CARDS
============================== -->

<div class="cards-row">


<!-- ==============================
     COURSES
============================== -->

<div class="dashboard-card card-one">

    <div class="card-body">

        <div class="card-icon">

            📚

        </div>


        <h3>

            Courses

        </h3>


        <p>

            Browse Available Courses

        </p>


        <a
            href="course_catalog.php"
            class="action-btn course-btn">

            📚 View Courses →

        </a>

    </div>

</div>



<!-- ==============================
     MY PROGRESS
============================== -->

<div class="dashboard-card card-two">

    <div class="card-body">

        <div class="card-icon">

            📊

        </div>


        <h3>

            My Progress

        </h3>


        <p>

            Track Learning Progress

        </p>


    
           <a
    href="save.php"
    class="action-btn progress-btn">

    📊 View Progress →

</a>


        <!-- PROGRESS DETAILS -->

        <div
            id="progressDetails"
            class="progress-details">


            <div class="progress-title">

                📈 Learning Progress

            </div>


            <div class="progress-row">

                <span>

                    📚 Course Progress

                </span>

                <strong>

                    60%

                </strong>

            </div>


            <div class="progress-bar-box">

                <div class="progress-fill"></div>

            </div>


            <div class="progress-row">

                <span>

                    📝 Quiz Score

                </span>

                <strong>

                    3 / 5

                </strong>

            </div>


            <div class="progress-row">

                <span>

                    🎯 Quiz Percentage

                </span>

                <strong>

                    60%

                </strong>

            </div>


            <div class="progress-row">

                <span>

                    📖 Learning Status

                </span>

                <strong>

                    In Progress

                </strong>

            </div>


            <a
                href="course_catalog.php"
                class="generate-btn">

                📚 Continue Learning

            </a>

        </div>

    </div>

</div>



<!-- ==============================
     CERTIFICATE
============================== -->

<div class="dashboard-card card-three">

    <div class="card-body">

        <div class="card-icon">

            🏆

        </div>


        <h3>

            Certificates

        </h3>


        <p>

            Download Certificates

        </p>


        <button
            type="button"
            id="certificateButton"
            class="action-btn certificate-btn"
            onclick="showCertificate()">

            🏆 Certificates →

        </button>


        <!-- CERTIFICATE DETAILS -->

        <div
            id="certificateDetails"
            class="certificate-details">


            <div class="certificate-box">


                <div class="certificate-icon">

                    🏆

                </div>


                <div class="certificate-title">

                    Certificate Center

                </div>


                <div class="certificate-row">

                    <span>

                        🎓 Course

                    </span>

                    <strong>

                        Java & HTML

                    </strong>

                </div>


                <div class="certificate-row">

                    <span>

                        📝 Quiz

                    </span>

                    <strong>

                        Completed

                    </strong>

                </div>


                <div class="certificate-row">

                    <span>

                        📊 Score Required

                    </span>

                    <strong>

                        40%+

                    </strong>

                </div>


                <div class="certificate-row">

                    <span>

                        🏆 Status

                    </span>

                    <span class="certificate-ready">

                        ✓ Available

                    </span>

                </div>


                <a
                    href="../certificates/certificate.php"
                    class="generate-btn">

                    🎓 Generate Certificate

                </a>


            </div>

        </div>

    </div>

</div>



<!-- ==============================
     AI IMPORTANT NOTES
============================== -->

<div class="dashboard-card card-four">

    <div class="card-body">

        <div class="card-icon">

            🤖

        </div>


        <h3>

            AI Important Notes

        </h3>


        <p>

            Paste a YouTube study video link
            and generate important notes.

        </p>


        <a
            href="../ai_notes.php"
            class="action-btn ai-btn">

            🤖 Open AI Notes →

        </a>

    </div>

</div>


</div>



<!-- ==============================
     FOOTER
============================== -->

<div class="dashboard-footer">

    📖 Learn • Practice • Track • Certify • AI

</div>


</div>



<script>

/* ==============================
   PROGRESS
============================== */

function showProgress() {

    const details =
        document.getElementById(
            "progressDetails"
        );

    const button =
        document.getElementById(
            "progressButton"
        );


    if (
        details.classList.contains("show")
    ) {

        details.classList.remove("show");

        button.innerHTML =
            "📊 View Progress →";

    }

    else {

        details.classList.add("show");

        button.innerHTML =
            "🙈 Hide Progress ↑";

    }

}


/* ==============================
   CERTIFICATE
============================== */

function showCertificate() {

    const details =
        document.getElementById(
            "certificateDetails"
        );

    const button =
        document.getElementById(
            "certificateButton"
        );


    if (
        details.classList.contains("show")
    ) {

        details.classList.remove("show");

        button.innerHTML =
            "🏆 Certificates →";

    }

    else {

        details.classList.add("show");

        button.innerHTML =
            "🙈 Hide Certificate ↑";

    }

}

</script>


</body>

</html>
