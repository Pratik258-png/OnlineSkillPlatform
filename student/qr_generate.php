<?php

session_start();
include '../includes/db.php';

// Generate secure temporary token
$token = bin2hex(random_bytes(32));

// Token expiry - 2 minutes
$expires_at = date("Y-m-d H:i:s", time() + 120);

// Insert token into database
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO qr_login_tokens
    (user_id, token, status, expires_at)
    VALUES (NULL, ?, 'pending', ?)"
);

mysqli_stmt_bind_param($stmt, "ss", $token, $expires_at);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);


// QR Scan URL
$login_url =
    "https://flags-speeches-ultimately-baby.trycloudflare.com/OnlineSkillPlatform/student/qr_scan.php?token="
    . urlencode($token);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>QR Login | Online Skill Platform</title>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{

    font-family:Arial,sans-serif;

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

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

    background:rgba(255,255,255,0.08);

    backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,0.15);

    border-radius:25px;

    padding:35px;

    text-align:center;

    box-shadow:0 20px 50px rgba(0,0,0,0.4);
}

.logo{

    font-size:48px;

    margin-bottom:10px;
}

h1{

    font-size:28px;

    margin-bottom:8px;
}

.subtitle{

    color:#cbd5e1;

    font-size:14px;

    margin-bottom:25px;
}

.qr-box{

    background:white;

    padding:15px;

    width:250px;

    height:250px;

    margin:0 auto 20px;

    border-radius:15px;

    display:flex;

    justify-content:center;

    align-items:center;
}

#qrcode{

    width:220px;

    height:220px;

    display:flex;

    justify-content:center;

    align-items:center;
}

.status{

    padding:13px;

    border-radius:12px;

    background:rgba(59,130,246,0.15);

    color:#93c5fd;

    margin-top:15px;

    font-size:14px;
}

.timer{

    margin-top:15px;

    color:#fbbf24;

    font-weight:bold;
}

.back{

    display:inline-block;

    margin-top:20px;

    color:#cbd5e1;

    text-decoration:none;

    font-size:14px;
}

.back:hover{

    color:white;
}

</style>

</head>

<body>

<div class="container">

    <div class="logo">📱</div>

    <h1>Scan to Login</h1>

    <p class="subtitle">
        Scan this QR code using your mobile device
    </p>


    <div class="qr-box">

        <div id="qrcode"></div>

    </div>


    <div class="timer">

        QR expires in
        <span id="countdown">120</span>
        seconds

    </div>


    <div class="status" id="status">

        ⏳ Waiting for mobile confirmation...

    </div>


    <a href="../login.php" class="back">

        ← Back to Password Login

    </a>

</div>


<script>

// QR token
const token = <?php echo json_encode($token); ?>;

// QR login URL
const loginURL = <?php echo json_encode($login_url); ?>;


// ============================
// GENERATE QR CODE
// ============================

new QRCode(
    document.getElementById("qrcode"),
    {
        text: loginURL,
        width: 220,
        height: 220
    }
);


// ============================
// COUNTDOWN
// ============================

let seconds = 120;

const countdown =
    document.getElementById("countdown");

const timer = setInterval(function(){

    seconds--;

    countdown.innerText = seconds;

    if(seconds <= 0){

        clearInterval(timer);

        document.getElementById("status").innerHTML =
            "❌ QR Code expired. Please generate a new QR code.";

        clearInterval(checkLogin);
    }

},1000);


// ============================
// CHECK MOBILE APPROVAL
// ============================

const checkLogin = setInterval(function(){

    if(seconds <= 0){

        clearInterval(checkLogin);

        return;
    }


    fetch(
        "qr_check.php?token=" +
        encodeURIComponent(token),
        {
            credentials: "same-origin"
        }
    )

    .then(function(response){

        return response.json();

    })

    .then(function(data){

        console.log("QR Status:", data.status);


        // MOBILE APPROVED
        if(data.status === "approved"){

            clearInterval(checkLogin);

            clearInterval(timer);


            document.getElementById("status").innerHTML =
                "✅ Login approved! Redirecting...";


            // Go to student dashboard
            window.location.href = "dashboard.php";

        }


        // QR EXPIRED
        else if(data.status === "expired"){

            clearInterval(checkLogin);

            clearInterval(timer);


            document.getElementById("status").innerHTML =
                "❌ QR Code expired. Please generate a new QR code.";

        }

    })

    .catch(function(error){

        console.log(
            "QR check error:",
            error
        );

    });

},2000);

</script>

</body>

</html>