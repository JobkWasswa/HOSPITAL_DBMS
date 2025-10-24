<?php
/**
 * Reports - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/ReportsController.php';

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
$reportsController = new ReportsController($pdo, $auth);

$action = $_GET['action'] ?? 'dashboard';
$reportType = $_GET['type'] ?? '';

// Handle different report types
switch ($reportType) {
    case 'patients':
        $result = $reportsController->index();
        // Data is already in $result['data'] for specific report types
        break;
    case 'appointments':
        $result = $reportsController->index();
        // Data is already in $result['data'] for specific report types
        break;
    case 'admissions':
        $result = $reportsController->index();
        // Data is already in $result['data'] for specific report types
        break;
    case 'payments':
        $result = $reportsController->index();
        // Data is already in $result['data'] for specific report types (financial totals)
        break;
    case 'lab_tests':
        $result = $reportsController->index();
        $result['data'] = [];
        break;
    default:
        $result = $reportsController->index();
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Reports & Analytics';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line"></i>
        Reports & Analytics
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i>
                Print Report
            </button>
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

<!-- Report Navigation -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills nav-fill">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === '' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'patients' ? 'active' : ''; ?>" href="reports.php?type=patients">
                            <i class="fas fa-users"></i> Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'appointments' ? 'active' : ''; ?>" href="reports.php?type=appointments">
                            <i class="fas fa-calendar"></i> Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'admissions' ? 'active' : ''; ?>" href="reports.php?type=admissions">
                            <i class="fas fa-bed"></i> Admissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'payments' ? 'active' : ''; ?>" href="reports.php?type=payments">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
                    </li>
                    
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if ($reportType === ''): ?>
    <!-- Dashboard Report -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Patients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $result['data']['patients']['total_patients'] ?? 0; ?>
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
                                <?php echo $result['data']['appointments']['total_appointments'] ?? 0; ?>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Current Admissions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $result['data']['admissions']['current_admissions'] ?? 0; ?>
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Today's Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($result['data']['financial']['total_revenue'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Recent Appointments
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($result['data']['appointments']['recent_appointments'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['data']['appointments']['recent_appointments'] as $appointment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                            <td><?php echo date('M j, H:i', strtotime($appointment['appointment_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($appointment['appointment_status']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent appointments</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Payment Status
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($result['data']['financial']['payment_stats'])): ?>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-success">
                                    <h4><?php echo $result['data']['financial']['payment_stats']['paid'] ?? 0; ?></h4>
                                    <small>Paid</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-warning">
                                    <h4><?php echo $result['data']['financial']['payment_stats']['pending'] ?? 0; ?></h4>
                                    <small>Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-danger">
                                    <h4><?php echo $result['data']['financial']['payment_stats']['declined'] ?? 0; ?></h4>
                                    <small>Declined</small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No payment data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php if ($reportType === 'payments' && $staffRole === STAFF_ACCOUNTANT): ?>
        <!-- Accountant-only Payments Summary (Bar Chart) -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> Revenue by Source</h5>
                <small class="text-muted">Appointments, Lab Tests, Prescriptions, Treatments, Admissions</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <canvas id="paymentsBarChart" height="120"></canvas>
                    </div>
                </div>
                <?php
                // Prepare labels and data from $result['data'] (array of ['source','total_amount'])
                $labels = [];
                $totals = [];
                if (is_array($result['data'])) {
                    foreach ($result['data'] as $row) {
                        if (isset($row['source'])) {
                            $labels[] = $row['source'];
                            $totals[] = (float)($row['total_amount'] ?? 0);
                        }
                    }
                }
                ?>
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
                <script>
                (function() {
                    const ctx = document.getElementById('paymentsBarChart');
                    const data = {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            label: 'Total Amount ($)',
                            data: <?php echo json_encode($totals); ?>,
                            backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b'],
                            borderColor: '#ffffff',
                            borderWidth: 1
                        }]
                    };
                    new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: (ctx) => '$' + Number(ctx.parsed.y).toFixed(2) } }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { callback: (v) => '$' + v } }
                            }
                        }
                    });
                })();
                </script>

                <!-- Compact totals table below chart -->
                <div class="table-responsive mt-4">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Source</th>
                                <th class="text-end">Total Amount ($)</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['data'] as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['source'] ?? ''); ?></td>
                                    <td class="text-end">$<?php echo number_format($row['total_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo (int)($row['count'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Fallback: existing generic specific-report table (kept for other types) -->
    <?php if ($reportType !== 'payments'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-line"></i>
                <?php echo ucfirst(str_replace('_', ' ', $reportType)); ?> Report
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($result['data'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <?php if ($reportType === 'patients'): ?>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Phone</th>
                                </tr>
                            <?php elseif ($reportType === 'appointments'): ?>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Fee</th>
                                </tr>
                            <?php elseif ($reportType === 'admissions'): ?>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Room Type</th>
                                    <th>Admission Date</th>
                                    <th>Discharge Date</th>
                                    <th>Status</th>
                                </tr>
                            <?php elseif ($reportType === 'lab_tests'): ?>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Test Type</th>
                                    <th>Results</th>
                                    <th>Cost</th>
                                    <th>Date</th>
                                </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php foreach ($result['data'] as $item): ?>
                                <tr>
                                    <?php if ($reportType === 'patients'): ?>
                                        <td><span class="badge bg-info"><?php echo $item['patient_id'] ?? 'N/A'; ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['gender'] ?? 'N/A'); ?></td>
                                        <td><?php echo isset($item['DOB']) ? date_diff(date_create($item['DOB']), date_create('today'))->y . ' years' : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($item['phone'] ?? 'N/A'); ?></td>
                                    <?php elseif ($reportType === 'appointments'): ?>
                                        <td><span class="badge bg-info"><?php echo $item['appointment_id'] ?? 'N/A'; ?></span></td>
                                        <td><?php echo htmlspecialchars($item['patient_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($item['doctor_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo isset($item['appointment_date']) ? date('M j, Y H:i', strtotime($item['appointment_date'])) : 'N/A'; ?></td>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($item['appointment_status'] ?? 'N/A'); ?></span></td>
                                        <td>$<?php echo number_format($item['consultation_fee'] ?? 0, 2); ?></td>
                                    <?php elseif ($reportType === 'admissions'): ?>
                                        <td><span class="badge bg-info"><?php echo $item['admission_id'] ?? 'N/A'; ?></span></td>
                                        <td><?php echo htmlspecialchars($item['patient_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($item['room_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo isset($item['admission_date']) ? date('M j, Y', strtotime($item['admission_date'])) : 'N/A'; ?></td>
                                        <td><?php echo isset($item['discharge_date']) && $item['discharge_date'] ? date('M j, Y', strtotime($item['discharge_date'])) : 'Active'; ?></td>
                                        <td>
                                            <span class="badge <?php echo isset($item['discharge_date']) && $item['discharge_date'] ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo isset($item['discharge_date']) && $item['discharge_date'] ? 'Discharged' : 'Active'; ?>
                                            </span>
                                        </td>
                                    <?php elseif ($reportType === 'lab_tests'): ?>
                                        <td><span class="badge bg-info"><?php echo $item['test_id'] ?? 'N/A'; ?></span></td>
                                        <td><?php echo htmlspecialchars($item['patient_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($item['test_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo isset($item['results']) ? htmlspecialchars(substr($item['results'], 0, 20)) . (strlen($item['results']) > 20 ? '...' : '') : 'N/A'; ?></td>
                                        <td>$<?php echo number_format($item['test_cost'] ?? 0, 2); ?></td>
                                        <td><?php echo isset($item['test_date']) ? date('M j, Y', strtotime($item['test_date'])) : 'N/A'; ?></td>

                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No data available</h4>
                    <p class="text-muted">No <?php echo str_replace('_', ' ', $reportType); ?> data found for the selected criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.text-xs { font-size: 0.7rem; }
.text-gray-300 { color: #dddfeb !important; }
.text-gray-800 { color: #5a5c69 !important; }
.shadow { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important; }
@media print { .btn-toolbar, .nav-pills, .card-header { display: none !important; } .card { border: none !important; box-shadow: none !important; } }
</style>

<?php include '../layouts/footer.php'; ?>
