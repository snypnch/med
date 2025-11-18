<?php
require_once __DIR__ . '/../../includes/db.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Authenticate user
function authenticateUser($username, $password) {
    $db = Database::getInstance();
    $username = $db->escape($username);
    
    $result = $db->query("SELECT * FROM users WHERE username = '$username'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            
            // Log the login activity
            logAdminActivity($user['id'], 'login', 'User logged in');
            
            return true;
        }
    }
    
    return false;
}

// Log out user
function logoutUser() {
    // Log the logout activity if user is logged in
    if (isset($_SESSION['admin_id'])) {
        logAdminActivity($_SESSION['admin_id'], 'logout', 'User logged out');
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
}

// Check user permissions (admin only)
function checkPermission($requiredRole = 'admin') {
    if (!isLoggedIn()) {
        return false;
    }
    
    // All logged-in users are admins in this system
    return true;
}

// Log admin activity
function logAdminActivity($userId, $action, $details = '') {
    $db = Database::getInstance();
    $data = [
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ];
    
    $fields = array_keys($data);
    $values = array_values($data);
    
    $escapedFields = array_map(function($field) {
        return "`" . $field . "`";
    }, $fields);
    
    $escapedValues = array_map(function($value) use ($db) {
        if ($value === NULL) return "NULL";
        return "'" . $db->escape($value) . "'";
    }, $values);
    
    $sql = "INSERT INTO `activity_logs` (" . implode(", ", $escapedFields) . ") VALUES (" . implode(", ", $escapedValues) . ")";
    
    // Create activity_logs table if it doesn't exist
    $db->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `action` varchar(50) NOT NULL,
        `details` text,
        `ip_address` varchar(50) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    return $db->query($sql);
}
?>
