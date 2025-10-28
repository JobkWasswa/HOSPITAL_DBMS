<?php
/**
 * Patient Management - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/PatientController.php';

// Define the staff role constants if they are not in config/config.php (assuming they were in the original setup)
if (!defined('ROLE_STAFF')) define('ROLE_STAFF', 'staff'); 
if (!defined('STAFF_RECEPTIONIST')) define('STAFF_RECEPTIONIST', 'receptionist');

// Initialize authentication
$auth = new Auth($pdo);
$auth->requireRole(ROLE_STAFF);

$currentUser = $auth->getCurrentUser();

// Get staff role
try {
    // Assuming staff roles are mapped in a 'staff' table which the 'users' table links to.
    $stmt = $pdo->prepare("
        SELECT u.role as staff_role 
        FROM users u
        WHERE u.user_id = ?
    ");
    $stmt->execute([$currentUser['user_id']]);
    $staffInfo = $stmt->fetch();
    $staffRole = $staffInfo['staff_role'] ?? 'Staff';
} catch (PDOException $e) {
    // Fallback if the above query fails (might be simpler if 'role' is directly on 'users')
    $staffRole = $currentUser['role'] ?? 'Staff';
}

// Initialize controller
$patientController = new PatientController($pdo, $auth);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions based on staff role
switch ($action) {
    case 'view':
        if ($id) {
            // NOTE: PatientController::view() is assumed to return 'patient', 'treatments', and 'appointments'
            $result = $patientController->view($id); 
        } else {
            $result = ['error' => 'Patient ID required'];
        }
        break;
        
    case 'add':
        // Only receptionists can add patients
        if ($staffRole === STAFF_RECEPTIONIST) {
            $result = $patientController->create();
            if (isset($result['success'])) {
                header('Location: patients.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only receptionists can add patients.'];
        }
        break;
        
    case 'edit':
        // Only receptionists can edit patients
        if ($staffRole === STAFF_RECEPTIONIST && $id) {
            $result = $patientController->update($id);
            if (isset($result['success'])) {
                header('Location: patients.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only receptionists can edit patients.'];
        }
        break;
        
    case 'delete':
        // Only receptionists can delete patients
        if ($staffRole === STAFF_RECEPTIONIST && $id) {
            $result = $patientController->delete($id);
            if (isset($result['success'])) {
                header('Location: patients.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only receptionists can delete patients.'];
        }
        break;
        
    default:
        $result = $patientController->index();
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Patient Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users"></i>
        Patient Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($staffRole === STAFF_RECEPTIONIST): ?>
            <div class="btn-group me-2">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Register New Patient
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
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by name or phone..." 
                               value="<?php echo htmlspecialchars($result['search'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="patients.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Patients List
                <span class="badge bg-primary ms-2"><?php echo $result['totalPatients'] ?? 0; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['patients'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No patients found</h4>
                    <p class="text-muted">
                        <?php if ($result['search'] ?? false): ?>
                            No patients match your search criteria.
                        <?php else: ?>
                            No patients have been registered yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!($result['search'] ?? false) && $staffRole === STAFF_RECEPTIONIST): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Register First Patient
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['patients'] as $patient): ?>
                                <?php
                                // Ensure DOB exists before calculating age
                                $age = isset($patient['DOB']) ? date_diff(date_create($patient['DOB']), date_create('today'))->y : 'N/A';
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $patient['patient_id']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($patient['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $patient['gender'] === 'Male' ? 'primary' : ($patient['gender'] === 'Female' ? 'danger' : 'secondary'); ?>">
                                            <?php echo htmlspecialchars($patient['gender']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $age; ?> years</td>
                                    <td>
                                        <a href="tel:<?php echo htmlspecialchars($patient['phone']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($patient['phone']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $patient['patient_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($staffRole === STAFF_RECEPTIONIST): ?>
                                                <a href="?action=edit&id=<?php echo $patient['patient_id']; ?>" 
                                                   class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $patient['patient_id']; ?>" 
                                                   class="btn btn-outline-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this patient?')">
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
                
                <?php if ($result['totalPages'] ?? 0 > 1): ?>
                    <nav aria-label="Patients pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($result['currentPage'] ?? 1 > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search'] ?? ''); ?>&page=<?php echo $result['currentPage'] - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $currentPage = $result['currentPage'] ?? 1;
                            $totalPages = $result['totalPages'] ?? 1;
                            $search = $result['search'] ?? '';

                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $currentPage + 1; ?>">
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

<?php elseif ($action === 'add' && $staffRole === STAFF_RECEPTIONIST): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-plus"></i>
                Register New Patient
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($result['data']['name'] ?? ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid name.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($result['data']['phone'] ?? ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid phone number.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="DOB" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="DOB" name="DOB" 
                                   value="<?php echo $result['data']['DOB'] ?? ''; ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid date of birth.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender *</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($result['data']['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($result['data']['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($result['data']['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a gender.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($result['data']['address'] ?? ''); ?></textarea>
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
                    <a href="patients.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Register Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'edit' && $staffRole === STAFF_RECEPTIONIST && isset($result['patient'])): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-edit"></i>
                Edit Patient
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($result['data']['name'] ?? $result['patient']['name']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid name.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($result['data']['phone'] ?? $result['patient']['phone']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid phone number.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="DOB" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="DOB" name="DOB" 
                                   value="<?php echo $result['data']['DOB'] ?? $result['patient']['DOB']; ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid date of birth.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender *</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($result['data']['gender'] ?? $result['patient']['gender']) === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($result['data']['gender'] ?? $result['patient']['gender']) === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($result['data']['gender'] ?? $result['patient']['gender']) === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a gender.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($result['data']['address'] ?? $result['patient']['address']); ?></textarea>
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
                    <a href="patients.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'view' && isset($result['patient'])): ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Patient Profile</h5>
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($result['patient']['name']); ?></h4>
                    <p class="text-muted">Patient ID: <?php echo $result['patient']['patient_id']; ?></p>
                    
                    <hr>
                    
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($result['patient']['gender']); ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo date('M j, Y', strtotime($result['patient']['DOB'])); ?></p>
                    <p><strong>Age:</strong> <?php echo date_diff(date_create($result['patient']['DOB']), date_create('today'))->y; ?> years</p>
                    <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($result['patient']['phone']); ?>"><?php echo htmlspecialchars($result['patient']['phone']); ?></a></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($result['patient']['address']); ?></p>
                    
                    <?php if ($staffRole === STAFF_RECEPTIONIST): ?>
                        <div class="mt-3">
                            <a href="?action=edit&id=<?php echo $result['patient']['patient_id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Patient
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Appointments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($result['appointments'])): ?>
                        <p class="text-muted">No appointments recorded.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Doctor</th>
                                        <th>Fee</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['appointments'] as $appointment): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['user_id']); // Assuming Controller fetches doctor name based on user_id ?></td>
                                            <td>$<?php echo number_format($appointment['consultation_fee'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $appointment['appointment_status'] === 'Completed' ? 'success' : 
                                                         ($appointment['appointment_status'] === 'Scheduled' ? 'info' : 'danger');
                                                ?>">
                                                    <?php echo htmlspecialchars($appointment['appointment_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Treatments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($result['treatments'])): // CORRECTED KEY: was 'treatment', now 'treatments' ?>
                        <p class="text-muted">No treatments recorded.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor (User ID)</th>
                                        <th>Notes</th>
                                        <th>Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['treatments'] as $treatment): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($treatment['treatment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($treatment['user_id']); // Assuming Controller fetches doctor name based on user_id ?></td>
                                            <td><?php echo htmlspecialchars(substr($treatment['notes'], 0, 50)) . (strlen($treatment['notes']) > 50 ? '...' : ''); ?></td>
                                            <td>$<?php echo number_format($treatment['treatment_fee'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="patients.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>