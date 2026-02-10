<?php
/**
 * Common utility functions
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format date to Vietnamese
 */
function formatDateVN($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Format datetime to Vietnamese
 */
function formatDateTimeVN($datetime) {
    $timestamp = strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Get day of week in Vietnamese
 */
function getDayOfWeekVN($date) {
    $days = [
        'Monday' => 'Thứ 2',
        'Tuesday' => 'Thứ 3',
        'Wednesday' => 'Thứ 4',
        'Thursday' => 'Thứ 5',
        'Friday' => 'Thứ 6',
        'Saturday' => 'Thứ 7',
        'Sunday' => 'Chủ nhật'
    ];
    $dayName = date('l', strtotime($date));
    return $days[$dayName] ?? $dayName;
}

/**
 * Get month name in Vietnamese
 */
function getMonthNameVN($month) {
    return "Tháng " . $month;
}

/**
 * Check if date is weekend
 */
function isWeekend($date) {
    $dayOfWeek = date('N', strtotime($date));
    return $dayOfWeek >= 6; // 6 = Saturday, 7 = Sunday
}

/**
 * Get all Saturdays in a date range
 */
function getSaturdays($startDate, $endDate) {
    $saturdays = [];
    $current = strtotime($startDate);
    $end = strtotime($endDate);
    
    while ($current <= $end) {
        if (date('N', $current) == 6) { // Saturday
            $saturdays[] = date('Y-m-d', $current);
        }
        $current = strtotime('+1 day', $current);
    }
    
    return $saturdays;
}

/**
 * Generate random color for staff
 */
function generateColor($seed) {
    $colors = [
        '#3788d8', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
        '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#d35400'
    ];
    return $colors[$seed % count($colors)];
}

/**
 * JSON response helper
 */
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Get shift label in Vietnamese
 */
function getShiftLabelVN($shiftType) {
    $labels = [
        'WEEKDAY_EVENING' => 'Tối (5p-11h30p)',
        'SUNDAY_MORNING' => 'Sáng CN (8a-5p)',
        'SUNDAY_EVENING' => 'Tối CN (5p-11h30p)',
        'SATURDAY_MORNING' => 'Sáng T7 (8a-5p)'
    ];
    return $labels[$shiftType] ?? $shiftType;
}

/**
 * Get shift color class
 */
function getShiftColorClass($shiftType) {
    $colors = [
        'WEEKDAY_EVENING' => 'shift-evening',
        'SUNDAY_MORNING' => 'shift-morning',
        'SUNDAY_EVENING' => 'shift-evening',
        'SATURDAY_MORNING' => 'shift-saturday'
    ];
    return $colors[$shiftType] ?? '';
}
?>
