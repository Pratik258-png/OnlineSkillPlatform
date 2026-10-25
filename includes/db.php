<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "online_skill_platform";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}



?>
