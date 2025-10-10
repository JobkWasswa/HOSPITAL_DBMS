<?php
/**
 * Appointment Management - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/AppointmentController.php';

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

// Initialize controller
$appointmentController = new AppointmentController($pdo, $auth);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            $result = $appointmentController->view($id);
        } else {
            $result = ['error' => 'Appointment ID required'];
        }
        break;
        
    case 'add':
        // Only receptionists can add appointments
        if ($staffRole === STAFF_RECEPTIONIST) {
            $result = $appointmentController->create();
            if (isset($result['success'])) {
                header('Location: appointments.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only receptionists can schedule appointments.'];
        }
        break;
        
    case 'edit':
        // Only receptionists can edit appointments
        if ($staffRole === STAFF_RECEPTIONIST && $id) {
            $result = $appointmentController->update($id);
            if (isset($result['success'])) {
                header('Location: appointments.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only receptionists can edit appointments.'];
        }
        break;
        
    case 'delete':
        // Only receptionists can delete appointments
        if ($staffRole === STAFF_RECEPTIONIST && $id) {
            $result = $appointmentController->delete($id);
            if (isset($result['success'])) {
                header('Location: appointments.php?success=' . urlencode($result['success']));
                exit();
            }
        } else {
            $result = ['error' => 'Access denied. Only receptionists can delete appointments.'];
        }
        break;
        
    default:
        $result = $appointmentController->index();
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Appointment Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt"></i>
        Appointment Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($staffRole === STAFF_RECEPTIONIST): ?>
            <div class="btn-group me-2">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-calendar-plus"></i>
                    Schedule Appointment
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
    <!-- Appointment List View -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
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
                        <option value="">All Status</option>
                        <option value="Scheduled" <?php echo $result['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="Completed" <?php echo $result['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo $result['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="No show" <?php echo $result['status'] === 'No show' ? 'selected' : ''; ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
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
                    <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No appointments found</h4>
                    <p class="text-muted">
                        <?php if ($result['search'] || $result['status']): ?>
                            No appointments match your search criteria.
                        <?php else: ?>
                            No appointments have been scheduled yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!$result['search'] && !$result['status'] && $staffRole === STAFF_RECEPTIONIST): ?>
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
                                <th>Doctor</th>
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
                                        <?php echo htmlspecialchars($appointment['doctor_name']); ?>
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
                                        $<?php echo number_format($appointment['consultation_fee'], 2); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $appointment['appointment_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($staffRole === STAFF_RECEPTIONIST): ?>
                                                <a href="?action=edit&id=<?php echo $appointment['appointment_id']; ?>" 
                                                   class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $appointment['appointment_id']; ?>" 
                                                   class="btn btn-outline-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this appointment?')">
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

<?php elseif ($action === 'view' && isset($result['appointment'])): ?>
    <!-- Appointment View -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Appointment Details</h5>
                </div>
                <div class="card-body">
                    <h4>Appointment #<?php echo $result['appointment']['appointment_id']; ?></h4>
                    
                    <hr>
                    
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($result['appointment']['patient_name']); ?></p>
                    <p><strong>Doctor:</strong> <?php echo htmlspecialchars($result['appointment']['doctor_name']); ?></p>
                    <p><strong>Date & Time:</strong> <?php echo date('M j, Y H:i', strtotime($result['appointment']['appointment_date'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-primary"><?php echo htmlspecialchars($result['appointment']['appointment_status']); ?></span>
                    </p>
                    <p><strong>Consultation Fee:</strong> $<?php echo number_format($result['appointment']['consultation_fee'], 2); ?></p>
                    
                    <?php if ($staffRole === STAFF_RECEPTIONIST): ?>
                        <div class="mt-3">
                            <a href="?action=edit&id=<?php echo $result['appointment']['appointment_id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Appointment
                            </a>
                        </div>
                    <?php endif; ?>
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
