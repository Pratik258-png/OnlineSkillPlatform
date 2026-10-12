<?php

session_start();

include '../includes/db.php';

$token = $_GET['token'] ?? '';

$message = '';
$error = '';


// Token missing
if (empty($token)) {
    $error = "Invalid QR Code.";
}


// Handle mobile confirmation
if (isset($_POST['confirm_login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {

        $error = "Please enter email and password.";

    } else {

        // Find user by email only
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$user) {

            $error = "User account not found.";

        } else {

            // Password is intentionally NOT verified
            // Any non-empty password will be accepted

            // Check QR token
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id, status, expires_at
                 FROM qr_login_tokens
                 WHERE token = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "s", $token);

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $qr = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);


            if (!$qr) {

                $error = "QR Code not found.";

            } elseif ($qr['status'] !== 'pending') {

                $error = "This QR Code has already been used or expired.";

            } elseif (strtotime($qr['expires_at']) < time()) {

                // Mark expired
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE qr_login_tokens
                     SET status = 'expired'
                     WHERE token = ?"
                );

                mysqli_stmt_bind_param($stmt, "s", $token);

                mysqli_stmt_execute($stmt);

                mysqli_stmt_close($stmt);

                $error = "QR Code has expired.";

            } else {

                // Approve QR login
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE qr_login_tokens
                     SET user_id = ?,
                         status = 'approved',
                         approved_at = NOW()
                     WHERE token = ?
                     AND status = 'pending'"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "is",
                    $user['id'],
                    $token
                );

                mysqli_stmt_execute($stmt);

                mysqli_stmt_close($stmt);

                $message = "Login approved successfully! You can close this page.";
            }
        }
    }
}


// Check token before showing form
$valid_token = false;

if (!empty($token)) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, status, expires_at
         FROM qr_login_tokens
         WHERE token = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "s", $token);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $qr_data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($qr_data) {

        if (
            $qr_data['status'] === 'pending' &&
            strtotime($qr_data['expires_at']) > time()
        ) {

            $valid_token = true;
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

<title>Confirm QR Login</title>

<style>

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    font-family:Arial,sans-serif;

    background:
    linear-gradient(
        135deg,
        #0f172a,
        #1e293b,
        #312e81
    );

    color:white;
}

.container{

    width:430px;

    max-width:92%;

    padding:35px;

    border-radius:25px;

    background:rgba(255,255,255,0.08);

    backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,0.15);

    box-shadow:0 20px 50px rgba(0,0,0,0.4);
}

.icon{

    text-align:center;

    font-size:55px;

    margin-bottom:10px;
}

h1{

    text-align:center;

    font-size:27px;

    margin-bottom:8px;
}

.subtitle{

    text-align:center;

    color:#cbd5e1;

    font-size:14px;

    margin-bottom:25px;
}

label{

    display:block;

    margin-bottom:7px;

    font-size:14px;

    color:#e2e8f0;
}

input{

    width:100%;

    padding:13px;

    margin-bottom:17px;

    border:none;

    border-radius:10px;

    background:rgba(255,255,255,0.1);

    color:white;

    outline:none;
}

input::placeholder{

    color:#94a3b8;
}

button{

    width:100%;

    padding:14px;

    border:none;

    border-radius:12px;

    background:linear-gradient(135deg,#6366f1,#8b5cf6);

    color:white;

    font-size:16px;

    font-weight:bold;

    cursor:pointer;
}

button:hover{

    opacity:0.9;
}

.error{

    background:rgba(239,68,68,0.15);

    color:#fca5a5;

    padding:12px;

    border-radius:10px;

    margin-bottom:18px;

    text-align:center;
}

.success{

    background:rgba(34,197,94,0.15);

    color:#86efac;

    padding:15px;

    border-radius:10px;

    text-align:center;
}

.info{

    margin-top:20px;

    padding:12px;

    border-radius:10px;

    background:rgba(59,130,246,0.1);

    color:#bfdbfe;

    font-size:13px;

    text-align:center;
}

</style>

</head>

<body>

<div class="container">

    <div class="icon">🔐</div>

    <h1>Confirm Login</h1>

    <p class="subtitle">
        Confirm that you want to login on your computer
    </p>


    <?php if (!empty($error)): ?>

        <div class="error">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($message)): ?>

        <div class="success">
            ✅ <?php echo htmlspecialchars($message); ?>
        </div>

    <?php elseif ($valid_token): ?>

        <form method="POST"
              action="qr_scan.php?token=<?php echo urlencode($token); ?>">

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your registered email"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter any password"
                required
            >

            <button type="submit"
                    name="confirm_login">

                ✅ Confirm Login

            </button>

        </form>

        <div class="info">

            🔐 Enter your registered email.
            Password verification is disabled for this demo.

        </div>

    <?php elseif (empty($error)): ?>

        <div class="error">
            ❌ Invalid or expired QR Code.
        </div>

    <?php endif; ?>

</div>

</body>

</html>