<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/Staff.php';

class Schedule {
    private $conn;
    private $staffModel;
    
    public function __construct() {
        $this->conn = getDBConnection();
        $this->staffModel = new Staff();
    }
    
    /**
     * Create new schedule
     */
    public function create($type, $month, $year, $generatedBy, $notes = null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO schedules (schedule_type, month, year, generated_by, notes) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param("siiss", $type, $month, $year, $generatedBy, $notes);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    /**
     * Add shift to schedule
     */
    public function addShift($scheduleId, $staffId, $date, $shiftType, $startTime, $endTime) {
        $stmt = $this->conn->prepare(
            "INSERT INTO schedule_shifts (schedule_id, staff_id, shift_date, shift_type, start_time, end_time) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param("iissss", $scheduleId, $staffId, $date, $shiftType, $startTime, $endTime);
        return $stmt->execute();
    }
    
    /**
     * Generate daily schedule for a month
     */
    public function generateDailySchedule($month, $year, $firstDayStaffId, $generatedBy, $staffIds = null) {
        // Define shift times
        $SHIFT_TIMES = [
            SHIFT_WEEKDAY_EVENING => ['start' => '17:00:00', 'end' => '23:30:00'],
            SHIFT_SUNDAY_MORNING => ['start' => '08:00:00', 'end' => '17:00:00'],
            SHIFT_SUNDAY_EVENING => ['start' => '17:00:00', 'end' => '23:30:00'],
            SHIFT_SATURDAY_MORNING => ['start' => '08:00:00', 'end' => '17:00:00']
        ];
        
        // Get active staff
        if ($staffIds && is_array($staffIds) && count($staffIds) > 0) {
            // Filter staff list by selected IDs
            $allStaff = $this->staffModel->getAllActive();
            $staffList = array_filter($allStaff, function($staff) use ($staffIds) {
                return in_array($staff['id'], $staffIds);
            });
            $staffList = array_values($staffList); // Re-index array
        } else {
            // Use all active staff
            $staffList = $this->staffModel->getAllActive();
        }
        
        if (empty($staffList)) {
            return ['success' => false, 'message' => 'Không có nhân viên nào'];
        }
        
        // Create schedule record
        $scheduleId = $this->create(SCHEDULE_DAILY, $month, $year, $generatedBy);
        if (!$scheduleId) {
            return ['success' => false, 'message' => 'Không thể tạo lịch'];
        }
        
        // Get all dates in month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $assignments = [];
        
        // Assign first day
        $firstDate = sprintf("%04d-%02d-01", $year, $month);
        $dayOfWeek = date('N', strtotime($firstDate));
        
        // Create staff pool for fair distribution FIRST
        $totalSlots = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $dayOfWeekTemp = date('N', strtotime($date));
            $totalSlots += ($dayOfWeekTemp == 7) ? 2 : 1; // Sunday has 2 shifts
        }
        
        $shiftsPerStaff = ceil($totalSlots / count($staffList));
        $staffPool = [];
        
        foreach ($staffList as $staff) {
            for ($i = 0; $i < $shiftsPerStaff; $i++) {
                $staffPool[] = $staff['id'];
            }
        }
        
        shuffle($staffPool);
        $poolIndex = 0;
        
        // Now assign first day
        if ($dayOfWeek == 7) { // Sunday - assign only morning shift to first staff
            $this->addShift($scheduleId, $firstDayStaffId, $firstDate, SHIFT_SUNDAY_MORNING, 
                          $SHIFT_TIMES[SHIFT_SUNDAY_MORNING]['start'], 
                          $SHIFT_TIMES[SHIFT_SUNDAY_MORNING]['end']);
            $assignments[$firstDate] = [$firstDayStaffId];
            
            // Remove first staff from pool if exists
            $key = array_search($firstDayStaffId, $staffPool);
            if ($key !== false) {
                unset($staffPool[$key]);
                $staffPool = array_values($staffPool);
            }
            
            // Evening shift goes to next staff in pool
            $eveningStaffId = $this->getNextStaffFromPool($staffPool, $poolIndex, $assignments, $firstDate, $staffList);
            $this->addShift($scheduleId, $eveningStaffId, $firstDate, SHIFT_SUNDAY_EVENING, 
                          $SHIFT_TIMES[SHIFT_SUNDAY_EVENING]['start'], 
                          $SHIFT_TIMES[SHIFT_SUNDAY_EVENING]['end']);
            $assignments[$firstDate][] = $eveningStaffId;
        } else {
            $this->addShift($scheduleId, $firstDayStaffId, $firstDate, SHIFT_WEEKDAY_EVENING, 
                          $SHIFT_TIMES[SHIFT_WEEKDAY_EVENING]['start'], 
                          $SHIFT_TIMES[SHIFT_WEEKDAY_EVENING]['end']);
            $assignments[$firstDate] = [$firstDayStaffId];
            
            // Remove first staff from pool if exists
            $key = array_search($firstDayStaffId, $staffPool);
            if ($key !== false) {
                unset($staffPool[$key]);
                $staffPool = array_values($staffPool);
            }
        }
        
        // Assign remaining days
        for ($day = 2; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $dayOfWeek = date('N', strtotime($date));
            
            if (!isset($assignments[$date])) {
                $assignments[$date] = [];
            }
            
            if ($dayOfWeek == 7) { // Sunday - 2 shifts
                // Morning shift
                $staffId = $this->getNextStaffFromPool($staffPool, $poolIndex, $assignments, $date, $staffList);
                $this->addShift($scheduleId, $staffId, $date, SHIFT_SUNDAY_MORNING, 
                              $SHIFT_TIMES[SHIFT_SUNDAY_MORNING]['start'], 
                              $SHIFT_TIMES[SHIFT_SUNDAY_MORNING]['end']);
                $assignments[$date][] = $staffId;
                
                // Evening shift
                $staffId = $this->getNextStaffFromPool($staffPool, $poolIndex, $assignments, $date, $staffList);
                $this->addShift($scheduleId, $staffId, $date, SHIFT_SUNDAY_EVENING, 
                              $SHIFT_TIMES[SHIFT_SUNDAY_EVENING]['start'], 
                              $SHIFT_TIMES[SHIFT_SUNDAY_EVENING]['end']);
                $assignments[$date][] = $staffId;
            } else { // Weekday
                $staffId = $this->getNextStaffFromPool($staffPool, $poolIndex, $assignments, $date, $staffList);
                $this->addShift($scheduleId, $staffId, $date, SHIFT_WEEKDAY_EVENING, 
                              $SHIFT_TIMES[SHIFT_WEEKDAY_EVENING]['start'], 
                              $SHIFT_TIMES[SHIFT_WEEKDAY_EVENING]['end']);
                $assignments[$date][] = $staffId;
            }
        }
        
        // Log history
        $this->addHistory($scheduleId, 'created', $generatedBy, 'Tạo lịch trực ngày tự động');
        
        return ['success' => true, 'scheduleId' => $scheduleId];
    }
    
    /**
     * Get next staff from pool avoiding consecutive shifts
     */
    private function getNextStaffFromPool(&$pool, &$index, $assignments, $currentDate, $staffList) {
        $maxAttempts = count($pool);
        $attempts = 0;
        
        // If pool is empty, refill it
        if (empty($pool)) {
            foreach ($staffList as $staff) {
                $pool[] = $staff['id'];
            }
            shuffle($pool);
            $index = 0;
        }
        
        while ($attempts < $maxAttempts) {
            if ($index >= count($pool)) {
                // Reshuffle pool
                shuffle($pool);
                $index = 0;
            }
            
            $staffId = $pool[$index];
            
            // Check if staff worked yesterday
            $yesterday = date('Y-m-d', strtotime($currentDate . ' -1 day'));
            $workedYesterday = isset($assignments[$yesterday]) && in_array($staffId, $assignments[$yesterday]);
            
            if (!$workedYesterday) {
                unset($pool[$index]);
                $pool = array_values($pool);
                return $staffId;
            }
            
            $index++;
            $attempts++;
        }
        
        // If all attempts failed, just take next (even if worked yesterday)
        if (empty($pool)) {
            // Pool is empty, refill
            foreach ($staffList as $staff) {
                $pool[] = $staff['id'];
            }
            shuffle($pool);
            $index = 0;
        }
        
        if ($index >= count($pool)) {
            $index = 0;
        }
        
        $staffId = $pool[$index];
        unset($pool[$index]);
        $pool = array_values($pool);
        return $staffId;
    }
    
    /**
     * Generate weekly schedule (Saturday mornings)
     */
    public function generateWeeklySchedule($startDate, $numWeeks, $staffIds, $generatedBy) {
        // Define shift times
        $SHIFT_TIMES = [
            SHIFT_WEEKDAY_EVENING => ['start' => '17:00:00', 'end' => '23:30:00'],
            SHIFT_SUNDAY_MORNING => ['start' => '08:00:00', 'end' => '17:00:00'],
            SHIFT_SUNDAY_EVENING => ['start' => '17:00:00', 'end' => '23:30:00'],
            SHIFT_SATURDAY_MORNING => ['start' => '08:00:00', 'end' => '17:00:00']
        ];
        
        if (empty($staffIds)) {
            return ['success' => false, 'message' => 'Chưa chọn nhân viên'];
        }
        
        // Shuffle staff list
        shuffle($staffIds);
        
        // Find all Saturdays from start date
        $saturdays = [];
        $checkDate = strtotime($startDate);
        
        // If start date is not Saturday, find next Saturday
        while (date('N', $checkDate) != 6) {
            $checkDate = strtotime('+1 day', $checkDate);
        }
        
        // Get Saturdays for the specified number of weeks
        for ($i = 0; $i < $numWeeks && $i < count($staffIds); $i++) {
            $saturdays[] = date('Y-m-d', $checkDate);
            $checkDate = strtotime('+7 days', $checkDate);
        }
        
        if (empty($saturdays)) {
            return ['success' => false, 'message' => 'Không tìm thấy ngày Thứ 7 nào'];
        }
        
        // Create schedule record (use month/year of first Saturday)
        $firstSaturday = $saturdays[0];
        $month = date('n', strtotime($firstSaturday));
        $year = date('Y', strtotime($firstSaturday));
        
        $scheduleId = $this->create(SCHEDULE_WEEKLY, $month, $year, $generatedBy, 
                                    'Lịch tuần cho ' . count($staffIds) . ' nhân viên, ' . count($saturdays) . ' tuần');
        
        if (!$scheduleId) {
            return ['success' => false, 'message' => 'Không thể tạo lịch'];
        }
        
        // Assign staff to Saturdays
        $assignments = [];
        for ($i = 0; $i < count($staffIds) && $i < count($saturdays); $i++) {
            $staffId = $staffIds[$i];
            $date = $saturdays[$i];
            
            $this->addShift($scheduleId, $staffId, $date, SHIFT_SATURDAY_MORNING, 
                          $SHIFT_TIMES[SHIFT_SATURDAY_MORNING]['start'], 
                          $SHIFT_TIMES[SHIFT_SATURDAY_MORNING]['end']);
            
            $assignments[] = [
                'date' => $date,
                'staff_id' => $staffId
            ];
        }
        
        // Log history
        $this->addHistory($scheduleId, 'created', $generatedBy, 'Tạo lịch trực tuần tự động');
        
        return ['success' => true, 'scheduleId' => $scheduleId, 'assignments' => $assignments];
    }
    
    /**
     * Get schedule by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM schedules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get shifts for a schedule
     */
    public function getShifts($scheduleId) {
        $stmt = $this->conn->prepare(
            "SELECT ss.*, st.name as staff_name 
             FROM schedule_shifts ss
             JOIN staff st ON ss.staff_id = st.id
             WHERE ss.schedule_id = ?
             ORDER BY ss.shift_date ASC, ss.start_time ASC"
        );
        
        $stmt->bind_param("i", $scheduleId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get schedules by type and date
     */
    public function getByTypeAndDate($type, $month, $year) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM schedules 
             WHERE schedule_type = ? AND month = ? AND year = ?
             ORDER BY created_at DESC"
        );
        
        $stmt->bind_param("sii", $type, $month, $year);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Update schedule status
     */
    public function updateStatus($scheduleId, $status) {
        $stmt = $this->conn->prepare("UPDATE schedules SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $scheduleId);
        return $stmt->execute();
    }
    
    /**
     * Delete schedule
     */
    public function delete($scheduleId) {
        $stmt = $this->conn->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->bind_param("i", $scheduleId);
        return $stmt->execute();
    }
    
    /**
     * Add history record
     */
    public function addHistory($scheduleId, $action, $changedBy, $details) {
        $stmt = $this->conn->prepare(
            "INSERT INTO schedule_history (schedule_id, action, changed_by, change_details) 
             VALUES (?, ?, ?, ?)"
        );
        
        $stmt->bind_param("isss", $scheduleId, $action, $changedBy, $details);
        return $stmt->execute();
    }
    
    /**
     * Get all schedules
     */
    public function getAll() {
        $sql = "SELECT * FROM schedules ORDER BY year DESC, month DESC, created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
