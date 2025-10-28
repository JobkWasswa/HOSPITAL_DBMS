<?php
/**
 * Admission Management - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../config/constants.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/AdmissionController.php';

// Initialize authentication
$auth = new Auth($pdo);

// Define allowed roles based on your constants
$allowedRoles = [ROLE_NURSE, ROLE_RECEPTIONIST, ROLE_DOCTOR];
$auth->requireRoles($allowedRoles);

$currentUser = $auth->getCurrentUser();

$staffRole = $currentUser['role'] ?? 'Staff';

// Check if the current user has primary admission/discharge permissions
$canAdmit = ($staffRole === ROLE_NURSE || $staffRole === ROLE_RECEPTIONIST);
$canManage = ($staffRole === ROLE_NURSE); // Only nurses can manage/discharge/delete

// Initialize controller
$admissionController = new AdmissionController($pdo, $auth);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Initialize form data arrays
$patients = [];
$rooms = [];
$result = [];

// FIX: Initialize $success and $error for use outside of the switch/GET array checks
$success = ''; 
$error = '';

// Get all patients for form dropdowns (only need these once)
try {
    // Get all patients
    $stmt = $pdo->prepare("SELECT patient_id, name FROM patient ORDER BY name");
    $stmt->execute();
    $patients = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error fetching base form data (patients): " . $e->getMessage());
    $result['error'] = 'Database error loading patient data.';
}

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            $result = $admissionController->view($id);
        } else {
            $result = ['error' => 'Admission ID required'];
        }
        break;
        
    case 'add':
        if ($canAdmit) {
            // Check for POST request for form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // The AdmissionController::create() handles all validation (active patient & room capacity)
                $result = $admissionController->create(); 
                if (isset($result['success'])) {
                    header('Location: admissions.php?success=' . urlencode($result['success']));
                    exit();
                }
            }
            
            // FIX: Fetch rooms using the Controller's stock-aware logic
            try {
                // Use the Controller method to fetch ONLY rooms with available bed stock
                $roomResult = $admissionController->getAvailableRooms();
                
                if (isset($roomResult['error'])) {
                    $result['error'] = $roomResult['error'];
                    $rooms = [];
                } else {
                    // Overwrite the $rooms list for the 'add' form view
                    $rooms = $roomResult['rooms'] ?? []; 
                }

            } catch (Exception $e) {
                error_log("Error loading available rooms for form: " . $e->getMessage());
                $result['error'] = 'Error loading available room data: ' . $e->getMessage();
                $rooms = [];
            }

        } else {
            $result = ['error' => 'Access denied. Only authorized staff (Nurses/Receptionists) can admit patients.'];
        }
        break;
        
    case 'edit':
    case 'discharge':
    case 'delete':
        if ($canManage && $id) {
            if ($action === 'edit') {
                $result = $admissionController->update($id);
            } elseif ($action === 'discharge') {
                // The AdmissionController::discharge() must handle the room_status update!
                $result = $admissionController->discharge($id); 
            } elseif ($action === 'delete') {
                $result = $admissionController->delete($id);
            }
            
            if (isset($result['success'])) {
                header('Location: admissions.php?success=' . urlencode($result['success']));
                exit();
            }
            
            // If action is discharge, fetch the admission data for the discharge form view
            if ($action === 'discharge' && !isset($result['error'])) {
                $result['admission'] = $admissionController->view($id)['admission'] ?? null;
                if (!$result['admission']) {
                    $result = ['error' => 'Admission not found for discharge.'];
                }
            }
            
        } else {
            $result = ['error' => 'Access denied. Only Nurses can perform the requested action.'];
        }
        break;

    default:
        // Controller::index() must fetch admissions with patient and room details
        $result = $admissionController->index(); 
        break;
}

// FIX: Update error/success handling to combine $result and $_GET values
$error = $result['error'] ?? ($_GET['error'] ?? ''); 
$success = $result['success'] ?? ($_GET['success'] ?? '');

$pageTitle = 'Admission Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-bed"></i>
        Admission Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($canAdmit): ?>
            <div class="btn-group me-2">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Admit Patient
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <h2>Admission History & Active Cases</h2>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Admission Date</th>
                    <th>Room Type</th>
                    <th>Daily Cost</th>
                    <th>Discharge Date</th> 
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($result['admissions'])): ?>
                    <?php foreach ($result['admissions'] as $admission): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admission['admission_id']); ?></td>
                            <td><?php echo htmlspecialchars($admission['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($admission['admission_date']))); ?></td>
                            <td><?php echo htmlspecialchars($admission['room_type']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($admission['daily_cost'], 2)); ?></td>
                            <td>
                                <?php if ($admission['discharge_date']): ?>
                                    <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($admission['discharge_date']))); ?>
                                <?php else: ?>
                                    <span class="badge bg-danger">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=view&id=<?php echo $admission['admission_id']; ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                                <?php if ($canManage && is_null($admission['discharge_date'])): ?>
                                    <a href="?action=discharge&id=<?php echo $admission['admission_id']; ?>" class="btn btn-sm btn-warning" title="Discharge"><i class="fas fa-sign-out-alt"></i></a>
                                    <a href="?action=delete&id=<?php echo $admission['admission_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('WARNING: Are you sure you want to permanently DELETE this admission record? This action cannot be undone.')" title="Delete"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No admission records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'add' && $canAdmit): ?>
    <h2>Admit New Patient</h2>
    <div class="card p-3">
        <form method="POST" action="?action=add">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">Patient Name</label>
                        <select class="form-select" id="patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>" 
                                    <?php echo (isset($result['data']['patient_id']) && $result['data']['patient_id'] == $patient['patient_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($patient['name']); ?> (ID: <?php echo htmlspecialchars($patient['patient_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="admission_date" class="form-label">Admission Date/Time</label>
                        <input type="datetime-local" class="form-control" id="admission_date" name="admission_date" value="<?php echo htmlspecialchars($result['data']['admission_date'] ?? date('Y-m-d\TH:i')); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Room Assignment</label>
                        <select class="form-select" id="room_id" name="room_id" required>
                            <option value="">Select an Available Room</option>
                            <?php 
                            // $rooms contains only rooms with available slots (bed_stock > occupied_slots)
                            foreach ($rooms as $room): 
                            ?>
                                <option value="<?php echo htmlspecialchars($room['room_id']); ?>"
                                    <?php echo (isset($result['data']['room_id']) && $result['data']['room_id'] == $room['room_id']) ? 'selected' : ''; ?>>
                                    <?php 
                                    // Display available slots information
                                    $slotInfo = isset($room['available_slots']) && isset($room['bed_stock']) ? " (Slots: {$room['available_slots']}/{$room['bed_stock']})" : '';
                                    echo htmlspecialchars($room['room_type'] . " Room (ID: {$room['room_id']}) - $" . number_format($room['daily_cost'], 2) . "/day" . $slotInfo);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($rooms)): ?>
                            <div class="form-text text-danger">No rooms currently have available bed stock. All rooms may be full or marked for maintenance.</div>
                        <?php else: ?>
                           <div class="form-text">Rooms listed here have at least one free slot based on current admissions.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Record Admission</button>
            <a href="admissions.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

<?php elseif ($action === 'discharge' && $canManage && isset($result['admission'])): ?>
    <h2>Discharge Patient</h2>
    <div class="alert alert-warning">
        You are about to discharge **<?php echo htmlspecialchars($result['admission']['patient_name']); ?>** (ID: <?php echo htmlspecialchars($result['admission']['patient_id']); ?>) 
        from **<?php echo htmlspecialchars($result['admission']['room_type']); ?>** (Room ID: <?php echo htmlspecialchars($result['admission']['room_id']); ?>).
        <br>
        Confirming the discharge will **update the admission record with a discharge date** and the system will automatically **update the room status** based on remaining capacity.
    </div>
    <form method="POST" action="?action=discharge&id=<?php echo htmlspecialchars($id); ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="discharge_date" class="form-label">Discharge Date/Time</label>
                    <input type="datetime-local" class="form-control" id="discharge_date" name="discharge_date" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i')); ?>" required>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-warning"><i class="fas fa-sign-out-alt"></i> Confirm Discharge</button>
        <a href="admissions.php" class="btn btn-secondary">Cancel</a>
    </form>

<?php elseif ($action === 'view' && isset($result['admission'])): ?>
    <h2>Admission Details (ID: <?php echo htmlspecialchars($result['admission']['admission_id']); ?>)</h2>
    <div class="card p-3">
        <dl class="row">
            <dt class="col-sm-3">Patient</dt>
            <dd class="col-sm-9"><a href="patients.php?action=view&id=<?php echo htmlspecialchars($result['admission']['patient_id']); ?>"><?php echo htmlspecialchars($result['admission']['patient_name']); ?></a></dd>

            <dt class="col-sm-3">Admission Date</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($result['admission']['admission_date']))); ?></dd>

            <dt class="col-sm-3">Room Type</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($result['admission']['room_type']); ?> (Room ID: <?php echo htmlspecialchars($result['admission']['room_id']); ?>)</dd>

            <dt class="col-sm-3">Daily Cost</dt>
            <dd class="col-sm-9">$<?php echo htmlspecialchars(number_format($result['admission']['daily_cost'], 2)); ?></dd>
            
            <dt class="col-sm-3">Discharge Date</dt>
            <dd class="col-sm-9">
                <?php if ($result['admission']['discharge_date']): ?>
                    <span class="badge bg-success"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($result['admission']['discharge_date']))); ?></span>
                <?php else: ?>
                    <span class="badge bg-danger">Active Admission</span>
                <?php endif; ?>
            </dd>
        </dl>
    </div>
    <div class="mt-3">
        <a href="admissions.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        <?php if ($canManage && is_null($result['admission']['discharge_date'])): ?>
            <a href="?action=discharge&id=<?php echo $result['admission']['admission_id']; ?>" class="btn btn-warning"><i class="fas fa-sign-out-alt"></i> Discharge Patient</a>
        <?php endif; ?>
    </div>
    
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>
<script src="../../public/js/admissions.js"></script>