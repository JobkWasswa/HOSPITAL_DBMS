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
    
    // -----------------------------------------------------------------
    // READ OPERATIONS
    // -----------------------------------------------------------------
    
    /**
     * Handle admission list request
     * ✅ FIX: Modified to run a joined query to fetch Patient Name and Room Info directly.
     */
    public function index() {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $params = [];
        $where = "1=1";
        
        // 1. Handle Search (by Patient Name)
        if (!empty($search)) {
            $where .= " AND p.name LIKE ?";
            $params[] = "%$search%";
        }
        
        // 2. Handle Status (Active/Discharged - using discharge_date IS NULL)
        if ($status === 'Active') {
            $where .= " AND a.discharge_date IS NULL";
        } elseif ($status === 'Discharged') {
            $where .= " AND a.discharge_date IS NOT NULL";
        }
        // If $status is empty or 'All', no filter is applied.

        // 3. Main Query (FETCH DATA)
        $sql = "
            SELECT 
                a.admission_id, 
                a.admission_date, 
                a.discharge_date,
                a.patient_id,
                a.room_id,
                p.name AS patient_name,             -- ✅ ADDED: Patient Name
                r.room_type,                        -- ✅ ADDED: Room Type
                r.daily_cost                        -- ✅ ADDED: Room Cost
            FROM 
                admission a
            JOIN 
                patient p ON a.patient_id = p.patient_id
            JOIN 
                room r ON a.room_id = r.room_id
            WHERE 
                {$where}
            ORDER BY 
                a.admission_date DESC
            LIMIT ? OFFSET ?
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $admissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Total Count Query
            $countSql = "
                SELECT 
                    COUNT(a.admission_id) 
                FROM 
                    admission a
                JOIN 
                    patient p ON a.patient_id = p.patient_id
                WHERE 
                    {$where}
            ";
            $countStmt = $this->pdo->prepare($countSql);
            // Execute count query with search/status parameters only
            $countStmt->execute($params); 
            $totalAdmissions = $countStmt->fetchColumn();

        } catch (PDOException $e) {
            error_log("AdmissionController Index Error: " . $e->getMessage());
            return ['error' => 'Database error fetching admissions list.'];
        }

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
     * ✅ FIX: Updated to ensure joined data (Patient/Room name) is fetched for view.
     */
    public function view($id) {
        
        $sql = "
            SELECT 
                a.*, 
                p.name AS patient_name,
                r.room_type,
                r.daily_cost
            FROM 
                admission a
            JOIN 
                patient p ON a.patient_id = p.patient_id
            JOIN 
                room r ON a.room_id = r.room_id
            WHERE 
                a.admission_id = ?
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $admission = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdmissionController View Error: " . $e->getMessage());
            return ['error' => 'Database error fetching admission details.'];
        }
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        // This line assumes the calculateCost method exists and works on the model
        $cost = $this->admissionModel->calculateCost($id);
        
        return [
            'admission' => $admission,
            'cost' => $cost
        ];
    }

    // -----------------------------------------------------------------
    // AVAILABILITY AND CREATE OPERATIONS (MODIFIED)
    // -----------------------------------------------------------------
    
    /**
     * Get available rooms for admission form.
     * FIX: Implements the complex SQL logic to check available capacity (bed_stock > occupied_slots).
     */
    public function getAvailableRooms(): array {
        
        $sql = "
            SELECT 
                r.room_id, 
                r.room_type, 
                r.daily_cost, 
                r.bed_stock,
                (r.bed_stock - COUNT(a.admission_id)) AS available_slots
            FROM 
                room r
            LEFT JOIN 
                admission a ON r.room_id = a.room_id AND a.discharge_date IS NULL
            -- ✅ FIX: Filter non-aggregated column using WHERE
            WHERE
                r.room_status != 'Maintenance'
            GROUP BY
                r.room_id, r.room_type, r.daily_cost, r.bed_stock
            HAVING 
                COUNT(a.admission_id) < r.bed_stock
            ORDER BY 
                r.room_type, r.room_id
        ";

        try {
            $stmt = $this->pdo->query($sql);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['rooms' => $rooms];
        } catch (PDOException $e) {
            error_log("Controller Error fetching available rooms: " . $e->getMessage());
            
            // ✅ FINAL FIX: Revert to the secure, non-descriptive error message
            return ['error' => 'Database error while fetching available rooms.'];
        }
    }
    
    /**
     * Handle admission create request
     * FIX: Added Active Patient Check and Final Room Stock Check.
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'admission_date' => $_POST['admission_date'] ?? '',
                'patient_id' => intval($_POST['patient_id'] ?? 0),
                'room_id' => intval($_POST['room_id'] ?? 0),
            ];
            
            $errors = $this->admissionModel->validate($data);
            
            if (empty($errors)) {
                
                // 1. ACTIVE PATIENT CHECK: Must not have an existing active admission
                // NOTE: This assumes admissionModel has an isActiveAdmission() method
                if ($this->admissionModel->isActiveAdmission($data['patient_id'])) {
                    return ['error' => 'Patient already has an active admission and cannot be re-admitted.', 'data' => $data];
                }

                // 2. ROOM STOCK CHECK: Final verification to prevent race conditions
                // NOTE: This assumes admissionModel has a getRoomOccupancy() method
                $roomData = $this->admissionModel->getRoomOccupancy($data['room_id']);
                
                if (!$roomData || (int)$roomData['occupied_slots'] >= (int)$roomData['bed_stock']) {
                    return ['error' => 'The selected room is now full or unavailable. Please choose another room.', 'data' => $data];
                }

                // 3. Perform Admission
                // NOTE: This assumes admissionModel has a create() method
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
    
    // -----------------------------------------------------------------
    // UPDATE AND DELETE OPERATIONS
    // -----------------------------------------------------------------

    /**
     * Handle admission update request
     */
    public function update($id) {
        // This will fetch the admission WITHOUT joined data (fine for just getting initial form data)
        $admission = $this->admissionModel->getById($id); 
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'admission_date' => $_POST['admission_date'] ?? '',
                'patient_id' => intval($_POST['patient_id'] ?? 0),
                'room_id' => intval($_POST['room_id'] ?? 0),
            ];
            
            // NOTE: This assumes admissionModel has a validate() method
            $errors = $this->admissionModel->validate($data);
            
            if (empty($errors)) {
                 
                // NOTE: This assumes admissionModel has an update() method
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
        // This will fetch the admission WITHOUT joined data (fine for just checking status)
        $admission = $this->admissionModel->getById($id); 
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        if ($admission['discharge_date']) {
            return ['error' => 'Patient is already discharged'];
        }
        
        $dischargeDate = $_POST['discharge_date'] ?? date('Y-m-d H:i:s');
        
        // NOTE: This assumes admissionModel has a discharge() method
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
        // This will fetch the admission WITHOUT joined data (fine for just checking existence)
        $admission = $this->admissionModel->getById($id); 
        
        if (!$admission) {
            return ['error' => 'Admission not found'];
        }
        
        // NOTE: This assumes admissionModel has a delete() method
        if ($this->admissionModel->delete($id)) {
            return ['success' => 'Admission deleted successfully'];
        } else {
            return ['error' => 'Failed to delete admission'];
        }
    }
    
    // -----------------------------------------------------------------
    // UTILITY METHODS
    // -----------------------------------------------------------------
    
    /**
     * Get current admissions
     * NOTE: This assumes admissionModel has a getCurrentAdmissions() method
     */
    public function getCurrentAdmissions() {
        $admissions = $this->admissionModel->getCurrentAdmissions();
        return ['admissions' => $admissions];
    }
    
    /**
     * Get admission statistics
     * NOTE: This assumes admissionModel has a getStatistics() method
     */
    public function getStatistics() {
        $stats = $this->admissionModel->getStatistics();
        return ['statistics' => $stats];
    }
}
?>