<?php

include '../includes/db.php';


/* =========================================
   SAVE MODULE
========================================= */

if (isset($_POST['save'])) {

    $course_id = intval($_POST['course_id']);

    $title = mysqli_real_escape_string(
        $conn,
        trim($_POST['title'] ?? '')
    );

    $content = mysqli_real_escape_string(
        $conn,
        trim($_POST['content'] ?? '')
    );

    $video_url = mysqli_real_escape_string(
        $conn,
        trim($_POST['video_url'] ?? '')
    );


    /* =========================================
       FOLDERS
    ========================================= */

    $videoDir = "../learning_material/lecture_videos/";
    $audioDir = "../learning_material/lecture_audio/";
    $imageDir = "../learning_material/course_images/";
    $pdfDir   = "../learning_material/study_pdfs/";


    if (!is_dir($videoDir)) {
        mkdir($videoDir, 0777, true);
    }

    if (!is_dir($audioDir)) {
        mkdir($audioDir, 0777, true);
    }

    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0777, true);
    }

    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }


    /* =========================================
       DEFAULT VALUES
    ========================================= */

    $video = "";
    $audio = "";
    $image = "";
    $pdf = "";


    /* =========================================
       LOCAL VIDEO
    ========================================= */

    if (
        isset($_FILES['video']) &&
        !empty($_FILES['video']['name'])
    ) {

        $video =
            time() . "_" .
            basename($_FILES['video']['name']);

        move_uploaded_file(
            $_FILES['video']['tmp_name'],
            $videoDir . $video
        );
    }


    /* =========================================
       AUDIO
    ========================================= */

    if (
        isset($_FILES['audio']) &&
        !empty($_FILES['audio']['name'])
    ) {

        $audio =
            time() . "_" .
            basename($_FILES['audio']['name']);

        move_uploaded_file(
            $_FILES['audio']['tmp_name'],
            $audioDir . $audio
        );
    }


    /* =========================================
       IMAGE
    ========================================= */

    if (
        isset($_FILES['image']) &&
        !empty($_FILES['image']['name'])
    ) {

        $image =
            time() . "_" .
            basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $imageDir . $image
        );
    }


    /* =========================================
       PDF
    ========================================= */

    if (
        isset($_FILES['pdf']) &&
        !empty($_FILES['pdf']['name'])
    ) {

        $pdf =
            time() . "_" .
            basename($_FILES['pdf']['name']);

        move_uploaded_file(
            $_FILES['pdf']['tmp_name'],
            $pdfDir . $pdf
        );
    }


    /* =========================================
       SAVE MODULE
    ========================================= */

    $sql = "INSERT INTO modules_content
            (
                course_id,
                title,
                content,
                video,
                video_url,
                audio,
                image,
                pdf
            )
            VALUES
            (
                '$course_id',
                '$title',
                '$content',
                '$video',
                '$video_url',
                '$audio',
                '$image',
                '$pdf'
            )";


    if (mysqli_query($conn, $sql)) {

        echo "<script>

            alert('Module Added Successfully! ✅');

            window.location='add_module.php';

        </script>";

        exit;

    } else {

        echo "<div style='
            margin:20px;
            padding:20px;
            background:#ffe5e5;
            color:#b00000;
            border-radius:10px;
            font-family:Arial;
        '>

            <strong>Database Error:</strong><br><br>

            " . htmlspecialchars(
                mysqli_error($conn)
            ) . "

        </div>";

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Add Learning Module</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet">


<style>

body {

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #f8fafc
        );

    min-height:100vh;

}


.card {

    border:0;

    border-radius:18px;

    overflow:hidden;

}


.card-header {

    padding:25px;

}


.card-body {

    padding:30px;

}


.form-control {

    border-radius:10px;

    padding:12px;

}


.form-control:focus {

    box-shadow:
        0 0 0 3px
        rgba(13,110,253,.15);

}


textarea {

    resize:vertical;

}


.section-title {

    font-weight:700;

    margin-top:25px;

    margin-bottom:10px;

}


.youtube-help {

    background:#f1f5f9;

    padding:12px;

    border-radius:10px;

    margin-top:8px;

}


