<?php
/**
 * Admission Management - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/AdmissionController.php';

// Initialize authentication
$auth = new Auth($pdo);
$auth->requireRole(ROLE_STAFF);

$currentUser = $auth->getCurrentUser();

// Get staff role
try {
    $stmt = $pdo->prepare("
        SELECT s.role as staff_role 
        FROM staff s
        WHERE s.staff_id = (SELECT staff_id FROM users WHERE user_id = ?)
    ");
    $stmt->execute([$currentUser['user_id']]);
    $staffInfo = $stmt->fetch();
    $staffRole = $staffInfo['staff_role'] ?? 'Staff';
} catch (PDOException $e) {
    $staffRole = 'Staff';
}

// Derive reliable nurse check using session or case-insensitive DB value
$isNurse = false;
if (!empty($_SESSION['staff_role'])) {
    $isNurse = ($_SESSION['staff_role'] === STAFF_NURSE);
} else {
    $isNurse = (strcasecmp($staffRole, STAFF_NURSE) === 0);
}

// Initialize controller
$admissionController = new AdmissionController($pdo, $auth);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

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
        // Only nurses can admit patients
        if ($isNurse) {
            $result = $admissionController->create();
            if (isset($result['success'])) {
                header('Location: admissions.php?success=' . urlencode($result['success']));
                exit();
            }
            
            // Load patients, rooms and beds for the form
            try {
                // Get all patients
                $stmt = $pdo->prepare("SELECT patient_id, name FROM patient ORDER BY name");
                $stmt->execute();
                $patients = $stmt->fetchAll();
                
                // Get rooms
                $stmt = $pdo->prepare("SELECT room_id, room_type, daily_cost FROM room ORDER BY room_type, room_id");
                $stmt->execute();
                $rooms = $stmt->fetchAll();

                // Get available beds
                $bedsResult = $admissionController->getAvailableBeds();
                $beds = $bedsResult['beds'] ?? [];
            } catch (PDOException $e) {
                $patients = [];
                $rooms = [];
                $beds = [];
                $result['error'] = 'Error loading form data: ' . $e->getMessage();
            }
        } else {
            $result = ['error' => 'Access denied. Only nurses can admit patients.'];
        }
        break;
        
    case 'edit':
        // Only nurses can edit admissions
        if ($isNurse && $id) {
            $result = $admissionController->update($id);
            if (isset($result['success'])) {
                header('Location: admissions.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only nurses can edit admissions.'];
        }
        break;
        
    case 'discharge':
        // Only nurses can discharge patients
        if ($isNurse && $id) {
            $result = $admissionController->discharge($id);
            if (isset($result['success'])) {
                header('Location: admissions.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only nurses can discharge patients.'];
        }
        break;
        
    case 'delete':
        // Only nurses can delete admissions
        if ($isNurse && $id) {
            $result = $admissionController->delete($id);
            if (isset($result['success'])) {
                header('Location: admissions.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only nurses can delete admissions.'];
        }
        break;
        
    default:
        $result = $admissionController->index();
        break;
}

// Get patients, rooms and beds for forms
$patients = [];
$rooms = [];
$beds = [];
try {
    $stmt = $pdo->prepare("SELECT patient_id, name FROM patient ORDER BY name");
    $stmt->execute();
    $patients = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT room_id, room_type, daily_cost FROM room ORDER BY room_type, room_id");
    $stmt->execute();
    $rooms = $stmt->fetchAll();

    $beds = $admissionController->getAvailableBeds()['beds'];
} catch (PDOException $e) {
    error_log("Error fetching patients/beds: " . $e->getMessage());
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Admission Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-bed"></i>
        Admission Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($isNurse): ?>
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
    <!-- Admission List View -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by patient name..." 
                               value="<?php echo htmlspecialchars($result['search']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Admissions</option>
                        <option value="active" <?php echo $result['status'] === 'active' ? 'selected' : ''; ?>>Current Admissions</option>
                        <option value="discharged" <?php echo $result['status'] === 'discharged' ? 'selected' : ''; ?>>Discharged</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="admissions.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Admissions List
                <span class="badge bg-primary ms-2"><?php echo $result['totalAdmissions']; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['admissions'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No admissions found</h4>
                    <p class="text-muted">
                        <?php if ($result['search'] || $result['status']): ?>
                            No admissions match your search criteria.
                        <?php else: ?>
                            No patients have been admitted yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!$result['search'] && !$result['status'] && $isNurse): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Admit First Patient
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Admission Date</th>
                                <th>Room & Bed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['admissions'] as $admission): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $admission['admission_id']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($admission['patient_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($admission['patient_phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y H:i', strtotime($admission['admission_date'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($admission['room_type'] && $admission['bed_no']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($admission['room_type']); ?></span>
                                            <br><small>Bed <?php echo htmlspecialchars($admission['bed_no']); ?> (<?php echo htmlspecialchars($admission['bed_type']); ?>)</small>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($admission['discharge_date']): ?>
                                            <span class="badge bg-success">Discharged</span>
                                            <br><small class="text-muted"><?php echo date('M j, Y', strtotime($admission['discharge_date'])); ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Current</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $admission['admission_id']; ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($isNurse): ?>
                                                <?php if (!$admission['discharge_date']): ?>
                                                    <a href="?action=discharge&id=<?php echo $admission['admission_id']; ?>" 
                                                       class="btn btn-outline-success" title="Discharge"
                                                       onclick="return confirm('Discharge this patient?')">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </a>
                                                    <a href="?action=edit&id=<?php echo $admission['admission_id']; ?>" 
                                                       class="btn btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?php echo $admission['admission_id']; ?>" 
                                                   class="btn btn-outline-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this admission?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($result['totalPages'] > 1): ?>
                    <nav aria-label="Admissions pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($result['currentPage'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&status=<?php echo urlencode($result['status']); ?>&page=<?php echo $result['currentPage'] - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $result['currentPage'] - 2);
                            $endPage = min($result['totalPages'], $result['currentPage'] + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $result['currentPage'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&status=<?php echo urlencode($result['status']); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($result['currentPage'] < $result['totalPages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&status=<?php echo urlencode($result['status']); ?>&page=<?php echo $result['currentPage'] + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($action === 'add' && $isNurse): ?>
    <!-- Add Admission Form (Nurse Only) -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-plus"></i>
                Admit Patient
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['patient_id']; ?>" 
                                            <?php echo ($result['data']['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($patient['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a patient.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="admission_date" class="form-label">Admission Date *</label>
                            <input type="datetime-local" class="form-control" id="admission_date" name="admission_date" 
                                   value="<?php echo $result['data']['admission_date'] ?? date('Y-m-d\TH:i'); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid admission date.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Room *</label>
                            <select class="form-select" id="room_id" name="room_id" required>
                                <option value="">Select Room</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room['room_id']; ?>" 
                                            <?php echo ($result['data']['room_id'] ?? '') == $room['room_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($room['room_type'] . ' - $' . number_format($room['daily_cost'], 2) . '/day'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a room.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="bed_id" class="form-label">Bed Assignment (optional)</label>
                            <select class="form-select" id="bed_id" name="bed_id">
                                <option value="">No specific bed</option>
                                <?php foreach ($beds as $bed): ?>
                                    <option value="<?php echo $bed['bed_id']; ?>" 
                                            <?php echo ($result['data']['bed_id'] ?? '') == $bed['bed_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bed['room_type'] . ' - Bed ' . $bed['bed_no'] . ' (' . $bed['bed_type'] . ') - $' . number_format($bed['daily_cost'], 2) . '/day'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($beds)): ?>
                                <div class="form-text text-muted">
                                    No available beds. You can still admit by room only.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($result['errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($result['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between">
                    <a href="admissions.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Admit Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'discharge' && $isNurse && isset($result['admission'])): ?>
    <!-- Discharge Patient Form (Nurse Only) -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-sign-out-alt"></i>
                Discharge Patient
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="alert alert-info">
                    <h6>Patient Information</h6>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($result['admission']['patient_name']); ?></p>
                    <p class="mb-1"><strong>Admission Date:</strong> <?php echo date('M j, Y H:i', strtotime($result['admission']['admission_date'])); ?></p>
                    <p class="mb-0"><strong>Room:</strong> <?php echo htmlspecialchars($result['admission']['room_type'] . ' - Bed ' . $result['admission']['bed_no']); ?></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="discharge_date" class="form-label">Discharge Date *</label>
                            <input type="datetime-local" class="form-control" id="discharge_date" name="discharge_date" 
                                   value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid discharge date.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Note:</strong> Discharging the patient will make the bed available for other patients.
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="admissions.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sign-out-alt"></i>
                        Discharge Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'view' && isset($result['admission'])): ?>
    <!-- Admission View -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Admission Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Patient Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($result['admission']['patient_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($result['admission']['patient_phone']); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($result['admission']['gender']); ?></p>
                            <p><strong>Age:</strong> <?php echo date_diff(date_create($result['admission']['DOB']), date_create('today'))->y; ?> years</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Admission Information</h6>
                            <p><strong>Admission Date:</strong> <?php echo date('M j, Y H:i', strtotime($result['admission']['admission_date'])); ?></p>
                            <?php if ($result['admission']['discharge_date']): ?>
                                <p><strong>Discharge Date:</strong> <?php echo date('M j, Y H:i', strtotime($result['admission']['discharge_date'])); ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-success">Discharged</span></p>
                            <?php else: ?>
                                <p><strong>Status:</strong> <span class="badge bg-primary">Current</span></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Room & Bed Information</h6>
                            <?php if ($result['admission']['room_type'] && $result['admission']['bed_no']): ?>
                                <p><strong>Room Type:</strong> <?php echo htmlspecialchars($result['admission']['room_type']); ?></p>
                                <p><strong>Bed Number:</strong> <?php echo htmlspecialchars($result['admission']['bed_no']); ?></p>
                                <p><strong>Bed Type:</strong> <?php echo htmlspecialchars($result['admission']['bed_type']); ?></p>
                                <p><strong>Daily Cost:</strong> $<?php echo number_format($result['admission']['daily_cost'], 2); ?></p>
                            <?php else: ?>
                                <p class="text-muted">No bed assigned</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6>Cost Information</h6>
                            <p><strong>Total Cost:</strong> $<?php echo number_format($result['cost'], 2); ?></p>
                            <p class="text-muted">
                                <?php
                                $admissionDate = new DateTime($result['admission']['admission_date']);
                                $dischargeDate = $result['admission']['discharge_date'] ? new DateTime($result['admission']['discharge_date']) : new DateTime();
                                $days = $admissionDate->diff($dischargeDate)->days + 1;
                                ?>
                                <small>Calculated for <?php echo $days; ?> day(s)</small>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($isNurse && !$result['admission']['discharge_date']): ?>
                        <div class="mt-3">
                            <a href="?action=discharge&id=<?php echo $result['admission']['admission_id']; ?>" class="btn btn-success">
                                <i class="fas fa-sign-out-alt"></i> Discharge Patient
                            </a>
                            <a href="?action=edit&id=<?php echo $result['admission']['admission_id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Admission
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="patients.php?action=view&id=<?php echo $result['admission']['patient_id']; ?>" 
                           class="btn btn-info">
                            <i class="fas fa-user"></i> View Patient Profile
                        </a>
                        <a href="billing.php?action=add&patient_id=<?php echo $result['admission']['patient_id']; ?>" 
                           class="btn btn-warning">
                            <i class="fas fa-credit-card"></i> Create Bill
                        </a>
                        <?php if ($isNurse && !$result['admission']['discharge_date']): ?>
                            <a href="?action=discharge&id=<?php echo $result['admission']['admission_id']; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-sign-out-alt"></i> Discharge Patient
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="admissions.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>
<script src="../../public/js/admissions.js"></script>
