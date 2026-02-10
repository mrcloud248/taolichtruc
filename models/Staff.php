<?php
require_once __DIR__ . '/../config/database.php';

class Staff {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all active staff
     */
    public function getAllActive() {
        $sql = "SELECT * FROM staff WHERE is_active = 1 ORDER BY name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get all staff (including inactive)
     */
    public function getAll() {
        $sql = "SELECT * FROM staff ORDER BY is_active DESC, name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get staff by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Create new staff
     */
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO staff (name, max_shifts_per_week, max_shifts_per_month) 
             VALUES (?, ?, ?)"
        );
        
        $stmt->bind_param(
            "sii",
            $data['name'],
            $data['max_shifts_per_week'],
            $data['max_shifts_per_month']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Update staff
     */
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE staff 
             SET name = ?, max_shifts_per_week = ?, max_shifts_per_month = ?, is_active = ?
             WHERE id = ?"
        );
        
        $stmt->bind_param(
            "siiii",
            $data['name'],
            $data['max_shifts_per_week'],
            $data['max_shifts_per_month'],
            $data['is_active'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete staff
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Get staff constraints
     */
    public function getConstraints($staffId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM staff_constraints WHERE staff_id = ? ORDER BY created_at DESC"
        );
        $stmt->bind_param("i", $staffId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Add constraint
     */
    public function addConstraint($staffId, $type, $value, $startDate = null, $endDate = null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO staff_constraints (staff_id, constraint_type, constraint_value, start_date, end_date) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param("issss", $staffId, $type, $value, $startDate, $endDate);
        return $stmt->execute();
    }
    
    /**
     * Delete constraint
     */
    public function deleteConstraint($constraintId) {
        $stmt = $this->conn->prepare("DELETE FROM staff_constraints WHERE id = ?");
        $stmt->bind_param("i", $constraintId);
        return $stmt->execute();
    }
    
    /**
     * Check if staff has day off
     */
    public function hasDayOff($staffId, $date) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM staff_constraints 
             WHERE staff_id = ? AND constraint_type = 'day_off' 
             AND constraint_value = ?"
        );
        
        $stmt->bind_param("is", $staffId, $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }
    
    /**
     * Get staff shift count for a month
     */
    public function getShiftCount($staffId, $month, $year) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM schedule_shifts ss
             JOIN schedules s ON ss.schedule_id = s.id
             WHERE ss.staff_id = ? AND s.month = ? AND s.year = ?"
        );
        
        $stmt->bind_param("iii", $staffId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
}
?>
