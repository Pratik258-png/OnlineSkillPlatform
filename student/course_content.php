
<?php

include '../includes/db.php';

/* =========================================
   CHECK COURSE ID
========================================= */

if (!isset($_GET['id'])) {
    header("Location: course_catalog.php");
    exit;
}

$course_id = intval($_GET['id']);

/* =========================================
   GET COURSE
========================================= */

$sql = "SELECT * FROM courses WHERE id='$course_id'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Course not found!");
}

$course = mysqli_fetch_assoc($result);

/* =========================================
   GET MODULES
========================================= */

$module_sql = "SELECT * FROM modules_content
               WHERE course_id='$course_id'
               ORDER BY id ASC";

$module_result = mysqli_query($conn, $module_sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($course['course_name']); ?></title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

/* BODY */

body {
    background: linear-gradient(135deg, #eef2ff, #f8fafc);
    min-height: 100vh;
    font-family: Arial, sans-serif;
}

/* PROFESSIONAL BACK BUTTON */

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 11px 22px;
    margin: 20px 0 0 max(20px, calc((100% - 1000px) / 2));
    background: #ffffff;
    color: #1e3a8a;
    border: 1px solid #dbeafe;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.08);
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: #1e3a8a;
    color: #ffffff;
    transform: translateX(-3px);
    box-shadow: 0 6px 18px rgba(30, 58, 138, 0.2);
}

.back-icon {
    font-size: 19px;
    line-height: 1;
}

/* MAIN CONTAINER */

.container-main {
    max-width: 1000px;
    margin: 25px auto 50px;
    padding: 20px;
}

/* COURSE HEADER */

.header {
    background: linear-gradient(135deg, #111827, #1e3a8a);
    color: white;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
}

.header h1 {
    font-weight: 700;
}

/* MODULE */

.module {
    background: white;
    padding: 25px;
    margin-top: 25px;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,.08);
    transition: .3s;
}

.module:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,.12);
}

/* LESSON */

.lesson {
    display: none;
    background: #f8fafc;
    padding: 25px;
    margin-top: 20px;
    border-radius: 12px;
    border-left: 5px solid #0d6efd;
}

/* LOCAL VIDEO */

video {
    width: 100%;
    max-height: 450px;
    border-radius: 10px;
    background: #000;
}

/* AUDIO */

audio {
    margin-top: 5px;
}

/* YOUTUBE VIDEO */

.youtube-box {
    width: 100%;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,.15);
    background: #000;
}

.youtube-box iframe {
    width: 100%;
    height: 500px;
    border: 0;
    display: block;
}

/* IMAGE */

