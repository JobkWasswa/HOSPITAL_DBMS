<?php
/**
 * Doctor Dashboard
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';

// Initialize authentication
$auth = new Auth($pdo);

// Require doctor role
$auth->requireRole(ROLE_DOCTOR);

$currentUser = $auth->getCurrentUser();

// Get dashboard statistics
try {
    // Today's appointments
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM appointment 
        WHERE doctor_id = (SELECT doctor_id FROM users WHERE user_id = ?) 
        AND DATE(appointment_date) = CURDATE()
    ");
    $stmt->execute([$currentUser['user_id']]);
    $todayAppointments = $stmt->fetch()['count'];

    // Total patients treated
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT patient_id) as count 
        FROM treatment 
        WHERE doctor_id = (SELECT doctor_id FROM users WHERE user_id = ?)
    ");
    $stmt->execute([$currentUser['user_id']]);
    $totalPatients = $stmt->fetch()['count'];

    // Pending lab results
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM lab_test 
        WHERE patient_id IN (
            SELECT DISTINCT patient_id 
            FROM treatment 
            WHERE doctor_id = (SELECT doctor_id FROM users WHERE user_id = ?)
        ) 
        AND results IS NULL
    ");
    $stmt->execute([$currentUser['user_id']]);
    $pendingLabResults = $stmt->fetch()['count'];

    // Recent treatments
    $stmt = $pdo->prepare("
        SELECT t.*, p.name as patient_name 
        FROM treatment t
        JOIN patient p ON t.patient_id = p.patient_id
        WHERE t.doctor_id = (SELECT doctor_id FROM users WHERE user_id = ?)
        ORDER BY t.treatment_date DESC
        LIMIT 5
    ");
    $stmt->execute([$currentUser['user_id']]);
    $recentTreatments = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $todayAppointments = 0;
    $totalPatients = 0;
    $pendingLabResults = 0;
    $recentTreatments = [];
}

$pageTitle = 'Doctor Dashboard';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt"></i>
        Doctor Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Welcome Message -->
<div class="alert alert-info" role="alert">
    <h4 class="alert-heading">
        <i class="fas fa-user-md"></i>
        Welcome, Dr. <?php echo htmlspecialchars($currentUser['doctor_name']); ?>!
    </h4>
    <p class="mb-0">
        Specialization: <?php echo htmlspecialchars($currentUser['specialization']); ?> | 
        Last login: <?php echo date('M j, Y H:i', $_SESSION['login_time']); ?>
    </p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Today's Appointments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $todayAppointments; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Patients
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $totalPatients; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Lab Results
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $pendingLabResults; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-flask fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            This Month
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo date('M Y'); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="patients.php" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus"></i><br>
                            All Patients
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="appointments.php?action=add" class="btn btn-success w-100">
                            <i class="fas fa-calendar-plus"></i><br>
                            Schedule Appointment
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="treatments.php?action=add" class="btn btn-warning w-100">
                            <i class="fas fa-stethoscope"></i><br>
                            New Treatment
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="reports.php" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar"></i><br>
                            View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Treatments -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i> Recent Treatments
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentTreatments)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-stethoscope fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No recent treatments</h5>
                        <p class="text-muted">Start by creating a new treatment for a patient.</p>
                        <a href="treatments.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Treatment
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient</th>
                                    <th>Treatment Date</th>
                                    <th>Notes</th>
                                    <th>Fee</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTreatments as $treatment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($treatment['patient_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y H:i', strtotime($treatment['treatment_date'])); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($treatment['notes'], 0, 50)) . (strlen($treatment['notes']) > 50 ? '...' : ''); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">$<?php echo number_format($treatment['treatment_fee'], 2); ?></span>
                                        </td>
                                        <td>
                                            <a href="treatments.php?action=view&id=<?php echo $treatment['treatment_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="treatments.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View All Treatments
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.text-xs {
    font-size: 0.7rem;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>

<?php include '../layouts/footer.php'; ?>
