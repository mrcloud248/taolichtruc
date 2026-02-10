<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Export {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Export schedule to CSV (simple alternative to Excel)
     */
    public function exportToCSV($scheduleId) {
        $schedule = $this->getScheduleData($scheduleId);
        
        if (!$schedule) {
            return false;
        }
        
        $filename = 'lich_truc_' . $schedule['info']['schedule_type'] . '_' . 
                    $schedule['info']['month'] . '_' . $schedule['info']['year'] . '_' . 
                    date('YmdHis') . '.csv';
        
        $filepath = EXPORT_DIR . $filename;
        
        // Create exports directory if not exists
        if (!file_exists(EXPORT_DIR)) {
            mkdir(EXPORT_DIR, 0777, true);
        }
        
        $fp = fopen($filepath, 'w');
        
        // Add BOM for UTF-8
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($fp, ['LỊCH TRỰC ' . strtoupper($schedule['info']['schedule_type'])]);
        fputcsv($fp, ['Tháng ' . $schedule['info']['month'] . '/' . $schedule['info']['year']]);
        fputcsv($fp, ['Tạo ngày: ' . date('d/m/Y H:i', strtotime($schedule['info']['generated_at']))]);
        fputcsv($fp, ['Người tạo: ' . $schedule['info']['generated_by']]);
        fputcsv($fp, []);
        
        // Column headers
        fputcsv($fp, ['STT', 'Ngày', 'Thứ', 'Ca trực', 'Nhân viên', 'Giờ trực']);
        
        // Data rows
        $stt = 1;
        foreach ($schedule['shifts'] as $shift) {
            fputcsv($fp, [
                $stt++,
                date('d/m/Y', strtotime($shift['shift_date'])),
                $this->getDayOfWeekVN($shift['shift_date']),
                $this->getShiftTypeName($shift['shift_type']),
                $shift['staff_name'],
                date('H:i', strtotime($shift['start_time'])) . ' - ' . date('H:i', strtotime($shift['end_time']))
            ]);
        }
        
        // Statistics
        fputcsv($fp, []);
        fputcsv($fp, ['THỐNG KÊ']);
        fputcsv($fp, ['Nhân viên', 'Số ca trực']);
        
        foreach ($schedule['statistics'] as $stat) {
            fputcsv($fp, [$stat['staff_name'], $stat['shift_count']]);
        }
        
        fclose($fp);
        
        return $filename;
    }
    
    /**
     * Get schedule data for export
     */
    private function getScheduleData($scheduleId) {
        // Get schedule info
        $stmt = $this->conn->prepare("SELECT * FROM schedules WHERE id = ?");
        $stmt->bind_param("i", $scheduleId);
        $stmt->execute();
        $info = $stmt->get_result()->fetch_assoc();
        
        if (!$info) {
            return false;
        }
        
        // Get shifts
        $stmt = $this->conn->prepare(
            "SELECT ss.*, st.name as staff_name, st.position 
             FROM schedule_shifts ss
             JOIN staff st ON ss.staff_id = st.id
             WHERE ss.schedule_id = ?
             ORDER BY ss.shift_date ASC, ss.start_time ASC"
        );
        $stmt->bind_param("i", $scheduleId);
        $stmt->execute();
        $shifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Calculate statistics
        $stats = [];
        foreach ($shifts as $shift) {
            $staffId = $shift['staff_id'];
            if (!isset($stats[$staffId])) {
                $stats[$staffId] = [
                    'staff_name' => $shift['staff_name'],
                    'shift_count' => 0
                ];
            }
            $stats[$staffId]['shift_count']++;
        }
        
        return [
            'info' => $info,
            'shifts' => $shifts,
            'statistics' => array_values($stats)
        ];
    }
    
    /**
     * Get day of week in Vietnamese
     */
    private function getDayOfWeekVN($date) {
        $days = [
            'Monday' => 'Thứ 2',
            'Tuesday' => 'Thứ 3',
            'Wednesday' => 'Thứ 4',
            'Thursday' => 'Thứ 5',
            'Friday' => 'Thứ 6',
            'Saturday' => 'Thứ 7',
            'Sunday' => 'Chủ nhật'
        ];
        return $days[date('l', strtotime($date))];
    }
    
    /**
     * Get shift type name
     */
    private function getShiftTypeName($type) {
        $names = [
            'WEEKDAY_EVENING' => 'Tối thường',
            'SUNDAY_MORNING' => 'Sáng CN',
            'SUNDAY_EVENING' => 'Tối CN',
            'SATURDAY_MORNING' => 'Sáng T7'
        ];
        return $names[$type] ?? $type;
    }
}
?>
