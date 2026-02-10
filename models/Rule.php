<?php
require_once __DIR__ . '/../config/database.php';

class Rule {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all rules
     */
    public function getAll() {
        $sql = "SELECT * FROM rules ORDER BY is_active DESC, rule_name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get active rules
     */
    public function getAllActive() {
        $sql = "SELECT * FROM rules WHERE is_active = 1 ORDER BY rule_name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get rule by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM rules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Create new rule
     */
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO rules (rule_name, rule_type, rule_value, description) 
             VALUES (?, ?, ?, ?)"
        );
        
        $stmt->bind_param("ssss", 
            $data['rule_name'],
            $data['rule_type'],
            $data['rule_value'],
            $data['description']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Update rule
     */
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE rules 
             SET rule_name = ?, rule_type = ?, rule_value = ?, is_active = ?, description = ?
             WHERE id = ?"
        );
        
        $stmt->bind_param("sssisi",
            $data['rule_name'],
            $data['rule_type'],
            $data['rule_value'],
            $data['is_active'],
            $data['description'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete rule
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM rules WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Toggle rule status
     */
    public function toggleStatus($id) {
        $stmt = $this->conn->prepare(
            "UPDATE rules SET is_active = NOT is_active WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