.lesson img {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

/* BUTTON */

.start-btn {
    border-radius: 50px;
    padding: 10px 25px;
}

/* BOTTOM BUTTONS */

.bottom-buttons {
    margin-top: 40px;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* MOBILE */

@media(max-width: 768px) {

    .back-btn {
        margin: 15px 12px 0;
        padding: 10px 18px;
    }

    .container-main {
        margin: 15px auto;
        padding: 12px;
    }

    .header {
        padding: 25px;
    }

    .header h1 {
        font-size: 25px;
    }

    .module {
        padding: 20px;
    }

    .youtube-box iframe {
        height: 250px;
    }

    .lesson {
        padding: 18px;
    }
}

</style>

</head>

<body>

<!-- PROFESSIONAL BACK BUTTON -->

<a href="course_catalog.php" class="back-btn">
    <span class="back-icon">←</span>
    <span>Back to Courses</span>
</a>
    

<div class="container-main">

<!-- COURSE HEADER -->

<div class="header">

    <h1>
        📚
        <?php echo htmlspecialchars($course['course_name']); ?>
    </h1>

    <p class="mb-0">
        Complete the modules and take the quiz.
    </p>

</div>

<!-- MODULE TITLE -->

<h3 class="mt-5 mb-3">
    📖 Course Modules
</h3>

<!-- MODULES -->

<?php

if ($module_result && mysqli_num_rows($module_result) > 0) {

    $module_number = 1;

    while ($module = mysqli_fetch_assoc($module_result)) {

?>

<div class="module">

    <h4>
        Module <?php echo $module_number; ?>:
        <?php echo htmlspecialchars($module['title']); ?>
    </h4>

    <p class="text-muted">
        <?php echo htmlspecialchars($module['content']); ?>
    </p>

    <!-- START MODULE -->

    <button
        class="btn btn-primary start-btn"
        onclick="startModule(<?php echo $module['id']; ?>)">
        ▶ Start Module
    </button>

    <!-- LESSON -->

    <div id="lesson<?php echo $module['id']; ?>" class="lesson">

        <h5>
            📖 <?php echo htmlspecialchars($module['title']); ?>
        </h5>

        <hr>

        <!-- CONTENT -->

        <p>
            <?php echo nl2br(htmlspecialchars($module['content'])); ?>
        </p>

        <!-- LOCAL VIDEO -->

        <?php if (isset($module['video']) && !empty(trim($module['video']))) { ?>

            <h6 class="mt-4">🎥 Video Lesson</h6>

            <video controls>
                <source
                    src="../learning_material/lecture_videos/<?php echo htmlspecialchars($module['video']); ?>"
                    type="video/mp4">
                Your browser does not support the video tag.
            </video>

        <?php } ?>

        <!-- YOUTUBE VIDEO -->

        <?php

        if (isset($module['video_url']) && !empty(trim($module['video_url']))) {

            $youtube_url = trim($module['video_url']);

            if (strpos($youtube_url, 'watch?v=') !== false) {

                $video_id = explode('watch?v=', $youtube_url)[1];
                $video_id = explode('&', $video_id)[0];

                $youtube_url = "https://www.youtube.com/embed/" . $video_id;

            } elseif (strpos($youtube_url, 'youtu.be/') !== false) {

                $video_id = explode('youtu.be/', $youtube_url)[1];
                $video_id = explode('?', $video_id)[0];

                $youtube_url = "https://www.youtube.com/embed/" . $video_id;

            }

        ?>

            <h6 class="mt-4">▶️ YouTube Video Lesson</h6>

            <div class="youtube-box">

                <iframe
                    src="<?php echo htmlspecialchars($youtube_url); ?>"
                    title="YouTube Learning Video"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen>
                </iframe>

            </div>

        <?php } ?>

        <!-- AUDIO -->

        <?php if (isset($module['audio']) && !empty(trim($module['audio']))) { ?>

            <h6 class="mt-4">🎧 Audio Lesson</h6>

            <audio controls style="width:100%;">
                <source
                    src="../learning_material/lecture_audio/<?php echo htmlspecialchars($module['audio']); ?>"
                    type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>

        <?php } ?>

        <!-- IMAGE -->

        <?php if (isset($module['image']) && !empty(trim($module['image']))) { ?>

            <h6 class="mt-4">🖼️ Learning Image</h6>

            <img
                src="../learning_material/course_images/<?php echo htmlspecialchars($module['image']); ?>"
                class="img-fluid rounded"
                alt="Learning Image">

        <?php } ?>

        <!-- PDF -->

        <?php if (isset($module['pdf']) && !empty(trim($module['pdf']))) { ?>

            <h6 class="mt-4">📄 Study Material</h6>

            <a
                href="../learning_material/study_pdfs/<?php echo htmlspecialchars($module['pdf']); ?>"
                target="_blank"
                class="btn btn-outline-danger">
                📄 Open PDF Notes
            </a>

        <?php } ?>

        <!-- COMPLETION -->

        <div class="mt-4">

            <form method="POST" action="save_progress.php">

                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">

                <button type="submit" class="btn btn-success">
                    ✓ Mark Module as Completed
                </button>

            </form>

        </div>

    </div>

</div>

<?php

        $module_number++;

    }

} else {

?>

<div class="alert alert-warning mt-4">
    📚 No modules available for this course yet.
</div>

<?php

}

?>

<!-- QUIZ AND BACK TO COURSES -->

<div class="bottom-buttons">

    <a
        href="../quiz.php?course_id=<?php echo $course_id; ?>"
        class="btn btn-success btn-lg">
        📝 Start Quiz
    </a>

    <a
        href="course_catalog.php"
        class="btn btn-secondary btn-lg">
        ← Back to Courses
    </a>

</div>

</div>

<!-- JAVASCRIPT -->

<script>

function startModule(id) {

    const lesson = document.getElementById("lesson" + id);

    if (lesson.style.display === "block") {

        lesson.style.display = "none";

    } else {

        lesson.style.display = "block";

        lesson.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    }

}

</script>

</body>
</html>
