<?php
require_once __DIR__ . '/../config/constants.php';

// Detect base URL - Simplified and more reliable
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Get base path from SCRIPT_NAME
// For example: /taolich/index.php -> /taolich
//              /index.php -> empty
//              /taolich/views/schedule/generate.php -> /taolich
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName);

// Find the base directory (remove /views, /controllers, /includes, etc.)
$basePath = $scriptDir;
if (strpos($basePath, '/views') !== false) {
    $basePath = substr($basePath, 0, strpos($basePath, '/views'));
} elseif (strpos($basePath, '/controllers') !== false) {
    $basePath = substr($basePath, 0, strpos($basePath, '/controllers'));
} elseif (strpos($basePath, '/includes') !== false) {
    $basePath = substr($basePath, 0, strpos($basePath, '/includes'));
}

// Clean up
$basePath = rtrim($basePath, '/');

$baseUrl = $protocol . '://' . $host . $basePath;
if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

// Load auth after BASE_URL is defined
require_once __DIR__ . '/auth.php';

// Require login for all pages except login.php
$currentScript = basename($_SERVER['SCRIPT_NAME']);
if ($currentScript !== 'login.php' && $currentScript !== 'logout.php' && $currentScript !== 'test_auth.php') {
    requireLogin();
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">
                <i class="fas fa-calendar-alt"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i> Nhân viên
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/staff/list.php">Danh sách</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/staff/add.php">Thêm mới</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="scheduleDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-check"></i> Lịch trực
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/schedule/generate.php">Tạo lịch mới</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/schedule/daily_calendar.php">Lịch ngày</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/schedule/weekly_list.php">Lịch tuần</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/schedule/history.php">Lịch sử</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/views/rules/config.php"><i class="fas fa-cog"></i> Cấu hình</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser['full_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/change_password.php">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
