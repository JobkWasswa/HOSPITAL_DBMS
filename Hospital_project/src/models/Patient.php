<?php
/**
 * Patient Model
 * Hospital Management System
 * * FIX: Updated joins to use 'users' table instead of 'doctor' table.
 */

class Patient {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all patients with search and pagination
     */
    public function getAll($search = '', $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT * FROM patient";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE name LIKE ? OR phone LIKE ?";
                $params = ["%$search%", "%$search%"];
            }
            
            $sql .= " ORDER BY patient_id DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Patient getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of patients
     */
    public function getTotalCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as count FROM patient";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE name LIKE ? OR phone LIKE ?";
                $params = ["%$search%", "%$search%"];
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Patient getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get patient by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM patient WHERE patient_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Patient getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new patient
     */
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO patient (name, DOB, gender, address, phone) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'],
                $data['DOB'],
                $data['gender'],
                $data['address'],
                $data['phone']
            ]);
        } catch (PDOException $e) {
            error_log("Patient create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update patient
     */
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE patient 
                SET name = ?, DOB = ?, gender = ?, address = ?, phone = ?
                WHERE patient_id = ?
            ");
            return $stmt->execute([
                $data['name'],
                $data['DOB'],
                $data['gender'],
                $data['address'],
                $data['phone'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Patient update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete patient
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM patient WHERE patient_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Patient delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search patients by name or phone
     */
    public function search($query) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM patient 
                WHERE name LIKE ? OR phone LIKE ?
                ORDER BY name
                LIMIT 10
            ");
            $stmt->execute(["%$query%", "%$query%"]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Patient search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get patient medical history
     */
    public function getMedicalHistory($patientId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM medical_history 
                WHERE patient_id = ? 
                ORDER BY diagnosis_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Patient getMedicalHistory error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get patient treatments
     */
    public function getTreatments($patientId) {
        try {
            // FIX: Changed JOIN from 'doctor' (d) to 'users' (u)
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.name as doctor_name 
                FROM treatment t
                JOIN users u ON t.doctor_id = u.user_id 
                WHERE t.patient_id = ? 
                ORDER BY t.treatment_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Patient getTreatments error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get patient appointments
     */
    public function getAppointments($patientId) {
        try {
            // FIX: Changed JOIN from 'doctor' (d) to 'users' (u)
            $stmt = $this->pdo->prepare("
                SELECT a.*, u.name as doctor_name 
                FROM appointment a
                JOIN users u ON a.doctor_id = u.user_id 
                WHERE a.patient_id = ? 
                ORDER BY a.appointment_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Patient getAppointments error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate patient data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }
        
        if (empty($data['DOB'])) {
            $errors[] = 'Date of birth is required';
        } elseif (!strtotime($data['DOB'])) {
            $errors[] = 'Invalid date of birth';
        }
        
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required';
        } elseif (!in_array($data['gender'], [GENDER_MALE, GENDER_FEMALE, GENDER_OTHER])) {
            $errors[] = 'Invalid gender';
        }
        
        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            $errors[] = 'Invalid phone number format';
        }
        
        return $errors;
    }
}
?>