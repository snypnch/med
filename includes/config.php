<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application Constants
define('APP_NAME', 'Mandaue MedCompare');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/med');

// Database Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'med');

// Assets paths
define('CSS_PATH', APP_URL . '/assets/css');
define('JS_PATH', APP_URL . '/assets/js');
define('IMG_PATH', APP_URL . '/assets/img');

// Timezone
date_default_timezone_set('Asia/Manila');

// Common functions
function sanitize($input) {
    if (is_array($input)) {
        foreach($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
        return $input;
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
