<?php
require_once __DIR__ . '/config/constants.php';

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);
    $basePath = rtrim($scriptDir, '/');
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

require_once __DIR__ . '/includes/auth.php';

logoutUser();
header('Location: ' . BASE_URL . '/login.php');
exit;
?>
