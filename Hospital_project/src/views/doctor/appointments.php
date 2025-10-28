<?php
/**
 * Appointment Management - Doctor View
 * Hospital Management System
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
// NOTE: Assuming database connection ($pdo) is established in config/config.php or is globally available.
// If $pdo is not globally available, you must include the file where it is defined, e.g., database.php.

// Initialize authentication
// Ensure ROLE_DOCTOR is defined in config/config.php (e.g., const ROLE_DOCTOR = 'doctor';)
$auth = new Auth($pdo);
$auth->requireRole(ROLE_DOCTOR);

$currentUser = $auth->getCurrentUser();

// ==============================================================================
// 1. FIX: GET DOCTOR ID (Use the current user's ID from the 'users' table)
// ==============================================================================
// Based on the schema, the doctor's identifier in the appointment table is the user_id.
// We directly use the logged-in user's ID as the doctor ID.
$doctorId = $currentUser['user_id'] ?? null;

// Ensure the user is logged in (this is a redundant check if requireRole works, but good for clarity)
if (!$doctorId) {
    // Handle error: User is logged in as a doctor but no corresponding user_id found.
    error_log("Critical Error: Doctor user_id not found in session for user: " . ($currentUser['username'] ?? 'Unknown'));
    http_response_code(403);
    include '../layouts/header.php';
    echo '<div class="alert alert-danger">Critical Error: User profile is incomplete or invalid. Cannot proceed.</div>';
    include '../layouts/footer.php';
    exit();
}
// ==============================================================================

// NOTE ON MVC: The following logic (lines 37-124) is Controller logic and should ideally be moved
// into src/controllers/AppointmentController.php for better Separation of Concerns.

// Initialize controller
require_once '../../../src/controllers/AppointmentController.php'; // Ensure it's required before initialization
$appointmentController = new AppointmentController($pdo, $auth);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            // Must enforce scope: Doctor can only view their own appointments
            $result = $appointmentController->view($id, $doctorId); 
        } else {
            $result = ['error' => 'Appointment ID required'];
        }
        break;
        
    case 'add':
        // Pass doctorId for automatic assignment
        $result = $appointmentController->create($doctorId);
        if (isset($result['success'])) {
            header('Location: appointments.php?success=' . urlencode($result['success']));
            exit();
        }
        break;
        
    case 'edit':
        if ($id) {
            // Pass doctorId for scope enforcement during update
            $result = $appointmentController->update($id, $doctorId);
            if (isset($result['success'])) {
                header('Location: appointments.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Appointment ID required'];
        }
        break;
        
    case 'delete':
        if ($id) {
            // Pass doctorId for scope enforcement during deletion
            $result = $appointmentController->delete($id, $doctorId);
            if (isset($result['success'])) {
                header('Location: appointments.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Appointment ID required'];
        }
        break;
        
    case 'update_status':
        if ($id && isset($_GET['status'])) {
            // Pass doctorId for scope enforcement
            $result = $appointmentController->updateStatus($id, $_GET['status'], $doctorId);
            if (isset($result['success'])) {
                header('Location: appointments.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Invalid request'];
        }
        break;
        
    default:
        // FILTER INDEX BY LOGGED-IN DOCTOR ID
        // Assuming AppointmentController::index accepts doctorId as a filter
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));

        $result = $appointmentController->index($search, $status, $page, $doctorId); 
        break;
}

// 2. Fetch patients list for dropdown (Data fetching is acceptable here, but Model is better)
$patients = [];
try {
    $stmt = $pdo->prepare("SELECT patient_id, name FROM patient ORDER BY name");
    $stmt->execute();
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching patients: " . $e->getMessage());
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'My Appointments';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt"></i>
        My Appointments
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i>
                Schedule Appointment
            </a>
        </div>
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
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by patient..." 
                               value="<?php echo htmlspecialchars($result['search']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="Scheduled" <?php echo $result['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="Completed" <?php echo $result['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo $result['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="No show" <?php echo $result['status'] === 'No show' ? 'selected' : ''; ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="appointments.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Appointments List
                <span class="badge bg-primary ms-2"><?php echo $result['totalAppointments']; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['appointments'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No appointments found</h4>
                    <p class="text-muted">
                        <?php if ($result['search'] || $result['status']): ?>
                            No appointments match your search criteria.
                        <?php else: ?>
                            No appointments have been scheduled with you yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!$result['search'] && !$result['status']): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-calendar-plus"></i>
                            Schedule First Appointment
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
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Fee</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['appointments'] as $appointment): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $appointment['appointment_id']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y H:i', strtotime($appointment['appointment_date'])); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($appointment['appointment_status']) {
                                            'Scheduled' => 'bg-primary',
                                            'Completed' => 'bg-success',
                                            'Cancelled' => 'bg-danger',
                                            'No show' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($appointment['appointment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">$<?php echo number_format($appointment['consultation_fee'], 2); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $appointment['appointment_id']; ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?php echo $appointment['appointment_id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($appointment['appointment_status'] === 'Scheduled'): ?>
                                                <a href="?action=update_status&id=<?php echo $appointment['appointment_id']; ?>&status=Completed" 
                                                   class="btn btn-outline-success" title="Mark as Completed"
                                                   onclick="return confirm('Mark this appointment as completed?')">
                                                     <i class="fas fa-check"></i>
                                                </a>
                                                <a href="?action=update_status&id=<?php echo $appointment['appointment_id']; ?>&status=Cancelled" 
                                                   class="btn btn-outline-danger" title="Cancel"
                                                   onclick="return confirm('Cancel this appointment?')">
                                                     <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $appointment['appointment_id']; ?>" 
                                               class="btn btn-outline-danger btn-delete" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this appointment?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($result['totalPages'] > 1): ?>
                    <nav aria-label="Appointments pagination">
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

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-<?php echo $action === 'add' ? 'calendar-plus' : 'calendar-edit'; ?>"></i>
                <?php echo $action === 'add' ? 'Schedule New Appointment' : 'Edit Appointment'; ?>
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
                                            <?php echo ($result['data']['patient_id'] ?? $result['appointment']['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : ''; ?>>
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
                            <label for="doctor_name" class="form-label">Doctor (Yourself)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($currentUser['username'] ?? 'Loading...'); ?>" disabled>
                            <input type="hidden" name="user_id" value="<?php echo $doctorId; ?>"> </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Date & Time *</label>
                            <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" 
                                    value="<?php echo $result['data']['appointment_date'] ?? str_replace(' ', 'T', $result['appointment']['appointment_date'] ?? ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid date and time.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="consultation_fee" class="form-label">Consultation Fee *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" 
                                       step="0.01" min="0" 
                                       value="<?php echo $result['data']['consultation_fee'] ?? $result['appointment']['consultation_fee'] ?? ''; ?>" required>
                            </div>
                            <div class="invalid-feedback">
                                Please provide a valid consultation fee.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="appointment_status" class="form-label">Status *</label>
                            <select class="form-select" id="appointment_status" name="appointment_status" required>
                                <option value="Scheduled" <?php echo ($result['data']['appointment_status'] ?? $result['appointment']['appointment_status'] ?? 'Scheduled') === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="Completed" <?php echo ($result['data']['appointment_status'] ?? $result['appointment']['appointment_status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo ($result['data']['appointment_status'] ?? $result['appointment']['appointment_status'] ?? '') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="No show" <?php echo ($result['data']['appointment_status'] ?? $result['appointment']['appointment_status'] ?? '') === 'No show' ? 'selected' : ''; ?>>No Show</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a status.
                            </div>
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
                    <a href="appointments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $action === 'add' ? 'Schedule Appointment' : 'Update Appointment'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'view' && isset($result['appointment'])): ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Appointment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Patient Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($result['appointment']['patient_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($result['appointment']['patient_phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Doctor Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($result['appointment']['doctor_name']); ?></p>
                            <p><strong>Specialization:</strong> <?php echo htmlspecialchars($result['appointment']['specialization']); ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Appointment Details</h6>
                            <p><strong>Date & Time:</strong> <?php echo date('M j, Y H:i', strtotime($result['appointment']['appointment_date'])); ?></p>
                            <p><strong>Status:</strong> 
                                <?php
                                $statusClass = match($result['appointment']['appointment_status']) {
                                    'Scheduled' => 'bg-primary',
                                    'Completed' => 'bg-success',
                                    'Cancelled' => 'bg-danger',
                                    'No show' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($result['appointment']['appointment_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Financial Information</h6>
                            <p><strong>Consultation Fee:</strong> $<?php echo number_format($result['appointment']['consultation_fee'], 2); ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="?action=edit&id=<?php echo $result['appointment']['appointment_id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Appointment
                        </a>
                        <?php if ($result['appointment']['appointment_status'] === 'Scheduled'): ?>
                            <a href="?action=update_status&id=<?php echo $result['appointment']['appointment_id']; ?>&status=Completed" 
                               class="btn btn-success" onclick="return confirm('Mark this appointment as completed?')">
                                <i class="fas fa-check"></i> Mark Completed
                            </a>
                            <a href="?action=update_status&id=<?php echo $result['appointment']['appointment_id']; ?>&status=Cancelled" 
                               class="btn btn-danger" onclick="return confirm('Cancel this appointment?')">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
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
                        <a href="treatments.php?action=add&patient_id=<?php echo $result['appointment']['patient_id']; ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-stethoscope"></i> Create Treatment
                        </a>
                        <a href="patients.php?action=view&id=<?php echo $result['appointment']['patient_id']; ?>" 
                           class="btn btn-info">
                            <i class="fas fa-user"></i> View Patient Profile
                        </a>
                        <a href="appointments.php?action=add&patient_id=<?php echo $result['appointment']['patient_id']; ?>" 
                           class="btn btn-success">
                            <i class="fas fa-calendar-plus"></i> Schedule Follow-up
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="appointments.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>