<?php
include '../includes/db.php';

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM courses WHERE id='$id'");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Course not found.");
}

if (isset($_POST['update'])) {

    $course_name = $_POST['course_name'];
    $instructor_name = $_POST['instructor_name'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];

    $sql = "UPDATE courses SET
            course_name='$course_name',
            instructor_name='$instructor_name',
            description='$description',
            duration='$duration'
            WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Course Updated Successfully!');
                window.location='manage_courses.php';
              </script>";
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Course</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-warning">
            <h3>Edit Course</h3>
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Course Name</label>

                    <input type="text"
                           name="course_name"
                           class="form-control"
                           value="<?php echo htmlspecialchars($row['course_name']); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Instructor Name</label>

                    <input type="text"
                           name="instructor_name"
                           class="form-control"
                           value="<?php echo htmlspecialchars($row['instructor_name']); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>

                    <textarea name="description"
                              class="form-control"
                              rows="4"
                              required><?php echo htmlspecialchars($row['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Duration</label>

                    <input type="text"
                           name="duration"
                           class="form-control"
                           value="<?php echo htmlspecialchars($row['duration']); ?>"
                           required>
                </div>

                <button type="submit"
                        name="update"
                        class="btn btn-success">
                    Update Course
                </button>

                <a href="manage_courses.php"
                   class="btn btn-secondary">
                    Back
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>