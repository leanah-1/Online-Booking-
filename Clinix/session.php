<?php
session_start();

// Define timeout duration (e.g., 20 minutes = 1200 seconds)
$timeout_duration = 1200;

// If user is logged in, check for session timeout
if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    if ($elapsed_time > $timeout_duration) {
        // Session expired, destroy session and redirect to login with timeout message
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'sign.php'];
$protected_pages = ['book.php', 'update.php', 'dashboard.php'];

if (in_array($current_page, $protected_pages) && !isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}


// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
