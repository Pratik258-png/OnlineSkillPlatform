<?php
include '../includes/db.php';

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $sql = "DELETE FROM courses WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Course Deleted Successfully!');
                window.location='manage_courses.php';
              </script>";
    } else {
        echo "Error deleting course: " . mysqli_error($conn);
    }

} else {
    header("Location: manage_courses.php");
    exit;
}
?>