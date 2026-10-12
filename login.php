<?php

session_start();

include 'includes/db.php';

$error = "";

/* ==========================================
   LOGIN / CREATE USER
========================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /* ==========================================
       VALIDATION
    ========================================== */

    if ($name == "" || $email == "" || $password == "") {

        $error = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $name_safe = mysqli_real_escape_string($conn, $name);
        $email_safe = mysqli_real_escape_string($conn, $email);

        /* ==========================================
           CHECK USER
        ========================================== */

        $sql = "SELECT * FROM users
                WHERE email='$email_safe'
                LIMIT 1";

        $result = mysqli_query($conn, $sql);

        /* ==========================================
           EXISTING USER
        ========================================== */

        if ($result && mysqli_num_rows($result) > 0) {

            $user = mysqli_fetch_assoc($result);

            $user_id = intval($user['id']);

            /* UPDATE NAME */

            $update_sql = "UPDATE users
                           SET name='$name_safe'
                           WHERE id=$user_id";

            mysqli_query($conn, $update_sql);

            /* SESSION */

            $_SESSION['user_id'] = $user_id;
            $_SESSION['student_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            /* DASHBOARD */

            header("Location: student/dashboard.php");
            exit;
        }

        /* ==========================================
           NEW USER
        ========================================== */

        else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert_sql = "INSERT INTO users
                           (name, email, password, role)
                           VALUES
                           ('$name_safe',
                            '$email_safe',
                            '$hashed_password',
                            'student')";

            if (mysqli_query($conn, $insert_sql)) {

                $new_user_id = mysqli_insert_id($conn);

                /* SESSION */

                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['student_id'] = $new_user_id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = "student";

                /* DASHBOARD */

                header("Location: student/dashboard.php");
                exit;

            } else {

                $error = "Unable to create account. "
                       . mysqli_error($conn);
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Login | Online Skill Platform</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet">

<style>

/* =====================================================
   GLOBAL
===================================================== */

* {
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    min-height: 100%;
}

body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    overflow: hidden;

    color: white;

    position: relative;

    background:

        radial-gradient(
            circle at 10% 15%,
            rgba(0, 210, 255, 0.12),
            transparent 25%
        ),

        radial-gradient(
            circle at 90% 85%,
            rgba(124, 58, 237, 0.16),
            transparent 28%
        ),

        linear-gradient(
            135deg,
            #020617 0%,
            #07152f 35%,
            #0b1733 65%,
            #11103a 100%
        );
}


/* =====================================================
   DIGITAL GRID
===================================================== */

body::before {

    content: "";

    position: fixed;

    inset: 0;

    pointer-events: none;

    z-index: 0;

    opacity: 0.30;

    background-image:

        linear-gradient(
            rgba(56,189,248,0.055) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(56,189,248,0.055) 1px,
            transparent 1px
        );

    background-size: 55px 55px;

    mask-image:
        radial-gradient(
            ellipse at center,
            black 20%,
            transparent 78%
        );
}


/* =====================================================
   DIGITAL GLOW
===================================================== */

body::after {

    content: "";

    position: fixed;

    width: 520px;
    height: 520px;

    left: -210px;
    top: -220px;

    border-radius: 50%;

    background:

        radial-gradient(
            circle,
            rgba(0,220,255,0.18),
            rgba(0,220,255,0.05) 40%,
            transparent 70%
        );

    filter: blur(8px);

    animation:
        glowMoveOne
        10s
        ease-in-out
        infinite;

    pointer-events: none;

    z-index: 0;
}


@keyframes glowMoveOne {

    0% {
        transform: translate(0,0) scale(1);
    }

    50% {
        transform: translate(180px,170px) scale(1.25);
    }

    100% {
        transform: translate(0,0) scale(1);
    }
}


/* =====================================================
   ORBS
===================================================== */

.bg-orb {

    position: fixed;

    border-radius: 50%;

    pointer-events: none;

    z-index: 0;

    filter: blur(2px);
}


.orb-one {

    width: 280px;
    height: 280px;

    right: -100px;
    top: 80px;

    background:

        radial-gradient(
            circle,
            rgba(124,58,237,0.22),
            transparent 70%
        );

    animation:
        orbOne
        9s
        ease-in-out
        infinite;
}


.orb-two {

    width: 220px;
    height: 220px;

    left: 5%;
    bottom: -80px;

    background:

        radial-gradient(
            circle,
            rgba(6,182,212,0.15),
            transparent 70%
        );

    animation:
        orbTwo
        8s
        ease-in-out
        infinite;
}


@keyframes orbOne {

    0% {
        transform: translate(0,0) scale(1);
    }

    50% {
        transform: translate(-100px,100px) scale(1.25);
    }

    100% {
        transform: translate(0,0) scale(1);
    }
}


@keyframes orbTwo {

    0% {
        transform: translate(0,0);
    }

    50% {
        transform: translate(100px,-80px);
    }

    100% {
        transform: translate(0,0);
    }
}


/* =====================================================
   AI NETWORK
===================================================== */

.ai-network {

    position: fixed;

    inset: 0;

    pointer-events: none;

    z-index: 1;

    overflow: hidden;
}


.node {

    position: absolute;

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: #38bdf8;

    box-shadow:
        0 0 8px #38bdf8,
        0 0 20px rgba(56,189,248,0.65);

    animation:
        nodePulse
        3s
        ease-in-out
        infinite;
}


.node1 {
    left: 8%;
    top: 22%;
}

.node2 {
    left: 22%;
    top: 12%;
    animation-delay: 0.5s;
}

.node3 {
    left: 31%;
    top: 27%;
    animation-delay: 1s;
}

.node4 {
    right: 9%;
    top: 22%;
    animation-delay: 0.7s;
}

.node5 {
    right: 22%;
    top: 13%;
    animation-delay: 1.2s;
}

.node6 {
    right: 31%;
    top: 28%;
    animation-delay: 1.7s;
}

.node7 {
    left: 12%;
    bottom: 21%;
    animation-delay: 0.8s;
}

.node8 {
    left: 27%;
    bottom: 13%;
    animation-delay: 1.5s;
}

.node9 {
    right: 12%;
    bottom: 21%;
    animation-delay: 0.4s;
}

.node10 {
    right: 27%;
    bottom: 13%;
    animation-delay: 1.8s;
}


@keyframes nodePulse {

    0% {
        transform: scale(1);
        opacity: 0.45;
    }

    50% {
        transform: scale(1.8);
        opacity: 1;
    }

    100% {
        transform: scale(1);
        opacity: 0.45;
    }
}


/* =====================================================
   NETWORK LINES
===================================================== */

.network-line {

    position: absolute;

    height: 1px;

    transform-origin: left center;

    background:

        linear-gradient(
            90deg,
            transparent,
            rgba(56,189,248,0.35),
            transparent
        );

    opacity: 0.35;

    animation:
        linePulse
        4s
        ease-in-out
        infinite;
}


.line1 {
    width: 240px;
    left: 8%;
    top: 22%;
    transform: rotate(-18deg);
}

.line2 {
    width: 230px;
    left: 22%;
    top: 12%;
    transform: rotate(48deg);
}

.line3 {
    width: 260px;
    left: 22%;
    top: 12%;
    transform: rotate(12deg);
}

.line4 {
    width: 240px;
    right: 8%;
    top: 22%;
    transform: rotate(198deg);
}

.line5 {
    width: 230px;
    right: 22%;
    top: 13%;
    transform: rotate(132deg);
}

.line6 {
    width: 260px;
    right: 22%;
    top: 13%;
    transform: rotate(168deg);
}


@keyframes linePulse {

    0% {
        opacity: 0.18;
    }

    50% {
        opacity: 0.55;
    }

    100% {
        opacity: 0.18;
    }
}


/* =====================================================
   PARTICLES
===================================================== */

.particle {

    position: fixed;

    width: 3px;
    height: 3px;

    border-radius: 50%;

    background: #67e8f9;

    box-shadow:
        0 0 10px
        rgba(103,232,249,0.8);

    pointer-events: none;

    z-index: 1;

    animation:
        particleFloat
        8s
        linear
        infinite;
}


.p1 {
    left: 15%;
    bottom: -10px;
}

.p2 {
    left: 35%;
    bottom: -10px;
    animation-delay: 2s;
}

.p3 {
    left: 65%;
    bottom: -10px;
    animation-delay: 4s;
}

.p4 {
    left: 82%;
    bottom: -10px;
    animation-delay: 1s;
}

.p5 {
    left: 50%;
    bottom: -10px;
    animation-delay: 5s;
}


@keyframes particleFloat {

    0% {
        transform: translateY(0);
        opacity: 0;
    }

    20% {
        opacity: 0.8;
    }

    80% {
        opacity: 0.8;
    }

    100% {
        transform:
            translateY(-110vh)
            translateX(70px);

        opacity: 0;
    }
}


/* =====================================================
   LOGIN WRAPPER
===================================================== */

.login-wrapper {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 30px;

    position: relative;

    z-index: 5;
}


/* =====================================================
   LOGIN CARD
===================================================== */

.login-card {

    width: 100%;

    max-width: 470px;

    background:
        rgba(8,18,40,0.94);

    backdrop-filter:
        blur(20px);

    border:
        1px solid
        rgba(0,255,213,0.20);

    border-radius:
        25px;

    overflow:
        hidden;

    box-shadow:

        0 30px 80px
        rgba(0,0,0,0.60),

        0 0 40px
        rgba(0,255,213,0.08);

    /* IMPORTANT FIX */
    opacity: 1;

    position: relative;

    transform-style:
        preserve-3d;
}


/* =====================================================
   TOP LINE
===================================================== */

.login-card::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 4px;

    background:

        linear-gradient(
            90deg,
            #00ffd5,
            #00aaff,
            #8b5cf6,
            #00aaff,
            #00ffd5
        );

    background-size:
        300% 100%;

    animation:
        topLine
        3s linear infinite;
}


@keyframes topLine {

    0% {
        background-position: 0% 50%;
    }

    100% {
        background-position: 300% 50%;
    }
}


/* =====================================================
   HEADER
===================================================== */

.login-header {

    text-align: center;

    padding:
        30px 25px 20px;

    background:

        linear-gradient(
            135deg,
            rgba(0,255,213,0.12),
            rgba(139,92,246,0.14)
        );

    border-bottom:
        1px solid
        rgba(255,255,255,0.08);
}


/* =====================================================
   ICON
===================================================== */

.login-icon {

    width: 75px;
    height: 75px;

    margin:
        0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    font-size: 35px;

    background:

        linear-gradient(
            135deg,
            #00c6ff,
            #7c3aed
        );

    box-shadow:

        0 0 30px
        rgba(0,198,255,0.35);

    animation:
        iconFloat
        3s ease-in-out infinite;
}


@keyframes iconFloat {

    0% {
        transform:
            translateY(0)
            rotate(0deg);
    }

    50% {
        transform:
            translateY(-8px)
            rotate(5deg);
    }

    100% {
        transform:
            translateY(0)
            rotate(0deg);
    }
}


/* =====================================================
   TITLE
===================================================== */

.login-header h3 {

    margin: 0;

    color: white;

    font-size: 28px;

    font-weight: 700;
}


.login-header p {

    margin:
        8px 0 0;

    color:
        #94a3b8;

    font-size:
        14px;
}


/* =====================================================
   BODY
===================================================== */

.login-body {

    padding:
        30px;
}


/* =====================================================
   ERROR
===================================================== */

.alert-danger {

    background:
        rgba(220,38,38,0.15);

    color:
        #fca5a5;

    border:
        1px solid
        rgba(248,113,113,0.30);

    border-radius:
        12px;
}


/* =====================================================
   FORM
===================================================== */

.form-group {

    margin-bottom:
        22px;

    /* IMPORTANT FIX */
    opacity: 1;
}


/* =====================================================
   LABEL
===================================================== */

.form-label {

    color:
        #dbeafe;

    font-weight:
        600;

    margin-bottom:
        8px;
}


/* =====================================================
   INPUT
===================================================== */

.form-control {

    background:
        rgba(2,6,23,0.75);

    border:
        1px solid
        rgba(148,163,184,0.25);

    color:
        white;

    border-radius:
        12px;

    padding:
        13px 15px;

    transition:
        all 0.3s ease;
}


.form-control::placeholder {

    color:
        #64748b;
}


.form-control:hover {

    border-color:
        rgba(0,255,213,0.40);

    transform:
        translateY(-1px);
}


.form-control:focus {

    background:
        rgba(2,6,23,0.95);

    color:
        white;

    border-color:
        #00ffd5;

    outline:
        none;

    box-shadow:

        0 0 0 3px
        rgba(0,255,213,0.08),

        0 0 25px
        rgba(0,255,213,0.18);

    transform:
        translateY(-3px);
}


/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn {

    width: 100%;

    position: relative;

    border: none;

    border-radius: 50px;

    padding: 14px;

    font-size: 17px;

    font-weight: 700;

    color: white;

    background:

        linear-gradient(
            135deg,
            #00b894,
            #00cec9,
            #0984e3,
            #6c5ce7
        );

    background-size:
        300% 300%;

    animation:
        buttonGradient
        5s ease infinite;

    box-shadow:

        0 10px 30px
        rgba(0,206,201,0.30);

    overflow:
        hidden;

    cursor:
        pointer;

    transition:
        all 0.3s ease;
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


.login-btn:hover {

    transform:
        translateY(-5px)
        scale(1.02);

    box-shadow:

        0 18px 40px
        rgba(0,206,201,0.45);
}


.login-btn:active {

    transform:
        scale(0.96);
}


/* =====================================================
   ARROW
===================================================== */

.login-arrow {

    display:
        inline-block;

    margin-left:
        8px;

    transition:
        transform
        0.3s ease;
}


.login-btn:hover .login-arrow {

    transform:
        translateX(7px);
}


/* =====================================================
   QR DIVIDER
===================================================== */

.qr-divider {

    display: flex;

    align-items: center;

    gap: 12px;

    margin:
        24px 0 20px;

    color:
        #94a3b8;

    font-size:
        14px;
}


.qr-divider-line {

    flex: 1;

    height: 1px;

    background:
        rgba(148,163,184,0.25);
}


/* =====================================================
   QR BUTTON
===================================================== */

.qr-login-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 10px;

    width: 100%;

    padding:
        14px 18px;

    border-radius:
        50px;

    background:

        linear-gradient(
            135deg,
            #667eea,
            #764ba2
        );

    color:
        white;

    text-decoration:
        none;

    font-size:
        16px;

    font-weight:
        700;

    box-shadow:

        0 10px 30px
        rgba(102,126,234,0.30);

    transition:
        all 0.3s ease;
}


.qr-login-btn:hover {

    color:
        white;

    transform:
        translateY(-4px)
        scale(1.02);

    box-shadow:

        0 16px 35px
        rgba(102,126,234,0.45);
}


.qr-icon {

    font-size:
        20px;
}


/* =====================================================
   QR DESCRIPTION
===================================================== */

.qr-description {

    font-size:
        12px;

    color:
        #64748b;

    text-align:
        center;

    margin-top:
        10px;

    margin-bottom:
        0;
}


/* =====================================================
   FOOTER
===================================================== */

.login-footer {

    text-align:
        center;

    margin-top:
        20px;

    color:
        #64748b;

    font-size:
        13px;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 600px) {

    .login-wrapper {

        padding:
            18px;
    }

    .login-card {

        max-width:
            100%;
    }

    .login-body {

        padding:
            22px;
    }

    .login-header h3 {

        font-size:
            24px;
    }

    .login-icon {

        width:
            65px;

        height:
            65px;

        font-size:
            30px;
    }

    .node {

        display:
            none;
    }

    .network-line {

        display:
            none;
    }
}

</style>

</head>


<body>


<!-- BACKGROUND -->

<div class="bg-orb orb-one"></div>

<div class="bg-orb orb-two"></div>


<!-- AI NETWORK -->

<div class="ai-network">

    <div class="node node1"></div>
    <div class="node node2"></div>
    <div class="node node3"></div>
    <div class="node node4"></div>
    <div class="node node5"></div>
    <div class="node node6"></div>
    <div class="node node7"></div>
    <div class="node node8"></div>
    <div class="node node9"></div>
    <div class="node node10"></div>

    <div class="network-line line1"></div>
    <div class="network-line line2"></div>
    <div class="network-line line3"></div>
    <div class="network-line line4"></div>
    <div class="network-line line5"></div>
    <div class="network-line line6"></div>

</div>


<!-- PARTICLES -->

<div class="particle p1"></div>
<div class="particle p2"></div>
<div class="particle p3"></div>
<div class="particle p4"></div>
<div class="particle p5"></div>


<!-- LOGIN -->

<div class="login-wrapper">

    <div class="login-card">

        <!-- HEADER -->

        <div class="login-header">

            <div class="login-icon">
                🎓
            </div>

            <h3>
                Student Login
            </h3>

            <p>
                Welcome back! Continue your learning journey.
            </p>

        </div>


        <!-- BODY -->

        <div class="login-body">

            <?php if ($error != "") { ?>

                <div class="alert alert-danger">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php } ?>


            <!-- FORM -->

            <form method="POST">

                <!-- NAME -->

                <div class="form-group">

                    <label class="form-label">
                        👤 Student Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Enter your name"
                        autocomplete="name"
                        required>

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label class="form-label">
                        📧 Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label class="form-label">
                        🔐 Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-btn">

                    Login

                    <span class="login-arrow">
                        ➜
                    </span>

                </button>


                <!-- OR -->

                <div class="qr-divider">

                    <div class="qr-divider-line"></div>

                    <span>OR</span>

                    <div class="qr-divider-line"></div>

                </div>


                <!-- QR LOGIN -->

                <a
                    href="student/qr_generate.php"
                    class="qr-login-btn">

                    <span class="qr-icon">
                        📱
                    </span>

                    Login with QR Code

                </a>


                <p class="qr-description">

                    Scan the QR code using your mobile to login securely.

                </p>

            </form>


            <!-- FOOTER -->

            <div class="login-footer">

                🔒 Secure Student Login

            </div>

        </div>

    </div>

</div>


</body>

</html>