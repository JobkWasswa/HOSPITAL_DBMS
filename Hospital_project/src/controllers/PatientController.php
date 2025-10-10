<?php
/**
 * Patient Controller
 * Hospital Management System
 */

require_once __DIR__ . '/../models/Patient.php';

class PatientController {
    private $patientModel;
    private $auth;
    
    public function __construct($pdo, $auth) {
        $this->patientModel = new Patient($pdo);
        $this->auth = $auth;
    }
    
    /**
     * Handle patient list request
     */
    public function index() {
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $patients = $this->patientModel->getAll($search, $limit, $offset);
        $totalPatients = $this->patientModel->getTotalCount($search);
        $totalPages = ceil($totalPatients / $limit);
        
        return [
            'patients' => $patients,
            'totalPatients' => $totalPatients,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
    }
    
    /**
     * Handle patient view request
     */
    public function view($id) {
        $patient = $this->patientModel->getById($id);
        
        if (!$patient) {
            return ['error' => 'Patient not found'];
        }
        
        $medicalHistory = $this->patientModel->getMedicalHistory($id);
        $treatments = $this->patientModel->getTreatments($id);
        $appointments = $this->patientModel->getAppointments($id);
        
        return [
            'patient' => $patient,
            'medicalHistory' => $medicalHistory,
            'treatments' => $treatments,
            'appointments' => $appointments
        ];
    }
    
    /**
     * Handle patient create request
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'DOB' => $_POST['DOB'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'address' => trim($_POST['address'] ?? ''),
                'phone' => trim($_POST['phone'] ?? '')
            ];
            
            $errors = $this->patientModel->validate($data);
            
            if (empty($errors)) {
                if ($this->patientModel->create($data)) {
                    return ['success' => 'Patient created successfully'];
                } else {
                    return ['error' => 'Failed to create patient'];
                }
            } else {
                return ['errors' => $errors, 'data' => $data];
            }
        }
        
        return [];
    }
    
    /**
     * Handle patient update request
     */
    public function update($id) {
        $patient = $this->patientModel->getById($id);
        
        if (!$patient) {
            return ['error' => 'Patient not found'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'DOB' => $_POST['DOB'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'address' => trim($_POST['address'] ?? ''),
                'phone' => trim($_POST['phone'] ?? '')
            ];
            
            $errors = $this->patientModel->validate($data);
            
            if (empty($errors)) {
                if ($this->patientModel->update($id, $data)) {
                    return ['success' => 'Patient updated successfully'];
                } else {
                    return ['error' => 'Failed to update patient'];
                }
            } else {
                return ['errors' => $errors, 'data' => $data];
            }
        }
        
        return ['patient' => $patient];
    }
    
    /**
     * Handle patient delete request
     */
    public function delete($id) {
        $patient = $this->patientModel->getById($id);
        
        if (!$patient) {
            return ['error' => 'Patient not found'];
        }
        
        if ($this->patientModel->delete($id)) {
            return ['success' => 'Patient deleted successfully'];
        } else {
            return ['error' => 'Failed to delete patient'];
        }
    }
    
    /**
     * Handle patient search request (AJAX)
     */
    public function search() {
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            return ['patients' => []];
        }
        
        $patients = $this->patientModel->search($query);
        return ['patients' => $patients];
    }
}
?>
