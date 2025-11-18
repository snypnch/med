<?php
/**
 * Utility functions for the application
 */

// Only declare sanitize() if it doesn't already exist
if (!function_exists('sanitize')) {
    /**
     * Sanitize input to prevent XSS attacks
     */
    function sanitize($input) {
        if (is_array($input)) {
            foreach($input as $key => $value) {
                $input[$key] = sanitize($value);
            }
            return $input;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Format price with PHP currency symbol
 */
function formatPrice($price) {
    return 'PHP ' . number_format($price, 2);
}

/**
 * Generate a random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Check if string is a valid JSON
 */
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Truncate text to a specific length
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . $append;
    }
    return $text;
}
?>
