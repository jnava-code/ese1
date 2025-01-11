<?php 
session_start();

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'esetech');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ./admin/login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESE-Tech Industrial Solutions Corporation</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="shortcut icon" type="x-icon" href="images/logo2.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
<header>
    <div class="logo">
        <img src="images/logo1.png" alt="ESE-Tech Logo">
        <h1>ESE-Tech Industrial Solutions Corporation</h1>
    </div>
    
    <!-- Right-side Icons and Profile -->
    <div class="header-right">
        <i class="fas fa-bullhorn"></i>  <!-- Horn Icon -->
        <i class="fas fa-bell"></i>  <!-- Notifications Icon -->
        <div class="profile">
            <strong>
                <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Guest'; ?>
            </strong>
        </div>
        <a href="./logout" class="logout-button">
            <i class="fas fa-power-off"></i> <!-- Shutdown icon -->
        </a> <!-- Logout button -->
    </div>
</header>
<div class="container">
