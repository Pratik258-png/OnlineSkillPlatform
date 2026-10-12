<?php
include '../includes/db.php';

$sql = "SELECT * FROM courses ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Courses</h2>

        <a href="add_course.php" class="btn btn-primary">
            + Add Course
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Course Title</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                if (mysqli_num_rows($result) > 0) {

                    while ($row = mysqli_fetch_assoc($result)) {
                ?>

                    <tr>
                        <td><?php echo $row['id']; ?></td>

                        <td>
                            <?php echo htmlspecialchars($row['course_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['description']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['duration']); ?>
                        </td>

                        <td>
                            <a href="edit_course.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <a href="delete_course.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this course?');">
                                Delete
                            </a>
                        </td>
                    </tr>

                <?php
                    }

                } else {
                ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            No courses found.
                        </td>
                    </tr>

                <?php
                }
                ?>

                </tbody>

            </table>

        </div>
    </div>

</div>

</body>
</html>