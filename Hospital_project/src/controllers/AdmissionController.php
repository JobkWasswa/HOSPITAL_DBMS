<?php
/**
 * Admission Controller
 * Hospital Management System
 */

require_once __DIR__ . '/../models/Admission.php';

class AdmissionController {
    private $admissionModel;
    private $auth;
    private $pdo;
    
    public function __construct($pdo, $auth) {
        $this->admissionModel = new Admission($pdo);
        $this->auth = $auth;
        $this->pdo = $pdo;
    }
    
    /**
     * Handle admission list request
     */
    public function index() {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $admissions = $this->admissionModel->getAll($search, $status, $limit, $offset);
        $totalAdmissions = $this->admissionModel->getTotalCount($search, $status);
        $totalPages = ceil($totalAdmissions / $limit);
        
        return [
            'admissions' => $admissions,
            'totalAdmissions' => $totalAdmissions,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'status' => $status
        ];
    }
    
    /**
     * Handle admission view request
     */
    public function view($id) {
        $admission = $this->admissionModel->getById($id);
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        $cost = $this->admissionModel->calculateCost($id);
        
        return [
            'admission' => $admission,
            'cost' => $cost
        ];
    }
    
    /**
     * Handle admission create request
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'admission_date' => $_POST['admission_date'] ?? '',
                'patient_id' => intval($_POST['patient_id'] ?? 0),
                'room_id' => intval($_POST['room_id'] ?? 0),
                'bed_id' => intval($_POST['bed_id'] ?? 0)
            ];
            
            $errors = $this->admissionModel->validate($data);
            
            if (empty($errors)) {
                $admissionId = $this->admissionModel->create($data);
                if ($admissionId) {
                    return ['success' => 'Patient admitted successfully', 'admission_id' => $admissionId];
                } else {
                    return ['error' => 'Failed to admit patient'];
                }
            } else {
                return ['errors' => $errors, 'data' => $data];
            }
        }
        
        return [];
    }
    
    /**
     * Handle admission update request
     */
    public function update($id) {
        $admission = $this->admissionModel->getById($id);
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'admission_date' => $_POST['admission_date'] ?? '',
                'patient_id' => intval($_POST['patient_id'] ?? 0),
                'room_id' => intval($_POST['room_id'] ?? 0),
                'bed_id' => intval($_POST['bed_id'] ?? 0)
            ];
            
            $errors = $this->admissionModel->validate($data);
            
            if (empty($errors)) {
                if ($this->admissionModel->update($id, $data)) {
                    return ['success' => 'Admission updated successfully'];
                } else {
                    return ['error' => 'Failed to update admission'];
                }
            } else {
                return ['errors' => $errors, 'data' => $data];
            }
        }
        
        return ['admission' => $admission];
    }
    
    /**
     * Handle patient discharge request
     */
    public function discharge($id) {
        $admission = $this->admissionModel->getById($id);
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        if ($admission['discharge_date']) {
            return ['error' => 'Patient is already discharged'];
        }
        
        $dischargeDate = $_POST['discharge_date'] ?? date('Y-m-d H:i:s');
        
        if ($this->admissionModel->discharge($id, $dischargeDate)) {
            return ['success' => 'Patient discharged successfully'];
        } else {
            return ['error' => 'Failed to discharge patient'];
        }
    }
    
    /**
     * Handle admission delete request
     */
    public function delete($id) {
        $admission = $this->admissionModel->getById($id);
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        if ($this->admissionModel->delete($id)) {
            return ['success' => 'Admission deleted successfully'];
        } else {
            return ['error' => 'Failed to delete admission'];
        }
    }
    
    /**
     * Get available beds for admission
     */
    public function getAvailableBeds() {
        $beds = $this->admissionModel->getAvailableBeds();
        return ['beds' => $beds];
    }
    
    /**
     * Get current admissions
     */
    public function getCurrentAdmissions() {
        $admissions = $this->admissionModel->getCurrentAdmissions();
        return ['admissions' => $admissions];
    }
    
    /**
     * Get admission statistics
     */
    public function getStatistics() {
        $stats = $this->admissionModel->getStatistics();
        return ['statistics' => $stats];
    }
}
?>