</style>

</head>


<body>


<div class="container mt-5 mb-5">

<div class="row justify-content-center">

<div class="col-lg-9">


<div class="card shadow-lg">


<!-- =====================================
     HEADER
===================================== -->

<div class="card-header bg-primary text-white">

    <h3 class="mb-1">

        📚 Add Learning Module

    </h3>

    <small>

        Add video, YouTube lecture, audio,
        image and PDF learning material.

    </small>

</div>


<!-- =====================================
     BODY
===================================== -->

<div class="card-body">


<form

    method="POST"

    enctype="multipart/form-data">


<!-- =====================================
     COURSE ID
===================================== -->

<h5 class="section-title">

    1️⃣ Course Information

</h5>


<div class="mb-3">

    <label class="form-label fw-bold">

        Course ID

    </label>

    <input

        type="number"

        name="course_id"

        class="form-control"

        placeholder="Example: 5"

        required>

    <small class="text-muted">

        Enter the ID of the course
        from the courses table.

    </small>

</div>


<!-- =====================================
     MODULE TITLE
===================================== -->

<div class="mb-3">

    <label class="form-label fw-bold">

        Module Name

    </label>

    <input

        type="text"

        name="title"

        class="form-control"

        placeholder="Example: Java Introduction"

        required>

</div>


<!-- =====================================
     CONTENT
===================================== -->

<div class="mb-3">

    <label class="form-label fw-bold">

        Module Description

    </label>

    <textarea

        name="content"

        class="form-control"

        rows="5"

        placeholder="Write lesson explanation..."

        required></textarea>

</div>


<!-- =====================================
     YOUTUBE
===================================== -->

<h5 class="section-title">

    2️⃣ 🎥 YouTube Learning Video

</h5>


<div class="mb-3">

    <label class="form-label fw-bold">

        YouTube Video URL

    </label>


    <input

        type="url"

        name="video_url"

        class="form-control"

        placeholder="https://www.youtube.com/embed/VIDEO_ID">


    <div class="youtube-help">

        <strong>Example:</strong><br>

        <code>
        https://www.youtube.com/embed/LGKniBZoxvc
        </code>

        <br><br>

        <strong>Important:</strong>

        Use the YouTube <b>Embed URL</b>,
        not the normal watch URL.

    </div>

</div>


<!-- =====================================
     LOCAL VIDEO
===================================== -->

<h5 class="section-title">

    3️⃣ 🎬 Local Video

</h5>


<div class="mb-3">

    <label class="form-label fw-bold">

        Upload Video

    </label>

    <input

        type="file"

        name="video"

        class="form-control"

        accept="video/*">

</div>


<!-- =====================================
     AUDIO
===================================== -->

<h5 class="section-title">

    4️⃣ 🎧 Audio Lecture

</h5>


<div class="mb-3">

    <label class="form-label fw-bold">

        Upload Audio

    </label>

    <input

        type="file"

        name="audio"

        class="form-control"

        accept="audio/*">

</div>


<!-- =====================================
     IMAGE
===================================== -->

<h5 class="section-title">

    5️⃣ 🖼️ Learning Image

</h5>


<div class="mb-3">

    <label class="form-label fw-bold">

        Upload Image

    </label>

    <input

        type="file"

        name="image"

        class="form-control"

        accept="image/*">

</div>


<!-- =====================================
     PDF
===================================== -->

<h5 class="section-title">

    6️⃣ 📄 Study Material

</h5>


<div class="mb-4">

    <label class="form-label fw-bold">

        Upload PDF Notes

    </label>

    <input

        type="file"

        name="pdf"

        class="form-control"

        accept=".pdf">

</div>


<!-- =====================================
     BUTTONS
===================================== -->

<div class="d-flex gap-2 flex-wrap">


    <button

        type="submit"

        name="save"

        class="btn btn-success btn-lg">

        ✅ Save Module

    </button>


    <a

        href="manage_courses.php"

        class="btn btn-secondary btn-lg">

        ← Back

    </a>


</div>


</form>


</div>

</div>

</div>

</div>

</div>


</body>

</html>