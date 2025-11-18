<?php
require_once 'includes/auth.php';

// Log the user out
logoutUser();

// Redirect to login page
header("Location: index.php");
exit;
