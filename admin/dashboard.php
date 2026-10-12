<?php
session_start();

if(!isset($_SESSION['id']))
{
    header("Location: ../login.php");
    exit();
}

if($_SESSION['role']!="admin")
{
    die("Access Denied");
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-dark bg-dark">

<div class="container">

<span class="navbar-brand">

Admin Dashboard

</span>

<a href="../logout.php" class="btn btn-danger">

Logout

</a>

</div>

</nav>

<div class="container mt-5">

<h2>Welcome <?php echo $_SESSION['name']; ?></h2>

<hr>

<div class="row">

<div class="col-md-4">

<div class="card">

<div class="card-body">

<h4>Add Course</h4>

<a href="add_course.php" class="btn btn-primary">

Open

</a>

</div>

</div>

</div>

</div>

</div>

</body>

</html>