<?php
/**
 * Admission Model
 * Hospital Management System
 * * FIXES: 
 * 1. Corrected the logic in delete() to properly check room occupancy after deletion.
 * 2. Ensured all CRUD and helper methods (getAll, getById, create, discharge, delete, etc.) are present and use JOINs for patient and room details.
 */

class Admission {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // -----------------------------------------------------------------
    // HELPER METHODS FOR ROOM/PATIENT STATUS CHECKING
    // -----------------------------------------------------------------

    /**
     * Checks if a patient currently has an active admission.
     */
    public function isActiveAdmission($patientId): bool {
        $stmt = $this->pdo->prepare("SELECT admission_id FROM admission WHERE patient_id = ? AND discharge_date IS NULL");
        $stmt->execute([$patientId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Gets the current occupancy and bed stock of a room.
     */
    public function getRoomOccupancy($roomId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT 
                r.room_id, 
                r.bed_stock,
                COUNT(a.admission_id) AS occupied_slots
            FROM 
                room r
            LEFT JOIN 
                admission a ON r.room_id = a.room_id AND a.discharge_date IS NULL
            WHERE
                r.room_id = ?
            GROUP BY
                r.room_id, r.bed_stock
        ");
        $stmt->execute([$roomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // -----------------------------------------------------------------
    // READ OPERATIONS
    // -----------------------------------------------------------------
    
    /**
     * Get all admissions with search and pagination (with joins)
     */
    public function getAll($search = '', $status = '', $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT a.*, p.name as patient_name, p.phone as patient_phone,
                        r.room_type, r.daily_cost
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Ensure FETCH_ASSOC for consistency
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
     * Get admission by ID (with joins)
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.name as patient_name, p.phone as patient_phone, p.DOB, p.gender,
                        r.room_type, r.daily_cost, r.room_number
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                WHERE a.admission_id = ?
            ");
            
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC); // Ensure FETCH_ASSOC for consistency
        } catch (PDOException $e) {
            error_log("Admission getById error: " . $e->getMessage());
            return null;
        }
    }
    
    // -----------------------------------------------------------------
    // WRITE OPERATIONS (FIXED FOR STOCK AWARENESS)
    // -----------------------------------------------------------------
    
    /**
     * Create new admission
     * FIX: Checks if room is full after insertion and updates room_status to 'Occupied' only then.
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();
            
            $roomId = $data['room_id'];

            // 1. Insert admission
            $stmt = $this->pdo->prepare("
                INSERT INTO admission (admission_date, patient_id, room_id, condition_on_admission) 
                VALUES (?, ?, ?, ?)
            ");
            // Assuming 'condition_on_admission' is a field you might want to include from the controller data
            $stmt->execute([
                $data['admission_date'],
                $data['patient_id'],
                $roomId,
                $data['condition_on_admission'] ?? null 
            ]);
            
            $admissionId = $this->pdo->lastInsertId();
            
            // 2. Check room occupancy AFTER the new admission is inserted
            $roomData = $this->getRoomOccupancy($roomId);
            
            // 3. Update room status if it is now FULLY occupied
            if ($roomData && (int)$roomData['occupied_slots'] >= (int)$roomData['bed_stock']) {
                $stmt = $this->pdo->prepare("UPDATE room SET room_status = 'Occupied' WHERE room_id = ?");
                $stmt->execute([$roomId]);
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
        // NOTE: A proper update should also handle room changes and update room statuses
        // For simplicity here, we only update admission details. Room status update logic
        // for changing rooms is complex and usually done in the Controller.
        try {
            $stmt = $this->pdo->prepare("
                UPDATE admission 
                SET admission_date = ?, patient_id = ?, room_id = ?, condition_on_admission = ?
                WHERE admission_id = ?
            ");
            return $stmt->execute([
                $data['admission_date'],
                $data['patient_id'],
                $data['room_id'],
                $data['condition_on_admission'] ?? null,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Admission update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Discharge patient
     * FIX: Checks room occupancy *after* discharge and sets room_status to 'Available' only when empty.
     */
    public function discharge($id, $dischargeDate = null) {
        try {
            $this->pdo->beginTransaction();
            
            $admission = $this->getById($id);
            if (!$admission || $admission['discharge_date'] !== null) {
                throw new Exception("Active admission not found or already discharged.");
            }
            
            $roomId = $admission['room_id'];
            
            // 1. Update admission with discharge date
            $stmt = $this->pdo->prepare("
                UPDATE admission 
                SET discharge_date = ?
                WHERE admission_id = ?
            ");
            $stmt->execute([$dischargeDate ?: date('Y-m-d H:i:s'), $id]);
            
            // 2. Check room occupancy *after* discharge (the admission is now marked discharged)
            // The getRoomOccupancy query only counts non-discharged patients, so this check is now correct.
            $roomData = $this->getRoomOccupancy($roomId);
            
            // 3. Update room status to 'Available' if occupancy is now 0
            if ($roomData && (int)$roomData['occupied_slots'] === 0) {
                $stmt = $this->pdo->prepare("
                    UPDATE room SET room_status = 'Available' WHERE room_id = ?
                ");
                $stmt->execute([$roomId]);
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
     * **FIXED:** Performs the deletion first, then checks the *new* occupancy to correctly update the room status.
     */
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            $admission = $this->getById($id);
            if (!$admission) {
                throw new Exception("Admission not found");
            }

            $roomId = $admission['room_id'];
            
            // 1. Delete admission
            $stmt = $this->pdo->prepare("DELETE FROM admission WHERE admission_id = ?");
            $stmt->execute([$id]);
            
            // 2. Check room occupancy *after* deletion (only if it was an active admission)
            if ($admission['discharge_date'] === null) {
                $roomData = $this->getRoomOccupancy($roomId);
                
                // 3. Update room status to 'Available' if occupancy is now 0
                // We check against bed_stock=0 just in case of data integrity issues, 
                // but the primary check is occupied_slots === 0
                if ($roomData && (int)$roomData['occupied_slots'] === 0 && (int)$roomData['bed_stock'] > 0) {
                    $stmt = $this->pdo->prepare("UPDATE room SET room_status = 'Available' WHERE room_id = ?");
                    $stmt->execute([$roomId]);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Admission delete error: " . $e->getMessage());
            return false;
        }
    }
    
    // -----------------------------------------------------------------
    // UTILITY METHODS
    // -----------------------------------------------------------------
    
    /**
     * Get current admissions (not discharged)
     */
    public function getCurrentAdmissions() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.name as patient_name, p.phone as patient_phone,
                        r.room_type, r.daily_cost
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                WHERE a.discharge_date IS NULL
                ORDER BY a.admission_date DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            // Using CURDATE() which is SQL function for current date without time
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN discharge_date IS NULL THEN 1 ELSE 0 END) as current,
                    SUM(CASE WHEN discharge_date IS NOT NULL THEN 1 ELSE 0 END) as discharged,
                    SUM(CASE WHEN DATE(admission_date) = CURDATE() THEN 1 ELSE 0 END) as today_admissions
                FROM admission
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
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
            // If discharge_date is null, use the current time for calculation
            $dischargeDate = $admission['discharge_date'] ? new DateTime($admission['discharge_date']) : new DateTime();
            
            // Calculate total days, ensuring at least 1 day for a same-day admission/discharge
            $interval = $admissionDate->diff($dischargeDate);
            $days = $interval->days;
            // Add 1 to count the first day, or if discharge is on the same day (diff days=0, total days=1)
            if ($admission['discharge_date'] === null || $days == 0) {
                $days = 1;
            }

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
    public function validate($data, $isUpdate = false) {
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

        // Only check for active admission on CREATE operations
        if (!$isUpdate && !empty($data['patient_id'])) {
            if ($this->isActiveAdmission($data['patient_id'])) {
                $errors[] = 'This patient already has an active admission.';
            }
        }
        
        // Final sanity check before admission
        if (empty($errors) && !$isUpdate && !empty($data['room_id'])) {
            $roomData = $this->getRoomOccupancy($data['room_id']);
            if (!$roomData || (int)$roomData['occupied_slots'] >= (int)$roomData['bed_stock']) {
                $errors[] = 'The selected room is full or does not exist.';
            }
        }
        
        return $errors;
    }
}
?>