<?php

session_start();

include '../includes/db.php';


/* ==========================================
   DATABASE CHECK
========================================== */

if (!$conn) {
    die("Database connection failed!");
}


/* ==========================================
   LOGIN CHECK
========================================== */

if (!isset($_SESSION['user_id'])) {

    die("Please login first!");

}

$user_id = intval($_SESSION['user_id']);


/* ==========================================
   COURSE ID
========================================== */

if (!isset($_GET['id'])) {

    header("Location: course_catalog.php");
    exit;

}

$course_id = intval($_GET['id']);


/* ==========================================
   GET COURSE
========================================== */

$course_sql = "SELECT id, course_name
               FROM courses
               WHERE id = '$course_id'
               LIMIT 1";

$course_result = mysqli_query(
    $conn,
    $course_sql
);


if (!$course_result) {

    die(
        "Course Error: "
        . mysqli_error($conn)
    );

}


if (mysqli_num_rows($course_result) == 0) {

    die("Course not found!");

}


$course = mysqli_fetch_assoc(
    $course_result
);

$course_name = $course['course_name'];


/* ==========================================
   SAVE CURRENT COURSE
========================================== */

$_SESSION['current_course_id'] = $course_id;


/* ==========================================
   CHECK ALREADY ENROLLED
========================================== */

$check_sql = "SELECT id
              FROM enrollments
              WHERE user_id = '$user_id'
              AND course_id = '$course_id'
              LIMIT 1";

$check_result = mysqli_query(
    $conn,
    $check_sql
);


if (!$check_result) {

    die(
        "Enrollment Check Error: "
        . mysqli_error($conn)
    );

}


/* ==========================================
   ALREADY ENROLLED
========================================== */

if (mysqli_num_rows($check_result) > 0) {

    /* IMPORTANT */
    $_SESSION['current_course_id'] = $course_id;


    echo "<script>

        alert('You are already enrolled in $course_name!');

        window.location.href =
        'course_content.php?id=$course_id';

    </script>";

    exit;
}


/* ==========================================
   INSERT ENROLLMENT
========================================== */

$insert_sql = "INSERT INTO enrollments
               (user_id, course_id)
               VALUES
               ('$user_id', '$course_id')";


if (mysqli_query($conn, $insert_sql)) {

    /* IMPORTANT */
    $_SESSION['current_course_id'] = $course_id;


    echo "<script>

        alert('🎉 $course_name Enrolled Successfully!');

        window.location.href =
        'course_content.php?id=$course_id';

    </script>";

    exit;
}


/* ==========================================
   ERROR
========================================== */

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Enrollment Error</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body>

<div class="container mt-5">

    <div class="alert alert-danger">

        <h4>
            ❌ Enrollment Failed
        </h4>

        <p>

            <?php

            echo htmlspecialchars(
                mysqli_error($conn)
            );

            ?>

        </p>

        <a
            href="course_catalog.php"
            class="btn btn-secondary">

            ← Back to Courses

        </a>

    </div>

</div>

</body>

</html>