<?php
/**
 * Prescription Model
 * Hospital Management System
 */

class Prescription {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all prescriptions with search and pagination
     */
    public function getAll($search = '', $patientId = null, $doctorId = null, $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT p.*, m.name as medicine_name, m.dosage, m.medicine_price,
                       t.treatment_date, t.notes as treatment_notes,
                       pat.name as patient_name, d.name as doctor_name
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN patient pat ON t.patient_id = pat.patient_id
                JOIN doctor d ON t.doctor_id = d.doctor_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (pat.name LIKE ? OR m.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($patientId) {
                $sql .= " AND t.patient_id = ?";
                $params[] = $patientId;
            }
            
            if ($doctorId) {
                $sql .= " AND t.doctor_id = ?";
                $params[] = $doctorId;
            }
            
            $sql .= " ORDER BY p.prescription_id DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Prescription getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of prescriptions
     */
    public function getTotalCount($search = '', $patientId = null, $doctorId = null) {
        try {
            $sql = "
                SELECT COUNT(*) as count
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN patient pat ON t.patient_id = pat.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (pat.name LIKE ? OR m.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($patientId) {
                $sql .= " AND t.patient_id = ?";
                $params[] = $patientId;
            }
            
            if ($doctorId) {
                $sql .= " AND t.doctor_id = ?";
                $params[] = $doctorId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Prescription getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get prescription by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, m.name as medicine_name, m.dosage, m.medicine_price, m.stock_quantity,
                       t.treatment_date, t.notes as treatment_notes, t.treatment_fee,
                       pat.name as patient_name, pat.phone as patient_phone,
                       d.name as doctor_name, d.specialization
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN patient pat ON t.patient_id = pat.patient_id
                JOIN doctor d ON t.doctor_id = d.doctor_id
                WHERE p.prescription_id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Prescription getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new prescription
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Check if medicine has enough stock
            $stmt = $this->pdo->prepare("SELECT stock_quantity FROM medicine WHERE medicine_id = ?");
            $stmt->execute([$data['medicine_id']]);
            $medicine = $stmt->fetch();
            
            if (!$medicine || $medicine['stock_quantity'] < $data['quantity']) {
                throw new Exception("Insufficient stock for this medicine");
            }
            
            // Insert prescription
            $stmt = $this->pdo->prepare("
                INSERT INTO prescription (quantity, dosage_instructions, treatment_id, medicine_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['quantity'],
                $data['dosage_instructions'],
                $data['treatment_id'],
                $data['medicine_id']
            ]);
            
            $prescriptionId = $this->pdo->lastInsertId();
            
            // Update medicine stock
            $stmt = $this->pdo->prepare("
                UPDATE medicine 
                SET stock_quantity = stock_quantity - ?
                WHERE medicine_id = ?
            ");
            $stmt->execute([$data['quantity'], $data['medicine_id']]);
            
            $this->pdo->commit();
            return $prescriptionId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Prescription create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update prescription
     */
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE prescription 
                SET quantity = ?, dosage_instructions = ?, treatment_id = ?, medicine_id = ?
                WHERE prescription_id = ?
            ");
            return $stmt->execute([
                $data['quantity'],
                $data['dosage_instructions'],
                $data['treatment_id'],
                $data['medicine_id'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Prescription update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete prescription
     */
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // Get prescription details
            $stmt = $this->pdo->prepare("
                SELECT quantity, medicine_id FROM prescription WHERE prescription_id = ?
            ");
            $stmt->execute([$id]);
            $prescription = $stmt->fetch();
            
            if (!$prescription) {
                throw new Exception("Prescription not found");
            }
            
            // Restore medicine stock
            $stmt = $this->pdo->prepare("
                UPDATE medicine 
                SET stock_quantity = stock_quantity + ?
                WHERE medicine_id = ?
            ");
            $stmt->execute([$prescription['quantity'], $prescription['medicine_id']]);
            
            // Delete prescription
            $stmt = $this->pdo->prepare("DELETE FROM prescription WHERE prescription_id = ?");
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Prescription delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get prescriptions for a treatment
     */
    public function getByTreatment($treatmentId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, m.name as medicine_name, m.dosage, m.medicine_price
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                WHERE p.treatment_id = ?
                ORDER BY p.prescription_id
            ");
            $stmt->execute([$treatmentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Prescription getByTreatment error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prescriptions for a patient
     */
    public function getByPatient($patientId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, m.name as medicine_name, m.dosage, m.medicine_price,
                       t.treatment_date, d.name as doctor_name
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN doctor d ON t.doctor_id = d.doctor_id
                WHERE t.patient_id = ?
                ORDER BY t.treatment_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Prescription getByPatient error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate prescription cost
     */
    public function calculateCost($prescriptionId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.quantity, m.medicine_price
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                WHERE p.prescription_id = ?
            ");
            $stmt->execute([$prescriptionId]);
            $prescription = $stmt->fetch();
            
            if (!$prescription) {
                return 0;
            }
            
            return $prescription['quantity'] * $prescription['medicine_price'];
        } catch (PDOException $e) {
            error_log("Prescription calculateCost error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Validate prescription data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['quantity'])) {
            $errors[] = 'Quantity is required';
        } elseif (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            $errors[] = 'Invalid quantity';
        }
        
        if (empty($data['dosage_instructions'])) {
            $errors[] = 'Dosage instructions are required';
        }
        
        if (empty($data['treatment_id'])) {
            $errors[] = 'Treatment is required';
        }
        
        if (empty($data['medicine_id'])) {
            $errors[] = 'Medicine is required';
        }
        
        return $errors;
    }
}
?>
