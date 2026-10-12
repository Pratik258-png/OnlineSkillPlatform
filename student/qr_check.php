<?php
session_start();

include '../includes/db.php';

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(["status" => "error"]);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM qr_login_tokens WHERE token = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$qr = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$qr) {
    echo json_encode(["status" => "error"]);
    exit;
}


// Expired
if (
    $qr['status'] === 'pending' &&
    strtotime($qr['expires_at']) < time()
) {

    mysqli_query(
        $conn,
        "UPDATE qr_login_tokens
         SET status='expired'
         WHERE token='" . mysqli_real_escape_string($conn, $token) . "'"
    );

    echo json_encode(["status" => "expired"]);
    exit;
}


// APPROVED
if ($qr['status'] === 'approved' && !empty($qr['user_id'])) {

    $user_id = (int)$qr['user_id'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, role
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($user) {

        // CREATE LAPTOP LOGIN SESSION
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['student_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        echo json_encode([
            "status" => "approved",
            "redirect" => "dashboard.php"
        ]);

        exit;
    }
}


echo json_encode([
    "status" => $qr['status']
]);
?>