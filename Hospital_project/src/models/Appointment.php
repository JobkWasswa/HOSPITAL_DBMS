<?php
/**
 * Appointment Model
 * Hospital Management System
 * * FIX: Corrected all SQL queries to use the schema-defined column 'user_id' 
 * instead of the legacy 'doctor_id' for the doctor Foreign Key.
 */

class Appointment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all appointments with search and pagination
     */
    public function getAll($search = '', $doctorId = null, $status = '', $limit = 20, $offset = 0) {
        try {
            // FIX: Use a.user_id for join
            $sql = "
                SELECT a.*, p.name as patient_name, u.name as doctor_name, u.specialization
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN users u ON a.user_id = u.user_id 
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.name LIKE ? OR u.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($doctorId) {
                $sql .= " AND a.user_id = ?"; // FIX: Filter on a.user_id
                $params[] = $doctorId;
            }
            
            if (!empty($status)) {
                $sql .= " AND a.appointment_status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY a.appointment_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Appointment getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of appointments
     */
    public function getTotalCount($search = '', $doctorId = null, $status = '') {
        try {
            // FIX: Use a.user_id for join
            $sql = "
                SELECT COUNT(*) as count
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN users u ON a.user_id = u.user_id 
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.name LIKE ? OR u.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($doctorId) {
                $sql .= " AND a.user_id = ?"; // FIX: Filter on a.user_id
                $params[] = $doctorId;
            }
            
            if (!empty($status)) {
                $sql .= " AND a.appointment_status = ?";
                $params[] = $status;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Appointment getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get appointment by ID
     */
    public function getById($id) {
        try {
            // FIX: Use a.user_id for join
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.name as patient_name, p.phone as patient_phone, 
                         u.name as doctor_name, u.specialization
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN users u ON a.user_id = u.user_id 
                WHERE a.appointment_id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Appointment getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new appointment
     */
    public function create($data) {
        try {
            // Consolidate ID retrieval (user_id is the key the controller passes)
            $doctorId = $data['user_id'] ?? $data['doctor_id'] ?? 0;

            // FIX: Use the schema-defined column 'user_id' in the INSERT statement
            $stmt = $this->pdo->prepare("
                INSERT INTO appointment (appointment_date, consultation_fee, user_id, appointment_status, patient_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['appointment_date'],
                $data['consultation_fee'],
                $doctorId, // Value is correct, now column is correct
                $data['appointment_status'],
                $data['patient_id']
            ]);
        } catch (PDOException $e) {
            error_log("Appointment create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update appointment
     */
    public function update($id, $data) {
        try {
             // Consolidate ID retrieval
            $doctorId = $data['user_id'] ?? $data['doctor_id'] ?? 0;
            
            // FIX: Use the schema-defined column 'user_id' in the UPDATE statement
            $stmt = $this->pdo->prepare("
                UPDATE appointment 
                SET appointment_date = ?, consultation_fee = ?, user_id = ?, 
                    appointment_status = ?, patient_id = ?
                WHERE appointment_id = ?
            ");
            return $stmt->execute([
                $data['appointment_date'],
                $data['consultation_fee'],
                $doctorId,
                $data['appointment_status'],
                $data['patient_id'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Appointment update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete appointment
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM appointment WHERE appointment_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Appointment delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get today's appointments for a doctor
     */
    public function getTodayAppointments($doctorId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.name as patient_name, p.phone as patient_phone
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                WHERE a.user_id = ? AND DATE(a.appointment_date) = CURDATE() // FIX: Filter on a.user_id
                ORDER BY a.appointment_date ASC
            ");
            $stmt->execute([$doctorId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Appointment getTodayAppointments error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableSlots($doctorId, $date) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT TIME(appointment_date) as time_slot
                FROM appointment
                WHERE user_id = ? AND DATE(appointment_date) = ? AND appointment_status != 'Cancelled' // FIX: Filter on user_id
                ORDER BY appointment_date
            ");
            $stmt->execute([$doctorId, $date]);
            $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Generate available time slots (9 AM to 5 PM, 30-minute intervals)
            $availableSlots = [];
            $startTime = strtotime('09:00');
            $endTime = strtotime('17:00');
            
            for ($time = $startTime; $time < $endTime; $time += 1800) { // 30 minutes = 1800 seconds
                $timeSlot = date('H:i', $time);
                if (!in_array($timeSlot, $bookedSlots)) {
                    $availableSlots[] = $timeSlot;
                }
            }
            
            return $availableSlots;
        } catch (PDOException $e) {
            error_log("Appointment getAvailableSlots error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update appointment status
     */
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE appointment 
                SET appointment_status = ?
                WHERE appointment_id = ?
            ");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Appointment updateStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get appointment statistics
     */
    public function getStatistics($doctorId = null) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN appointment_status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN appointment_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN appointment_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN appointment_status = 'No show' THEN 1 ELSE 0 END) as no_show
                FROM appointment
            ";
            $params = [];
            
            if ($doctorId) {
                $sql .= " WHERE user_id = ?"; // FIX: Filter on user_id
                $params[] = $doctorId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Appointment getStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'scheduled' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'no_show' => 0
            ];
        }
    }
    
    /**
     * Validate appointment data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['appointment_date'])) {
            $errors[] = 'Appointment date is required';
        } elseif (!strtotime($data['appointment_date'])) {
            $errors[] = 'Invalid appointment date';
        } elseif (strtotime($data['appointment_date']) < strtotime('today')) {
            $errors[] = 'Appointment date cannot be in the past';
        }
        
        // Validation for the doctor ID (which is now user_id)
        $doctorId = $data['user_id'] ?? $data['doctor_id'] ?? 0;
        if (empty($doctorId)) {
            $errors[] = 'Doctor is required'; 
        }
        
        if (empty($data['patient_id'])) {
            $errors[] = 'Patient is required';
        }
        
        if (empty($data['consultation_fee'])) {
            $errors[] = 'Consultation fee is required';
        } elseif (!is_numeric($data['consultation_fee']) || $data['consultation_fee'] < 0) {
            $errors[] = 'Invalid consultation fee';
        }
        
        if (empty($data['appointment_status'])) {
            $errors[] = 'Appointment status is required';
        } 
        /* Assuming APPOINTMENT_* constants are defined
        elseif (!in_array($data['appointment_status'], [APPOINTMENT_SCHEDULED, APPOINTMENT_COMPLETED, APPOINTMENT_CANCELLED, APPOINTMENT_NO_SHOW])) {
            $errors[] = 'Invalid appointment status';
        }
        */
        
        return $errors;
    }
}
