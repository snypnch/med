<?php
// Include required files
require_once 'includes/auth.php';

// Log the logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    logUserActivity($_SESSION['user_id'], 'logout', 'User logged out');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: index.php");
exit;
?>
