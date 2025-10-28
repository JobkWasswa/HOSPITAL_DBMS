<?php
/**
 * Patient Management - Doctor View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/PatientController.php';

// Initialize authentication
$auth = new Auth($pdo);
$auth->requireRole(ROLE_DOCTOR);

$currentUser = $auth->getCurrentUser();

// Get doctor information
try {
    // CRITICAL FIX: The original query used `doctor_id = (SELECT doctor_id FROM users WHERE user_id = ?)`.
    // The correct approach is to join the `doctor` table using the `user_id` from the session.
    $stmt = $pdo->prepare("
        SELECT d.name as doctor_name, d.specialization, d.doctor_id
        FROM doctor d
        WHERE d.user_id = ?
    ");
    $stmt->execute([$currentUser['user_id']]);
    $doctorInfo = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array for consistency
    $doctorName = $doctorInfo['doctor_name'] ?? 'Doctor';
    $specialization = $doctorInfo['specialization'] ?? 'General';
    $doctorId = $doctorInfo['doctor_id'] ?? null;
} catch (PDOException $e) {
    // Log error but provide defaults to prevent page crash
    error_log("Error fetching doctor info in patients.php: " . $e->getMessage());
    $doctorName = 'Doctor';
    $specialization = 'General';
    $doctorId = null;
}

// Initialize controller
$patientController = new PatientController($pdo, $auth);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            // NOTE: The current logic fetches ALL patient data via $patientController->view($id).
            // No scope enforcement is applied here (i.e., doctor can see all patients).
            $result = $patientController->view($id);
        } else {
            $result = ['error' => 'Patient ID required'];
        }
        break;
        
    default:
        // NOTE: The current logic fetches ALL patients via $patientController->index().
        // If the goal was only to see *their* patients, the controller call would need a filter: $patientController->index($doctorId);
        $result = $patientController->index();
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Patient Management - Doctor View';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users"></i>
        Patient Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <span class="badge bg-info">
                <i class="fas fa-user-md"></i>
                <?php echo htmlspecialchars($doctorName); ?> - <?php echo htmlspecialchars($specialization); ?>
            </span>
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
                All Patients
                <span class="badge bg-primary ms-2"><?php echo $result['totalPatients'] ?? 0; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['patients'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No patients found</h4>
                    <p class="text-muted">
                        <?php if (isset($result['search']) && $result['search']): ?>
                            No patients match your search criteria.
                        <?php else: ?>
                            No patients have been registered yet.
                        <?php endif; ?>
                    </p>
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
                                            <a href="appointments.php?action=add&patient_id=<?php echo $patient['patient_id']; ?>" 
                                               class="btn btn-outline-success" title="Schedule Appointment">
                                                <i class="fas fa-calendar-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (($result['totalPages'] ?? 0) > 1): ?>
                    <nav aria-label="Patients pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($result['currentPage'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search'] ?? ''); ?>&page=<?php echo $result['currentPage'] - 1; ?>">
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
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search'] ?? ''); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($result['currentPage'] < $result['totalPages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search'] ?? ''); ?>&page=<?php echo $result['currentPage'] + 1; ?>">
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

<?php elseif ($action === 'view' && isset($result['patient'])): ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Patient Information</h5>
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
                    
                    <div class="mt-3">
                        <a href="appointments.php?action=add&patient_id=<?php echo $result['patient']['patient_id']; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-calendar-plus"></i> Schedule Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Medical History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($result['medicalHistory'])): ?>
                        <p class="text-muted">No medical history recorded.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Medical Condition</th>
                                        <th>Diagnosis Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['medicalHistory'] as $history): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($history['medical_condition']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($history['diagnosis_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($history['notes']); ?></td>
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
                    <?php if (empty($result['treatments'])): ?>
                        <p class="text-muted">No treatments recorded.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Notes</th>
                                        <th>Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['treatments'] as $treatment): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($treatment['treatment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($treatment['doctor_name']); ?></td>
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