<?php
/**
 * Admission Model
 * Hospital Management System
 */

class Admission {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all admissions with search and pagination
     */
    public function getAll($search = '', $status = '', $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT a.*, p.name as patient_name, p.phone as patient_phone,
                       r.room_type, r.daily_cost, b.bed_no, b.bed_type
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                LEFT JOIN bed b ON a.bed_id = b.bed_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND p.name LIKE ?";
                $params[] = "%$search%";
            }
            
            if ($status === 'active') {
                $sql .= " AND a.discharge_date IS NULL";
            } elseif ($status === 'discharged') {
                $sql .= " AND a.discharge_date IS NOT NULL";
            }
            
            $sql .= " ORDER BY a.admission_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Admission getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of admissions
     */
    public function getTotalCount($search = '', $status = '') {
        try {
            $sql = "
                SELECT COUNT(*) as count
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND p.name LIKE ?";
                $params[] = "%$search%";
            }
            
            if ($status === 'active') {
                $sql .= " AND a.discharge_date IS NULL";
            } elseif ($status === 'discharged') {
                $sql .= " AND a.discharge_date IS NOT NULL";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Admission getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get admission by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.name as patient_name, p.phone as patient_phone, p.DOB, p.gender,
                       r.room_type, r.daily_cost, b.bed_no, b.bed_type
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                LEFT JOIN bed b ON a.bed_id = b.bed_id
                WHERE a.admission_id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Admission getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new admission
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Auto-assign an available bed within the selected room if not provided
            if (empty($data['bed_id']) && !empty($data['room_id'])) {
                $stmt = $this->pdo->prepare("
                    SELECT b.bed_id
                    FROM bed b
                    WHERE b.room_id = ? AND b.bed_status = 'Available'
                    ORDER BY b.bed_id
                    LIMIT 1
                    FOR UPDATE
                ");
                $stmt->execute([$data['room_id']]);
                $autoBedId = $stmt->fetchColumn();
                if ($autoBedId) {
                    $data['bed_id'] = (int)$autoBedId;
                }
            }

            // Insert admission
            $stmt = $this->pdo->prepare("
                INSERT INTO admission (admission_date, patient_id, room_id, bed_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['admission_date'],
                $data['patient_id'],
                $data['room_id'],
                $data['bed_id']
            ]);
            
            $admissionId = $this->pdo->lastInsertId();
            
            // Update bed status to occupied
            if ($data['bed_id']) {
                $stmt = $this->pdo->prepare("
                    UPDATE bed SET bed_status = 'Occupied' WHERE bed_id = ?
                ");
                $stmt->execute([$data['bed_id']]);
                // Optionally mark room as occupied
                $stmt = $this->pdo->prepare("UPDATE room SET room_status = 'Occupied' WHERE room_id = ?");
                $stmt->execute([$data['room_id']]);
            }
            
            $this->pdo->commit();
            return $admissionId;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Admission create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update admission
     */
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE admission 
                SET admission_date = ?, patient_id = ?, room_id = ?, bed_id = ?
                WHERE admission_id = ?
            ");
            return $stmt->execute([
                $data['admission_date'],
                $data['patient_id'],
                $data['room_id'],
                $data['bed_id'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Admission update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Discharge patient
     */
    public function discharge($id, $dischargeDate = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Get admission details
            $admission = $this->getById($id);
            if (!$admission) {
                throw new Exception("Admission not found");
            }
            
            // Update admission with discharge date
            $stmt = $this->pdo->prepare("
                UPDATE admission 
                SET discharge_date = ?
                WHERE admission_id = ?
            ");
            $stmt->execute([$dischargeDate ?: date('Y-m-d H:i:s'), $id]);
            
            // Update bed status to available
            if ($admission['bed_id']) {
                $stmt = $this->pdo->prepare("
                    UPDATE bed SET bed_status = 'Available' WHERE bed_id = ?
                ");
                $stmt->execute([$admission['bed_id']]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Admission discharge error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete admission
     */
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // Get admission details
            $admission = $this->getById($id);
            if (!$admission) {
                throw new Exception("Admission not found");
            }
            
            // Update bed status to available if bed was assigned
            if ($admission['bed_id']) {
                $stmt = $this->pdo->prepare("
                    UPDATE bed SET bed_status = 'Available' WHERE bed_id = ?
                ");
                $stmt->execute([$admission['bed_id']]);
            }
            
            // Delete admission
            $stmt = $this->pdo->prepare("DELETE FROM admission WHERE admission_id = ?");
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Admission delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get available beds
     */
    public function getAvailableBeds() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, r.room_type, r.daily_cost
                FROM bed b
                JOIN room r ON b.room_id = r.room_id
                WHERE b.bed_status = 'Available'
                ORDER BY r.room_type, b.bed_no
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Admission getAvailableBeds error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available beds by room
     */
    public function getAvailableBedsByRoom($roomId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.bed_id, b.bed_no, b.bed_type, r.room_type, r.daily_cost
                FROM bed b
                JOIN room r ON b.room_id = r.room_id
                WHERE b.bed_status = 'Available' AND b.room_id = ?
                ORDER BY b.bed_no
            ");
            $stmt->execute([$roomId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Admission getAvailableBedsByRoom error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get current admissions (not discharged)
     */
    public function getCurrentAdmissions() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.name as patient_name, p.phone as patient_phone,
                       r.room_type, r.daily_cost, b.bed_no, b.bed_type
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                LEFT JOIN bed b ON a.bed_id = b.bed_id
                WHERE a.discharge_date IS NULL
                ORDER BY a.admission_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Admission getCurrentAdmissions error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get admission statistics
     */
    public function getStatistics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN discharge_date IS NULL THEN 1 ELSE 0 END) as current,
                    SUM(CASE WHEN discharge_date IS NOT NULL THEN 1 ELSE 0 END) as discharged,
                    SUM(CASE WHEN DATE(admission_date) = CURDATE() THEN 1 ELSE 0 END) as today_admissions
                FROM admission
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Admission getStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'current' => 0,
                'discharged' => 0,
                'today_admissions' => 0
            ];
        }
    }
    
    /**
     * Calculate admission cost
     */
    public function calculateCost($admissionId) {
        try {
            $admission = $this->getById($admissionId);
            if (!$admission) {
                return 0;
            }
            
            $admissionDate = new DateTime($admission['admission_date']);
            $dischargeDate = $admission['discharge_date'] ? new DateTime($admission['discharge_date']) : new DateTime();
            
            $days = $admissionDate->diff($dischargeDate)->days + 1;
            $dailyCost = $admission['daily_cost'] ?? 0;
            
            return $days * $dailyCost;
        } catch (Exception $e) {
            error_log("Admission calculateCost error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Validate admission data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['admission_date'])) {
            $errors[] = 'Admission date is required';
        } elseif (!strtotime($data['admission_date'])) {
            $errors[] = 'Invalid admission date';
        }
        
        if (empty($data['patient_id'])) {
            $errors[] = 'Patient is required';
        }
        
        if (empty($data['room_id'])) {
            $errors[] = 'Room assignment is required';
        }
        
        // If a specific bed is provided, verify it belongs to the selected room and is available
        if (!empty($data['bed_id'])) {
            try {
                $stmt = $this->pdo->prepare("SELECT room_id, bed_status FROM bed WHERE bed_id = ?");
                $stmt->execute([$data['bed_id']]);
                $bed = $stmt->fetch();
                if (!$bed) {
                    $errors[] = 'Invalid bed selected';
                } else {
                    if (!empty($data['room_id']) && (int)$bed['room_id'] !== (int)$data['room_id']) {
                        $errors[] = 'Selected bed does not belong to the chosen room';
                    }
                    if ($bed['bed_status'] !== 'Available') {
                        $errors[] = 'Selected bed is not available';
                    }
                }
            } catch (PDOException $e) {
                error_log('Admission validate bed check error: ' . $e->getMessage());
                $errors[] = 'Unable to validate bed selection';
            }
        }
        
        return $errors;
    }
}
?>
