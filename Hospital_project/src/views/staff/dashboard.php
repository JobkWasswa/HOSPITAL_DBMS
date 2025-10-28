<?php
/**
 * Staff Dashboard
 * Hospital Management System
 * FIX: Updated for consolidated 'users' table and multiple staff roles.
 */

require_once '../../../config/config.php';
// Make sure you include the constants file so ROLE_NURSE, etc., are available
require_once '../../../config/constants.php'; 
require_once '../../../src/helpers/Auth.php';

// Initialize authentication
$auth = new Auth($pdo);

// 1. FIX: Use requireRoles() to allow access for all non-Doctor staff roles.
$allowedStaffRoles = [ROLE_NURSE, ROLE_RECEPTIONIST, ROLE_ACCOUNTANT];
$auth->requireRoles($allowedStaffRoles);

$currentUser = $auth->getCurrentUser();

// The user's role is now directly in $currentUser['role']
$staffRole = $currentUser['role'];
$staffName = $currentUser['name'];

// 2. FIX: Simplified SQL query to get the department name directly from the users table.
try {
    // Select the department name by joining users with department
    $stmt = $pdo->prepare("
        SELECT d.name as department_name
        FROM users u
        LEFT JOIN department d ON u.department_id = d.department_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$currentUser['user_id']]);
    $departmentInfo = $stmt->fetch();
    
    $departmentName = $departmentInfo['department_name'] ?? 'General';
    
} catch (PDOException $e) {
    error_log("Staff dashboard department error: " . $e->getMessage());
    $departmentName = 'General';
}

// 3. FIX: Use the primary ROLE constants (ROLE_NURSE, ROLE_RECEPTIONIST, ROLE_ACCOUNTANT) 
// for conditional checks, since 'STAFF_NURSE' etc., are deprecated.

// Get dashboard statistics based on staff role
try {
    $stats = [];
    
    if ($staffRole === ROLE_RECEPTIONIST) {
        // Receptionist stats (Use ROLE_RECEPTIONIST)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM patient WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['new_patients'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointment WHERE DATE(appointment_date) = CURDATE()");
        $stmt->execute();
        $stats['today_appointments'] = $stmt->fetch()['count'];
        
    } elseif ($staffRole === ROLE_NURSE) {
        // Nurse stats (Use ROLE_NURSE)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admission WHERE discharge_date IS NULL");
        $stmt->execute();
        $stats['current_admissions'] = $stmt->fetch()['count'];
        
        // Note: You have a 'room' table with 'bed_stock', not a 'bed' table. Using 'room' table logic.
        $stmt = $pdo->prepare("SELECT SUM(bed_stock) as total_beds, SUM(CASE WHEN room_status = 'Available' THEN bed_stock ELSE 0 END) as available_beds_count FROM room");
        $stmt->execute();
        $bedStats = $stmt->fetch();
        $stats['available_beds'] = $bedStats['available_beds_count'] ?? 0;
        
    } elseif ($staffRole === ROLE_ACCOUNTANT) {
        // Accountant stats (Use ROLE_ACCOUNTANT)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payment WHERE payment_status = '" . PAYMENT_PENDING . "'");
        $stmt->execute();
        $stats['pending_payments'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM payment WHERE payment_status = '" . PAYMENT_PAID . "' AND DATE(payment_date) = CURDATE()");
        $stmt->execute();
        $stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;
        
    } else {
        // General staff stats (Shouldn't be hit, but good fallback)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM patient");
        $stmt->execute();
        $stats['total_patients'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointment WHERE DATE(appointment_date) = CURDATE()");
        $stmt->execute();
        $stats['today_appointments'] = $stmt->fetch()['count'];
    }
    
} catch (PDOException $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $stats = [];
}

$pageTitle = 'Staff Dashboard';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt"></i>
        Staff Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
</div>

<div class="alert alert-info" role="alert">
    <h4 class="alert-heading">
        <i class="fas fa-user-tie"></i>
        Welcome, <?php echo htmlspecialchars($staffName); ?>!
    </h4>
    <p class="mb-0">
        Role: <?php echo htmlspecialchars(ucwords($staffRole)); ?> | 
        Department: <?php echo htmlspecialchars($departmentName); ?> | 
        Last login: <?php echo date('M j, Y H:i', $_SESSION['login_time']); ?>
    </p>
</div>

<div class="row mb-4">
    <?php if ($staffRole === ROLE_RECEPTIONIST): // Use primary role constant ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                New Patients Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['new_patients'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                                Today's Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['today_appointments'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($staffRole === ROLE_NURSE): // Use primary role constant ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Current Admissions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['current_admissions'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bed fa-2x text-gray-300"></i>
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
                                Available Beds
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['available_beds'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($staffRole === ROLE_ACCOUNTANT): // Use primary role constant ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['pending_payments'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                                Today's Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($stats['today_revenue'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Patients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_patients'] ?? 0; ?>
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['today_appointments'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Current Time
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo date('H:i'); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Today's Date
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo date('M j, Y'); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <?php if ($staffRole === ROLE_RECEPTIONIST): // Use primary role constant ?>
                        <div class="col-md-3 mb-3">
                            <a href="patients.php?action=add" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i><br>
                                Register Patient
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="appointments.php?action=add" class="btn btn-success w-100">
                                <i class="fas fa-calendar-plus"></i><br>
                                Schedule Appointment
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="patients.php" class="btn btn-info w-100">
                                <i class="fas fa-search"></i><br>
                                Search Patient
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="reports.php" class="btn btn-warning w-100">
                                <i class="fas fa-chart-bar"></i><br>
                                View Reports
                            </a>
                        </div>
                        
                    <?php elseif ($staffRole === ROLE_NURSE): // Use primary role constant ?>
                        <div class="col-md-3 mb-3">
                            <a href="admissions.php?action=add" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i><br>
                                Admit Patient
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admissions.php" class="btn btn-success w-100">
                                <i class="fas fa-bed"></i><br>
                                Current Admissions
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="patients.php" class="btn btn-info w-100">
                                <i class="fas fa-search"></i><br>
                                Search Patient
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="reports.php" class="btn btn-warning w-100">
                                <i class="fas fa-chart-bar"></i><br>
                                View Reports
                            </a>
                        </div>
                        
                    <?php elseif ($staffRole === ROLE_ACCOUNTANT): // Use primary role constant ?>
                        <div class="col-md-3 mb-3">
                            <a href="billing.php?action=add" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i><br>
                                Create Bill
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="billing.php" class="btn btn-success w-100">
                                <i class="fas fa-credit-card"></i><br>
                                Process Payment
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="billing.php?status=pending" class="btn btn-warning w-100">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                Outstanding Bills
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="reports.php" class="btn btn-info w-100">
                                <i class="fas fa-chart-bar"></i><br>
                                Financial Reports
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <div class="col-md-3 mb-3">
                            <a href="patients.php" class="btn btn-primary w-100">
                                <i class="fas fa-users"></i><br>
                                View Patients
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="appointments.php" class="btn btn-success w-100">
                                <i class="fas fa-calendar"></i><br>
                                Appointments
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="reports.php" class="btn btn-info w-100">
                                <i class="fas fa-chart-bar"></i><br>
                                View Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-cog"></i><br>
                                Settings
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i> Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Activity tracking coming soon</h5>
                    <p class="text-muted">Recent activities will be displayed here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ... (CSS remains the same) ... */
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
.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
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