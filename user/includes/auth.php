<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/utilities.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

// Authenticate user
function authenticateUser($username, $password) {
    $db = Database::getInstance();
    $username = $db->escape($username);
    
    $result = $db->query("SELECT * FROM regular_users WHERE username = '$username'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Log the login activity
            logUserActivity($user['id'], 'login', 'User logged in');
            
            return true;
        }
    }
    
    return false;
}

// Register a new user
function registerUser($data) {
    $db = Database::getInstance();
    
    // Hash the password
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert into database
    return $db->insert('regular_users', $data);
}

// Log out user
function logoutUserSession() {
    // Log the logout activity if user is logged in
    if (isset($_SESSION['user_id'])) {
        logUserActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
}

// Check if username exists
function usernameExists($username) {
    $db = Database::getInstance();
    $username = $db->escape($username);
    
    $result = $db->query("SELECT id FROM regular_users WHERE username = '$username'");
    return ($result && $result->num_rows > 0);
}

// Check if email exists
function emailExists($email) {
    $db = Database::getInstance();
    $email = $db->escape($email);
    
    $result = $db->query("SELECT id FROM regular_users WHERE email = '$email'");
    return ($result && $result->num_rows > 0);
}

// Get user profile
function getUserProfile($userId) {
    $db = Database::getInstance();
    $userId = (int)$userId;
    
    $result = $db->query("SELECT * FROM regular_users WHERE id = $userId");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?>
