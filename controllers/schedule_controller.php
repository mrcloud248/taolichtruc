<?php
// Disable display_errors for production (errors will still be logged)
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $scheduleModel = new Schedule();
} catch (Exception $e) {
    jsonResponse(false, 'Database connection error: ' . $e->getMessage());
    exit;
}

// Handle AJAX requests
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    try {
        switch ($action) {
            case 'generate_daily':
                $month = intval($_POST['month']);
                $year = intval($_POST['year']);
                $firstDayStaffId = isset($_POST['first_day_staff_id']) && $_POST['first_day_staff_id'] !== '' 
                    ? intval($_POST['first_day_staff_id']) 
                    : null;
                $generatedBy = sanitize($_POST['generated_by']);
                $staffIds = isset($_POST['staff_ids']) ? json_decode($_POST['staff_ids'], true) : null;
                
                $result = $scheduleModel->generateDailySchedule($month, $year, $firstDayStaffId, $generatedBy, $staffIds);
                
                if ($result['success']) {
                    jsonResponse(true, 'Tạo lịch ngày thành công', $result);
                } else {
                    jsonResponse(false, $result['message']);
                }
                break;
            
        case 'generate_weekly':
            $startDate = sanitize($_POST['start_date']);
            $numWeeks = intval($_POST['num_weeks']);
            $staffIds = json_decode($_POST['staff_ids'], true);
            $generatedBy = sanitize($_POST['generated_by']);
            
            $result = $scheduleModel->generateWeeklySchedule($startDate, $numWeeks, $staffIds, $generatedBy);
            
            if ($result['success']) {
                jsonResponse(true, 'Tạo lịch tuần thành công', $result);
            } else {
                jsonResponse(false, $result['message']);
            }
            break;
            
        case 'get_schedule':
            $id = intval($_GET['id']);
            $schedule = $scheduleModel->getById($id);
            
            if ($schedule) {
                $shifts = $scheduleModel->getShifts($id);
                jsonResponse(true, 'Success', [
                    'schedule' => $schedule,
                    'shifts' => $shifts
                ]);
            } else {
                jsonResponse(false, 'Không tìm thấy lịch');
            }
            break;
            
        case 'update_status':
            $id = intval($_POST['id']);
            $status = sanitize($_POST['status']);
            
            if ($scheduleModel->updateStatus($id, $status)) {
                jsonResponse(true, 'Cập nhật trạng thái thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            
            if ($scheduleModel->delete($id)) {
                jsonResponse(true, 'Xóa lịch thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        case 'get_shifts_calendar':
            $scheduleId = intval($_GET['schedule_id']);
            $shifts = $scheduleModel->getShifts($scheduleId);
            
            // Get schedule info
            $schedule = $scheduleModel->getById($scheduleId);
            
            // If this is a daily schedule, also get Saturday shifts from ALL weekly schedules
            if ($schedule && $schedule['schedule_type'] === 'daily') {
                // Get all weekly schedules (not filtered by month/year)
                $allSchedules = $scheduleModel->getAll();
                
                // Filter weekly schedules
                foreach ($allSchedules as $weeklySchedule) {
                    if ($weeklySchedule['schedule_type'] === 'weekly') {
                        $weeklyShifts = $scheduleModel->getShifts($weeklySchedule['id']);
                        
                        // Add all Saturday shifts (calendar will handle date filtering)
                        foreach ($weeklyShifts as $weeklyShift) {
                            $shifts[] = $weeklyShift;
                        }
                    }
                }
            }
            
            // Format for FullCalendar
            $events = [];
            foreach ($shifts as $shift) {
                $shiftLabel = getShiftLabelVN($shift['shift_type']);
                $colorClass = getShiftColorClass($shift['shift_type']);
                
                // Determine background color
                $bgColor = '#3788d8'; // default
                if (strpos($shift['shift_type'], 'MORNING') !== false) {
                    $bgColor = '#4CAF50'; // green for morning
                } elseif (strpos($shift['shift_type'], 'EVENING') !== false) {
                    $bgColor = '#6c757d'; // gray for evening
                } elseif (strpos($shift['shift_type'], 'SATURDAY') !== false) {
                    $bgColor = '#2196F3'; // blue for Saturday
                }
                
                $events[] = [
                    'id' => $shift['id'],
                    'title' => $shift['staff_name'] . ' - ' . $shiftLabel,
                    'start' => $shift['shift_date'] . 'T' . $shift['start_time'],
                    'end' => $shift['shift_date'] . 'T' . $shift['end_time'],
                    'backgroundColor' => $bgColor,
                    'borderColor' => $bgColor,
                    'extendedProps' => [
                        'staff_id' => $shift['staff_id'],
                        'shift_type' => $shift['shift_type'],
                        'shift_label' => $shiftLabel
                    ]
                ];
            }
            
            jsonResponse(true, 'Success', $events);
            break;
            
        default:
            jsonResponse(false, 'Invalid action');
    }
    } catch (Exception $e) {
        jsonResponse(false, 'Lỗi: ' . $e->getMessage());
    }
}
?>
