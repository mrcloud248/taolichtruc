<?php
/**
 * Database Configuration Example
 * Copy this file to database.php and update with your credentials
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'schedule_management');
define('DB_CHARSET', 'utf8mb4');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset(DB_CHARSET);
    return $conn;
}
?>
