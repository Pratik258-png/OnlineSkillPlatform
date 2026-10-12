<?php
session_start();

include("../includes/db.php");

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $career_goal = $_POST['career_goal'] ?? '';

    if (!empty($career_goal)) {

        $sql = "UPDATE users SET career_goal = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "si", $career_goal, $user_id);

            if (mysqli_stmt_execute($stmt)) {

                $_SESSION['career_goal'] = $career_goal;

                // Next step
                header("Location: skill_assessment.php");
                exit;

            } else {
                $message = "Career Goal save झाला नाही.";
            }

            mysqli_stmt_close($stmt);

        } else {
            $message = "Database error.";
        }

    } else {
        $message = "Please select your career goal.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Career Goal</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff, #f8f9fa);
            font-family: Arial, sans-serif;
        }

        .goal-card {
            width: 600px;
            max-width: 95%;
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
        }

        .goal-card h2 {
            font-weight: bold;
        }

        .goal-option {
            display: block;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 17px;
        }

        .goal-option:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .goal-option input {
            margin-right: 12px;
            transform: scale(1.2);
        }

        .btn-start {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            font-size: 17px;
            font-weight: bold;
            margin-top: 10px;
        }

    </style>

</head>

<body>

<div class="goal-card">

    <h2 class="text-center mb-2">
        🎯 Choose Your Career Goal
    </h2>

    <p class="text-center text-muted mb-4">
        Select the career you want to prepare for.
    </p>

    <?php if (!empty($message)) { ?>

        <div class="alert alert-danger">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <form method="POST">

        <label class="goal-option">

            <input
                type="radio"
                name="career_goal"
                value="Web Developer"
                required>

            💻 Web Developer

        </label>


        <label class="goal-option">

            <input
                type="radio"
                name="career_goal"
                value="Python Developer">

            🐍 Python Developer

        </label>


        <label class="goal-option">

            <input
                type="radio"
                name="career_goal"
                value="Java Developer">

            ☕ Java Developer

        </label>


        <label class="goal-option">

            <input
                type="radio"
                name="career_goal"
                value="Data Scientist">

            📊 Data Scientist

        </label>


        <label class="goal-option">

            <input
                type="radio"
                name="career_goal"
                value="Full Stack Developer">

            🌐 Full Stack Developer

        </label>


        <button
            type="submit"
            class="btn btn-primary btn-start">

            Continue to Skill Assessment →

        </button>

    </form>

</div>

</body>

</html>
