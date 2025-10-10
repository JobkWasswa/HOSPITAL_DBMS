<?php
/**
 * Lab Test Model
 * Hospital Management System
 */

class LabTest {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all lab tests with search and pagination
     */
    public function getAll($search = '', $patientId = null, $status = '', $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT l.*, p.name as patient_name, p.phone as patient_phone
                FROM lab_test l
                JOIN patient p ON l.patient_id = p.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.name LIKE ? OR l.test_type LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($patientId) {
                $sql .= " AND l.patient_id = ?";
                $params[] = $patientId;
            }
            
            if ($status === 'pending') {
                $sql .= " AND l.results IS NULL";
            } elseif ($status === 'completed') {
                $sql .= " AND l.results IS NOT NULL";
            }
            
            $sql .= " ORDER BY l.test_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("LabTest getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of lab tests
     */
    public function getTotalCount($search = '', $patientId = null, $status = '') {
        try {
            $sql = "
                SELECT COUNT(*) as count
                FROM lab_test l
                JOIN patient p ON l.patient_id = p.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.name LIKE ? OR l.test_type LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($patientId) {
                $sql .= " AND l.patient_id = ?";
                $params[] = $patientId;
            }
            
            if ($status === 'pending') {
                $sql .= " AND l.results IS NULL";
            } elseif ($status === 'completed') {
                $sql .= " AND l.results IS NOT NULL";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("LabTest getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get lab test by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.*, p.name as patient_name, p.phone as patient_phone, p.DOB, p.gender
                FROM lab_test l
                JOIN patient p ON l.patient_id = p.patient_id
                WHERE l.test_id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("LabTest getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new lab test
     */
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO lab_test (test_type, test_date, test_cost, patient_id) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['test_type'],
                $data['test_date'],
                $data['test_cost'],
                $data['patient_id']
            ]);
        } catch (PDOException $e) {
            error_log("LabTest create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lab test
     */
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE lab_test 
                SET test_type = ?, test_date = ?, test_cost = ?, patient_id = ?
                WHERE test_id = ?
            ");
            return $stmt->execute([
                $data['test_type'],
                $data['test_date'],
                $data['test_cost'],
                $data['patient_id'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("LabTest update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lab test results
     */
    public function updateResults($id, $results, $notes = '') {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE lab_test 
                SET results = ?, notes = ?
                WHERE test_id = ?
            ");
            return $stmt->execute([$results, $notes, $id]);
        } catch (PDOException $e) {
            error_log("LabTest updateResults error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete lab test
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM lab_test WHERE test_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("LabTest delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get pending lab tests
     */
    public function getPendingTests() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.*, p.name as patient_name, p.phone as patient_phone
                FROM lab_test l
                JOIN patient p ON l.patient_id = p.patient_id
                WHERE l.results IS NULL
                ORDER BY l.test_date ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("LabTest getPendingTests error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get lab tests for a patient
     */
    public function getByPatient($patientId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM lab_test 
                WHERE patient_id = ?
                ORDER BY test_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("LabTest getByPatient error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get lab test statistics
     */
    public function getStatistics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN results IS NULL THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN results IS NOT NULL THEN 1 ELSE 0 END) as completed,
                    SUM(test_cost) as total_cost,
                    SUM(CASE WHEN DATE(test_date) = CURDATE() THEN 1 ELSE 0 END) as today_tests
                FROM lab_test
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("LabTest getStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'total_cost' => 0,
                'today_tests' => 0
            ];
        }
    }
    
    /**
     * Validate lab test data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['test_type'])) {
            $errors[] = 'Test type is required';
        }
        
        if (empty($data['test_date'])) {
            $errors[] = 'Test date is required';
        } elseif (!strtotime($data['test_date'])) {
            $errors[] = 'Invalid test date';
        }
        
        if (empty($data['test_cost'])) {
            $errors[] = 'Test cost is required';
        } elseif (!is_numeric($data['test_cost']) || $data['test_cost'] < 0) {
            $errors[] = 'Invalid test cost';
        }
        
        if (empty($data['patient_id'])) {
            $errors[] = 'Patient is required';
        }
        
        return $errors;
    }
}
?>
