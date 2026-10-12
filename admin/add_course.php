```php
<?php

include '../includes/db.php';

$message = "";

/* =========================================
   SAVE COURSE
========================================= */

if (isset($_POST['save'])) {

    $course_name = mysqli_real_escape_string(
        $conn,
        $_POST['course_name']
    );

    $instructor_name = mysqli_real_escape_string(
        $conn,
        $_POST['instructor_name']
    );

    $description = mysqli_real_escape_string(
        $conn,
        $_POST['description']
    );

    $duration = mysqli_real_escape_string(
        $conn,
        $_POST['duration']
    );


    /* =====================================
       INSERT COURSE
    ===================================== */

    $sql = "INSERT INTO courses
            (course_name, instructor_name, description, duration)
            VALUES
            ('$course_name',
             '$instructor_name',
             '$description',
             '$duration')";


    if (mysqli_query($conn, $sql)) {

        /* SUCCESS */

        echo "<script>
                alert('✅ Course added successfully!');
                window.location.href = 'manage_courses.php';
              </script>";

        exit;

    } else {

        /* ERROR */

        $message = "
        <div class='alert alert-danger'>
            ❌ Course could not be added.<br>
            <strong>Error:</strong> " .
            mysqli_error($conn) .
        "</div>";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add New Course</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: white;

            overflow-x: hidden;


            background:

                radial-gradient(
                    circle at 15% 20%,
                    rgba(0, 255, 200, 0.18),
                    transparent 30%
                ),

                radial-gradient(
                    circle at 85% 80%,
                    rgba(120, 70, 255, 0.20),
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #020617,
                    #07152f,
                    #10153a,
                    #020617
                );

            background-size:
                auto,
                auto,
                400% 400%;

            animation:
                backgroundMove
                14s ease infinite;

        }


        @keyframes backgroundMove {

            0% {
                background-position:
                    0 0,
                    0 0,
                    0% 50%;
            }

            50% {
                background-position:
                    50% 50%,
                    50% 50%,
                    100% 50%;
            }

            100% {
                background-position:
                    0 0,
                    0 0,
                    0% 50%;
            }

        }


        body::before {

            content: "📚  💡  🎓  💻  📖";

            position: fixed;

            width: 100%;

            height: 100%;

            top: 0;

            left: 0;

            display: flex;

            align-items: center;

            justify-content: space-around;

            font-size: 45px;

            opacity: 0.06;

            letter-spacing: 50px;

            pointer-events: none;

            animation:
                floatingIcons
                12s ease-in-out infinite;

            z-index: 0;

        }


        @keyframes floatingIcons {

            0% {
                transform: translateY(20px);
            }

            50% {
                transform: translateY(-30px);
            }

            100% {
                transform: translateY(20px);
            }

        }


        .course-container {

            width: 100%;

            max-width: 800px;

            margin: 70px auto;

            padding: 20px;

            position: relative;

            z-index: 2;

            animation:
                pageEnter
                1s ease forwards;

        }


        @keyframes pageEnter {

            0% {

                opacity: 0;

                transform:
                    translateY(60px)
                    scale(0.92);

            }

            100% {

                opacity: 1;

                transform:
                    translateY(0)
                    scale(1);

            }

        }


        .course-card {

            position: relative;

            background:
                rgba(10, 20, 45, 0.88);

            backdrop-filter:
                blur(18px);

            border:
                1px solid
                rgba(0, 255, 200, 0.20);

            border-radius:
                22px;

            overflow: hidden;

            box-shadow:

                0 25px 70px
                rgba(0, 0, 0, 0.55),

                0 0 35px
                rgba(0, 255, 200, 0.08);

            transition:
                all 0.4s ease;

        }


        .course-card:hover {

            transform:
                translateY(-6px);

            box-shadow:

                0 30px 80px
                rgba(0, 0, 0, 0.65),

                0 0 45px
                rgba(0, 255, 200, 0.15);

        }


        .course-card::before {

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
                left: -100%;
            }

            100% {
                left: 100%;
            }

        }


        .card-header {

            border: none;

            padding:
                25px 30px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0, 255, 200, 0.15),
                    rgba(80, 70, 255, 0.18)
                );

            border-bottom:
                1px solid
                rgba(255,255,255,0.08);

        }


        .card-header h3 {

            margin: 0;

            font-size: 28px;

            font-weight: 700;

            color: white;

            letter-spacing: 0.5px;

        }


        .card-body {

            padding:
                35px;

        }


        .form-label {

            color:
                #dbeafe;

            font-weight:
                600;

            margin-bottom:
                8px;

        }


        .form-control {

            background:
                rgba(2, 6, 23, 0.75);

            border:
                1px solid
                rgba(148, 163, 184, 0.25);

            color:
                white;

            border-radius:
                12px;

            padding:
                12px 15px;

            transition:
                all 0.3s ease;

        }


        .form-control::placeholder {

            color:
                #94a3b8;

        }


        .form-control:hover {

            border-color:
                rgba(0,255,200,0.45);

        }


        .form-control:focus {

            background:
                rgba(2, 6, 23, 0.95);

            color:
                white;

            border-color:
                #00ffd5;

            box-shadow:

                0 0 0 3px
                rgba(0,255,213,0.10),

                0 0 20px
                rgba(0,255,213,0.15);

            transform:
                translateY(-2px);

        }


        textarea.form-control {

            resize:
                vertical;

            min-height:
                120px;

        }


        .button-area {

            display:
                flex;

            gap:
                12px;

            flex-wrap:
                wrap;

            margin-top:
                25px;

        }


        .save-btn {

            position:
                relative;

            border:
                none;

            border-radius:
                50px;

            padding:
                13px 30px;

            color:
                white;

            font-weight:
                600;

            background:
                linear-gradient(
                    135deg,
                    #00b894,
                    #00cec9,
                    #0984e3
                );

            background-size:
                200% 200%;

            animation:
                buttonGradient
                4s ease infinite;

            box-shadow:
                0 8px 25px
                rgba(0, 206, 201, 0.30);

            overflow:
                hidden;

            transition:
                all 0.3s ease;

        }


        @keyframes buttonGradient {

            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }

        }


        .save-btn:hover {

            transform:
                translateY(-5px)
                scale(1.04);

            box-shadow:
                0 15px 35px
                rgba(0,206,201,0.45);

        }


        .view-btn {

            border:
                1px solid
                rgba(255,255,255,0.20);

            border-radius:
                50px;

            padding:
                13px 30px;

            color:
                #e2e8f0;

            background:
                rgba(255,255,255,0.06);

            font-weight:
                600;

            transition:
                all 0.3s ease;

        }


        .view-btn:hover {

            color:
                white;

            background:
                rgba(139,92,246,0.25);

            border-color:
                #8b5cf6;

            transform:
                translateY(-5px);

        }


        .btn-icon {

            display:
                inline-block;

            margin-left:
                7px;

        }


        @media (max-width: 600px) {

            .course-container {
                margin: 25px auto;
            }

            .card-body {
                padding: 22px;
            }

            .button-area {
                flex-direction: column;
            }

            .save-btn,
            .view-btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<div class="course-container">

    <div class="course-card">


        <!-- HEADER -->

        <div class="card-header">

            <h3>
                📚 Add New Course
            </h3>

        </div>


        <!-- BODY -->

        <div class="card-body">

            <?php echo $message; ?>


            <form method="POST">


                <!-- COURSE NAME -->

                <div class="mb-3">

                    <label class="form-label">
                        📖 Course Name
                    </label>

                    <input
                        type="text"
                        name="course_name"
                        class="form-control"
                        placeholder="Enter course name"
                        required>

                </div>


                <!-- INSTRUCTOR -->

                <div class="mb-3">

                    <label class="form-label">
                        👨‍🏫 Instructor Name
                    </label>

                    <input
                        type="text"
                        name="instructor_name"
                        class="form-control"
                        placeholder="Enter instructor name"
                        required>

                </div>


                <!-- DESCRIPTION -->

                <div class="mb-3">

                    <label class="form-label">
                        📝 Description
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Enter course description"
                        required></textarea>

                </div>


                <!-- DURATION -->

                <div class="mb-3">

                    <label class="form-label">
                        ⏱️ Duration
                    </label>

                    <input
                        type="text"
                        name="duration"
                        class="form-control"
                        placeholder="Example: 30 Days"
                        required>

                </div>


                <!-- BUTTONS -->

                <div class="button-area">


                    <button
                        type="submit"
                        name="save"
                        class="save-btn">

                        💾 Save Course

                        <span class="btn-icon">
                            ➜
                        </span>

                    </button>


                    <a
                        href="manage_courses.php"
                        class="btn view-btn">

                        📚 View Courses

                        <span class="btn-icon">
                            →
                        </span>

                    </a>


                </div>


            </form>

        </div>

    </div>

</div>


</body>

</html>
