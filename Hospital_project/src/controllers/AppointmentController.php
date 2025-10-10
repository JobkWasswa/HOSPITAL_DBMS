<?php
/**
 * Appointment Controller
 * Hospital Management System
 */

require_once __DIR__ . '/../models/Appointment.php';

class AppointmentController {
    private $appointmentModel;
    private $auth;
    private $pdo;
    
    public function __construct($pdo, $auth) {
        $this->appointmentModel = new Appointment($pdo);
        $this->auth = $auth;
        $this->pdo = $pdo;
    }
    
    /**
     * Handle appointment list request
     */
    public function index() {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $doctorId = null;
        if ($this->auth->hasRole(ROLE_DOCTOR)) {
            // Get doctor ID for current user
            $currentUser = $this->auth->getCurrentUser();
            $stmt = $this->pdo->prepare("SELECT doctor_id FROM users WHERE user_id = ?");
            $stmt->execute([$currentUser['user_id']]);
            $user = $stmt->fetch();
            $doctorId = $user['doctor_id'] ?? null;
        }
        
        $appointments = $this->appointmentModel->getAll($search, $doctorId, $status, $limit, $offset);
        $totalAppointments = $this->appointmentModel->getTotalCount($search, $doctorId, $status);
        $totalPages = ceil($totalAppointments / $limit);
        
        return [
            'appointments' => $appointments,
            'totalAppointments' => $totalAppointments,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'status' => $status
        ];
    }
    
    /**
     * Handle appointment view request
     */
    public function view($id) {
        $appointment = $this->appointmentModel->getById($id);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        return ['appointment' => $appointment];
    }
    
    /**
     * Handle appointment create request
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'appointment_date' => $_POST['appointment_date'] ?? '',
                'consultation_fee' => floatval($_POST['consultation_fee'] ?? 0),
                'doctor_id' => intval($_POST['doctor_id'] ?? 0),
                'appointment_status' => $_POST['appointment_status'] ?? APPOINTMENT_SCHEDULED,
                'patient_id' => intval($_POST['patient_id'] ?? 0)
            ];
            
            $errors = $this->appointmentModel->validate($data);
            
            if (empty($errors)) {
                if ($this->appointmentModel->create($data)) {
                    return ['success' => 'Appointment created successfully'];
                } else {
                    return ['error' => 'Failed to create appointment'];
                }
            } else {
                return ['errors' => $errors, 'data' => $data];
            }
        }
        
        return [];
    }
    
    /**
     * Handle appointment update request
     */
    public function update($id) {
        $appointment = $this->appointmentModel->getById($id);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'appointment_date' => $_POST['appointment_date'] ?? '',
                'consultation_fee' => floatval($_POST['consultation_fee'] ?? 0),
                'doctor_id' => intval($_POST['doctor_id'] ?? 0),
                'appointment_status' => $_POST['appointment_status'] ?? APPOINTMENT_SCHEDULED,
                'patient_id' => intval($_POST['patient_id'] ?? 0)
            ];
            
            $errors = $this->appointmentModel->validate($data);
            
            if (empty($errors)) {
                if ($this->appointmentModel->update($id, $data)) {
                    return ['success' => 'Appointment updated successfully'];
                } else {
                    return ['error' => 'Failed to update appointment'];
                }
            } else {
                return ['errors' => $errors, 'data' => $data];
            }
        }
        
        return ['appointment' => $appointment];
    }
    
    /**
     * Handle appointment delete request
     */
    public function delete($id) {
        $appointment = $this->appointmentModel->getById($id);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        if ($this->appointmentModel->delete($id)) {
            return ['success' => 'Appointment deleted successfully'];
        } else {
            return ['error' => 'Failed to delete appointment'];
        }
    }
    
    /**
     * Handle appointment status update
     */
    public function updateStatus($id, $status) {
        $appointment = $this->appointmentModel->getById($id);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        if ($this->appointmentModel->updateStatus($id, $status)) {
            return ['success' => 'Appointment status updated successfully'];
        } else {
            return ['error' => 'Failed to update appointment status'];
        }
    }
    
    /**
     * Get available time slots for a doctor
     */
    public function getAvailableSlots() {
        $doctorId = intval($_GET['doctor_id'] ?? 0);
        $date = $_GET['date'] ?? '';
        
        if (!$doctorId || !$date) {
            return ['slots' => []];
        }
        
        $slots = $this->appointmentModel->getAvailableSlots($doctorId, $date);
        return ['slots' => $slots];
    }
    
    /**
     * Get today's appointments for current doctor
     */
    public function getTodayAppointments() {
        if (!$this->auth->hasRole(ROLE_DOCTOR)) {
            return ['appointments' => []];
        }
        
        $currentUser = $this->auth->getCurrentUser();
        $stmt = $this->pdo->prepare("SELECT doctor_id FROM users WHERE user_id = ?");
        $stmt->execute([$currentUser['user_id']]);
        $user = $stmt->fetch();
        $doctorId = $user['doctor_id'] ?? null;
        
        if (!$doctorId) {
            return ['appointments' => []];
        }
        
        $appointments = $this->appointmentModel->getTodayAppointments($doctorId);
        return ['appointments' => $appointments];
    }
    
    /**
     * Get appointment statistics
     */
    public function getStatistics() {
        $doctorId = null;
        if ($this->auth->hasRole(ROLE_DOCTOR)) {
            $currentUser = $this->auth->getCurrentUser();
            $stmt = $this->pdo->prepare("SELECT doctor_id FROM users WHERE user_id = ?");
            $stmt->execute([$currentUser['user_id']]);
            $user = $stmt->fetch();
            $doctorId = $user['doctor_id'] ?? null;
        }
        
        $stats = $this->appointmentModel->getStatistics($doctorId);
        return ['statistics' => $stats];
    }
}
?>
