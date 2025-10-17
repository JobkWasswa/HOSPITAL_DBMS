<?php
/**
 * Reports - Doctor View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
require_once '../../../src/controllers/ReportsController.php';

// Initialize authentication
$auth = new Auth($pdo);
$auth->requireRole(ROLE_DOCTOR);

$currentUser = $auth->getCurrentUser();

// Initialize controller
$reportsController = new ReportsController($pdo, $auth);

$action = $_GET['action'] ?? 'view';
$reportType = $_GET['report'] ?? 'overview';

// Handle export action
if ($action === 'export') {
    $reportsController->export();
}

$result = $reportsController->index();

$pageTitle = 'Reports & Analytics';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar"></i>
        Reports & Analytics
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="?action=export&report=<?php echo $reportType; ?>&format=csv" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="report" class="form-label">Report Type</label>
                <select class="form-select" id="report" name="report" onchange="this.form.submit()">
                    <option value="overview" <?php echo $reportType === 'overview' ? 'selected' : ''; ?>>Overview</option>
                    <option value="financial" <?php echo $reportType === 'financial' ? 'selected' : ''; ?>>Financial Report</option>
                    <option value="doctors" <?php echo $reportType === 'doctors' ? 'selected' : ''; ?>>Doctor Performance</option>
                    <option value="departments" <?php echo $reportType === 'departments' ? 'selected' : ''; ?>>Department Report</option>
                    <option value="rooms" <?php echo $reportType === 'rooms' ? 'selected' : ''; ?>>Room Utilization</option>
                    <option value="inventory" <?php echo $reportType === 'inventory' ? 'selected' : ''; ?>>Medicine Inventory</option>
                    <option value="demographics" <?php echo $reportType === 'demographics' ? 'selected' : ''; ?>>Patient Demographics</option>
                    <option value="trends" <?php echo $reportType === 'trends' ? 'selected' : ''; ?>>Monthly Trends</option>
                    <option value="charts_doctor" <?php echo $reportType === 'charts_doctor' ? 'selected' : ''; ?>>Charts (Line/Pie/Bar)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                        value="<?php echo $result['startDate']; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                        value="<?php echo $result['endDate']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($reportType === 'overview'): ?>
    <div class="row mb-4">
        <div class="col-md-3">
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
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Appointments
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
        
        <div class="col-md-3">
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
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($result['data']['appointments']['total_revenue'] ?? 0, 2); ?>
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

<?php elseif ($reportType === 'patients'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Patient Statistics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Gender Distribution</h6>
                    <p><strong>Male:</strong> <?php echo $result['data']['male_patients'] ?? 0; ?></p>
                    <p><strong>Female:</strong> <?php echo $result['data']['female_patients'] ?? 0; ?></p>
                    <p><strong>Other:</strong> <?php echo $result['data']['other_patients'] ?? 0; ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Recent Activity</h6>
                    <p><strong>New Patients (30 days):</strong> <?php echo $result['data']['new_patients_30_days'] ?? 0; ?></p>
                    <p><strong>Total Patients:</strong> <?php echo $result['data']['total_patients'] ?? 0; ?></p>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'appointments'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Appointment Report</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Appointment Status</h6>
                    <p><strong>Scheduled:</strong> <?php echo $result['data']['scheduled'] ?? 0; ?></p>
                    <p><strong>Completed:</strong> <?php echo $result['data']['completed'] ?? 0; ?></p>
                    <p><strong>Cancelled:</strong> <?php echo $result['data']['cancelled'] ?? 0; ?></p>
                    <p><strong>No Show:</strong> <?php echo $result['data']['no_show'] ?? 0; ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Financial Summary</h6>
                    <p><strong>Total Appointments:</strong> <?php echo $result['data']['total_appointments'] ?? 0; ?></p>
                    <p><strong>Total Revenue:</strong> $<?php echo number_format($result['data']['total_revenue'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'admissions'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Admission Report</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Admission Status</h6>
                    <p><strong>Total Admissions:</strong> <?php echo $result['data']['total_admissions'] ?? 0; ?></p>
                    <p><strong>Current Admissions:</strong> <?php echo $result['data']['current_admissions'] ?? 0; ?></p>
                    <p><strong>Discharged:</strong> <?php echo $result['data']['discharged'] ?? 0; ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Length of Stay</h6>
                    <p><strong>Average Length:</strong> <?php echo number_format($result['data']['avg_length_of_stay'] ?? 0, 1); ?> days</p>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'financial'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Financial Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($result['data'] ?? []) as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['source'] ?? ''); ?></td>
                                <td><?php echo (int)($item['count'] ?? 0); ?></td>
                                <td>$<?php echo number_format($item['total_amount'] ?? 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'doctors'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Doctor Performance Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Appointments</th>
                            <th>Treatments</th>
                            <th>Unique Patients</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($result['data'] ?? []) as $doctor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['doctor_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?></td>
                                <td><?php echo (int)($doctor['total_appointments'] ?? 0); ?></td>
                                <td><?php echo (int)($doctor['total_treatments'] ?? 0); ?></td>
                                <td><?php echo (int)($doctor['unique_patients'] ?? 0); ?></td>
                                <td>$<?php echo number_format(($doctor['appointment_revenue'] ?? 0) + ($doctor['treatment_revenue'] ?? 0), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'departments'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Department Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Doctors</th>
                            <th>Staff</th>
                            <th>Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($result['data'] ?? []) as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['department_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($dept['location'] ?? ''); ?></td>
                                <td><?php echo (int)($dept['total_doctors'] ?? 0); ?></td>
                                <td><?php echo (int)($dept['total_staff'] ?? 0); ?></td>
                                <td><?php echo (int)($dept['total_appointments'] ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'rooms'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Room Utilization Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Room Type</th>
                            <th>Total Beds</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Utilization %</th>
                            <th>Daily Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($result['data'] ?? []) as $room): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['room_type'] ?? ''); ?></td>
                                
                                <td><?php echo (int)($room['total_beds'] ?? 0); ?></td>
                                
                                <td><?php echo (int)($room['occupied_beds'] ?? 0); ?></td>
                                
                                <td><?php echo (int)($room['available_beds'] ?? 0); ?></td>
                                
                                <td>
                                    <?php $utilization = number_format((float)($room['utilization_percentage'] ?? 0), 2); ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $utilization; ?>%">
                                            <?php echo $utilization; ?>%
                                        </div>
                                    </div>
                                </td>
                                
                                <td>$<?php echo number_format($room['daily_cost'] ?? 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'inventory'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Medicine Inventory Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine</th>
                            <th>Dosage</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($result['data'] ?? []) as $medicine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($medicine['dosage'] ?? ''); ?></td>
                                <td><?php echo (int)($medicine['stock_quantity'] ?? 0); ?></td>
                                <td>$<?php echo number_format($medicine['medicine_price'] ?? 0, 2); ?></td>
                                <td>$<?php echo number_format($medicine['total_value'] ?? 0, 2); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($medicine['stock_status'] ?? 'Unknown') {
                                        'Critical' => 'bg-danger',
                                        'Low' => 'bg-warning',
                                        'Adequate' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($medicine['stock_status'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'demographics'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Patient Demographics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Gender</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($result['data'] ?? []) as $demo): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($demo['gender'] ?? 'N/A'); ?></td>
                                        <td><?php echo (int)($demo['count'] ?? 0); ?></td>
                                        <td><?php echo number_format($demo['percentage'] ?? 0, 2); ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <canvas id="demographicsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'trends'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Monthly Trends</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Appointments</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($result['data'] ?? []) as $trend): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($trend['month_name'] ?? 'N/A'); ?></td>
                                <td><?php echo (int)($trend['appointments'] ?? 0); ?></td>
                                <td>$<?php echo number_format($trend['revenue'] ?? 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($reportType === 'charts_doctor'): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Appointments per Day</h5>
        </div>
        <div class="card-body">
            <canvas id="appointmentsPerDayChart" height="90"></canvas>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Appointment Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="appointmentStatusPie" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Medicines by Quantity</h5>
                </div>
                <div class="card-body">
                    <canvas id="topMedicinesBar" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    (function() {
        // Prepare datasets from PHP
        const perDay = <?php echo json_encode($result['data']['appointments_per_day'] ?? []); ?>;
        const perDayLabels = perDay.map(r => r.day);
        const perDayCounts = perDay.map(r => Number(r.count));

        const statusShare = <?php echo json_encode($result['data']['appointment_status_share'] ?? []); ?>;
        const statusLabels = statusShare.map(r => r.status);
        const statusCounts = statusShare.map(r => Number(r.count));

        const topMeds = <?php echo json_encode($result['data']['top_medicines'] ?? []); ?>;
        const medLabels = topMeds.map(r => r.medicine_name);
        const medCounts = topMeds.map(r => Number(r.total_quantity));

        // Line: Appointments per Day
        const ctxLine = document.getElementById('appointmentsPerDayChart');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: perDayLabels,
                datasets: [{
                    label: 'Appointments',
                    data: perDayCounts,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78,115,223,0.15)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Pie: Appointment Status Distribution
        const ctxPie = document.getElementById('appointmentStatusPie');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b', '#f6c23e'],
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Bar: Top Medicines by Quantity
        const ctxBar = document.getElementById('topMedicinesBar');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: medLabels,
                datasets: [{
                    label: 'Quantity',
                    data: medCounts,
                    backgroundColor: '#36b9cc',
                    borderColor: '#36b9cc',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    })();
    </script>

<?php endif; ?>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
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