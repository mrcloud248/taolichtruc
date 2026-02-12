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
     * Generate daily schedule for a month with advanced constraint satisfaction
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
            $allStaff = $this->staffModel->getAllActive();
            $staffList = array_filter($allStaff, function($staff) use ($staffIds) {
                return in_array($staff['id'], $staffIds);
            });
            $staffList = array_values($staffList);
        } else {
            $staffList = $this->staffModel->getAllActive();
        }
        
        if (empty($staffList)) {
            return ['success' => false, 'message' => 'Không có nhân viên nào'];
        }
        
        $staffCount = count($staffList);
        $staffIds = array_map(function($s) { return $s['id']; }, $staffList);
        
        // Create schedule record
        $scheduleId = $this->create(SCHEDULE_DAILY, $month, $year, $generatedBy);
        if (!$scheduleId) {
            return ['success' => false, 'message' => 'Không thể tạo lịch'];
        }
        
        // Get all dates and calculate total shifts needed
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $shifts = []; // Array of [date, dayOfMonth, dayOfWeek, shiftType]
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $dayOfWeek = date('N', strtotime($date)); // 1=Monday, 7=Sunday
            
            if ($dayOfWeek == 7) { // Sunday
                $shifts[] = ['date' => $date, 'day' => $day, 'dayOfWeek' => $dayOfWeek, 'type' => SHIFT_SUNDAY_MORNING];
                $shifts[] = ['date' => $date, 'day' => $day, 'dayOfWeek' => $dayOfWeek, 'type' => SHIFT_SUNDAY_EVENING];
            } else { // Weekday
                $shifts[] = ['date' => $date, 'day' => $day, 'dayOfWeek' => $dayOfWeek, 'type' => SHIFT_WEEKDAY_EVENING];
            }
        }
        
        // CRITICAL: Track which staff have worked each specific shift pattern (day of week + shift type)
        // shiftPatternAssignments["dayOfWeek_shiftType"] = [staffId1, staffId2, ...]
        // Example: shiftPatternAssignments["1_WEEKDAY_EVENING"] = [5, 8] (Monday evening)
        //          shiftPatternAssignments["7_SUNDAY_MORNING"] = [12, 5] (Sunday morning)
        $shiftPatternAssignments = [];
        
        // Track staff shift count for fairness
        $staffShiftCount = array_fill_keys($staffIds, 0);
        
        // Track last assigned staff to avoid consecutive assignments
        $lastAssignedStaffId = null;
        
        // Assign first shift
        $firstShift = $shifts[0];
        $firstShiftPattern = $firstShift['dayOfWeek'] . '_' . $firstShift['type'];
        
        // If no first day staff specified, pick randomly from selected staff
        if ($firstDayStaffId === null || $firstDayStaffId === 0) {
            $firstDayStaffId = $staffIds[array_rand($staffIds)];
        }
        
        $this->addShift($scheduleId, $firstDayStaffId, $firstShift['date'], $firstShift['type'],
                       $SHIFT_TIMES[$firstShift['type']]['start'],
                       $SHIFT_TIMES[$firstShift['type']]['end']);
        $shiftPatternAssignments[$firstShiftPattern] = [$firstDayStaffId];
        $staffShiftCount[$firstDayStaffId]++;
        $lastAssignedStaffId = $firstDayStaffId;
        
        // Assign remaining shifts
        for ($i = 1; $i < count($shifts); $i++) {
            $shift = $shifts[$i];
            $dayOfWeek = $shift['dayOfWeek'];
            $shiftType = $shift['type'];
            $shiftPattern = $dayOfWeek . '_' . $shiftType;
            
            // Find minimum and maximum shift counts
            $minShifts = min($staffShiftCount);
            $maxShifts = max($staffShiftCount);
            
            // Get staff who have already worked this shift pattern (same day of week + shift type)
            $staffWorkedThisPattern = isset($shiftPatternAssignments[$shiftPattern]) ? $shiftPatternAssignments[$shiftPattern] : [];
            
            // Count how many times this pattern has been assigned
            $patternAssignmentCount = count($staffWorkedThisPattern);
            
            // Create candidate list with multi-criteria scoring
            $candidates = [];
            foreach ($staffIds as $sid) {
                $currentShifts = $staffShiftCount[$sid];
                $hasWorkedThisPattern = in_array($sid, $staffWorkedThisPattern);
                $isLastAssigned = ($sid === $lastAssignedStaffId);
                
                // Calculate score (lower is better)
                // Priority 1: CRITICAL - Avoid same shift pattern until everyone has worked it
                // If this person has worked this pattern AND not everyone has worked it yet, huge penalty
                if ($hasWorkedThisPattern && $patternAssignmentCount < $staffCount) {
                    $patternScore = 10000; // Effectively exclude them
                } else {
                    $patternScore = 0;
                }
                
                // Priority 2: Balance - prefer those with fewer shifts
                $balanceScore = ($currentShifts - $minShifts) * 100;
                
                // Priority 3: Avoid consecutive assignments
                $consecutiveScore = $isLastAssigned ? 50 : 0;
                
                // Priority 4: Small randomness for variety
                $randomScore = rand(0, 5);
                
                $totalScore = $patternScore + $balanceScore + $consecutiveScore + $randomScore;
                
                $candidates[] = [
                    'id' => $sid,
                    'score' => $totalScore,
                    'count' => $currentShifts,
                    'hasWorkedPattern' => $hasWorkedThisPattern,
                    'isLastAssigned' => $isLastAssigned
                ];
            }
            
            // Sort by score (ascending - lower is better)
            usort($candidates, function($a, $b) {
                return $a['score'] - $b['score'];
            });
            
            // Pick the best candidate
            $selectedStaffId = $candidates[0]['id'];
            
            // If best candidate is last assigned AND there's a second option with similar score, pick second
            if ($candidates[0]['isLastAssigned'] && count($candidates) > 1) {
                if (($candidates[1]['score'] - $candidates[0]['score']) < 100) {
                    $selectedStaffId = $candidates[1]['id'];
                }
            }
            
            $this->addShift($scheduleId, $selectedStaffId, $shift['date'], $shift['type'],
                           $SHIFT_TIMES[$shift['type']]['start'],
                           $SHIFT_TIMES[$shift['type']]['end']);
            
            // Track this assignment
            if (!isset($shiftPatternAssignments[$shiftPattern])) {
                $shiftPatternAssignments[$shiftPattern] = [];
            }
            $shiftPatternAssignments[$shiftPattern][] = $selectedStaffId;
            $staffShiftCount[$selectedStaffId]++;
            $lastAssignedStaffId = $selectedStaffId;
        }
        
        // Log history
        $this->addHistory($scheduleId, 'created', $generatedBy, 'Tạo lịch trực ngày tự động');
        
        return ['success' => true, 'scheduleId' => $scheduleId];
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
