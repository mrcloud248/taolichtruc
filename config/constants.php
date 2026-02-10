<?php
/**
 * Application Constants
 */

// Shift Types
define('SHIFT_WEEKDAY_EVENING', 'WEEKDAY_EVENING');
define('SHIFT_SUNDAY_MORNING', 'SUNDAY_MORNING');
define('SHIFT_SUNDAY_EVENING', 'SUNDAY_EVENING');
define('SHIFT_SATURDAY_MORNING', 'SATURDAY_MORNING');

// Shift Times
$SHIFT_TIMES = [
    SHIFT_WEEKDAY_EVENING => ['start' => '17:00:00', 'end' => '23:30:00', 'label' => 'Tối (5:00 PM - 11:30 PM)', 'label_short' => '5p-11h30p'],
    SHIFT_SUNDAY_MORNING => ['start' => '08:00:00', 'end' => '17:00:00', 'label' => 'Sáng CN (8:00 AM - 5:00 PM)', 'label_short' => '8a-5p'],
    SHIFT_SUNDAY_EVENING => ['start' => '17:00:00', 'end' => '23:30:00', 'label' => 'Tối CN (5:00 PM - 11:30 PM)', 'label_short' => '5p-11h30p'],
    SHIFT_SATURDAY_MORNING => ['start' => '08:00:00', 'end' => '17:00:00', 'label' => 'Sáng T7 (8:00 AM - 5:00 PM)', 'label_short' => '8a-5p']
];

// Shift Labels in Vietnamese
$SHIFT_LABELS_VN = [
    'WEEKDAY_EVENING' => 'Tối (5p-11h30p)',
    'SUNDAY_MORNING' => 'Sáng CN (8a-5p)',
    'SUNDAY_EVENING' => 'Tối CN (5p-11h30p)',
    'SATURDAY_MORNING' => 'Sáng T7 (8a-5p)'
];

// Schedule Status
define('STATUS_DRAFT', 'draft');
define('STATUS_PUBLISHED', 'published');
define('STATUS_ARCHIVED', 'archived');

// Schedule Types
define('SCHEDULE_DAILY', 'daily');
define('SCHEDULE_WEEKLY', 'weekly');

// Constraint Types
define('CONSTRAINT_DAY_OFF', 'day_off');
define('CONSTRAINT_AVOID_SHIFT', 'avoid_shift');
define('CONSTRAINT_PREFER_SHIFT', 'prefer_shift');

// Application Settings
define('APP_NAME', 'Hệ Thống Quản Lý Lịch Trực');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Ho_Chi_Minh');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Export directory
define('EXPORT_DIR', __DIR__ . '/../exports/');
?>
