<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
</head>
<body>
    <header>
        <div class="header-top">
            <h1><a href="Home.php">Clinix</a></h1>
            <div class="hamburger" id="hamburger">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
        </div>
        <nav class="nav" id="nav">
            <ul>
                <li><a href="Home.php">Home</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="service.php">Services</a></li>
                <li><a href="contactus.php">Contact Us</a></li>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <li><a href="logout.php" class="button">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="button">Login</a></li>
                    <li><a href="sign.php" class="button">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <script>
        const hamburger = document.getElementById('hamburger');
const nav = document.getElementById('nav');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    nav.classList.toggle('active');
});

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    if (!nav.contains(e.target) && !hamburger.contains(e.target) && nav.classList.contains('active')) {
        nav.classList.remove('active');
        hamburger.classList.remove('active');
    }
});
    </script>

    </body>
</html>