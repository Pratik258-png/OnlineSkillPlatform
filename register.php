<?php
include 'includes/db.php';

$message = "";
$message_type = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check whether email already exists
    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {

        $message = "Email already registered. Please login.";
        $message_type = "danger";

    } else {

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $name,
            $email,
            $hashed_password,
            $role
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Registration Successful! You can login now.";
            $message_type = "success";

        } else {

            $message = "Registration Failed: " . mysqli_error($conn);
            $message_type = "danger";
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

    <title>User Registration</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-primary text-white text-center">

                    <h3>User Registration</h3>

                </div>

                <div class="card-body">

                    <?php if ($message != "") { ?>

                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>

                    <?php } ?>

                    <form method="POST" action="">

                        <!-- Name -->

                        <div class="mb-3">

                            <label class="form-label">
                                Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Enter your name"
                                required>

                        </div>


                        <!-- Email -->

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter your email"
                                required>

                        </div>


                        <!-- Password -->

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Create password"
                                required>

                        </div>


                        <!-- Role -->

                        <div class="mb-3">

                            <label class="form-label">
                                Select Role
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                required>

                                <option value="student">
                                    Student
                                </option>

                                <option value="instructor">
                                    Instructor
                                </option>

                            </select>

                        </div>


                        <!-- Register -->

                        <div class="d-grid">

                            <button
                                type="submit"
                                name="register"
                                class="btn btn-success">

                                Register

                            </button>

                        </div>

                    </form>


                    <div class="text-center mt-3">

                        <a href="login.php">
                            Already have an account? Login
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>