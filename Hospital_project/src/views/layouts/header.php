<?php
/**
 * Header Layout
 * Hospital Management System
 */

if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}

$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/public/index.php">
                <i class="fas fa-hospital"></i>
                <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php if ($currentUser['role'] === ROLE_DOCTOR): ?>
                            <!-- Doctor Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="patients.php">
                                    <i class="fas fa-users"></i> Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="appointments.php">
                                    <i class="fas fa-calendar-alt"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="treatments.php">
                                    <i class="fas fa-stethoscope"></i> Treatments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="reports.php">
                                    <i class="fas fa-chart-line"></i> Reports
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Staff Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="patients.php">
                                    <i class="fas fa-users"></i> Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="appointments.php">
                                    <i class="fas fa-calendar-alt"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admissions.php">
                                    <i class="fas fa-bed"></i> Admissions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="pharmacy.php">
                                    <i class="fas fa-pills"></i> Pharmacy
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lab.php">
                                    <i class="fas fa-flask"></i> Lab Tests
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="billing.php">
                                    <i class="fas fa-credit-card"></i> Billing
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="reports.php">
                                    <i class="fas fa-chart-line"></i> Reports
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <?php if ($auth->isLoggedIn()): ?>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($currentUser['username']); ?>
                            <span class="badge bg-light text-dark ms-1">
                                <?php echo ucfirst($currentUser['role']); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/public/login.php?logout=1">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <?php if ($auth->isLoggedIn()): ?>
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <?php if ($currentUser['role'] === ROLE_DOCTOR): ?>
                            <!-- Doctor Sidebar -->
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="patients.php">
                                    <i class="fas fa-users"></i> All Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="appointments.php">
                                    <i class="fas fa-calendar-check"></i> All Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="treatments.php">
                                    <i class="fas fa-stethoscope"></i> Treatments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="reports.php">
                                    <i class="fas fa-chart-line"></i> My Reports
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Staff Sidebar -->
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="patients.php">
                                    <i class="fas fa-users"></i> All Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="appointments.php">
                                    <i class="fas fa-calendar-check"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admissions.php">
                                    <i class="fas fa-bed"></i> Admissions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="pharmacy.php">
                                    <i class="fas fa-pills"></i> Pharmacy
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lab.php">
                                    <i class="fas fa-flask"></i> Lab Tests
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="billing.php">
                                    <i class="fas fa-credit-card"></i> Billing
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="reports.php">
                                    <i class="fas fa-chart-line"></i> Reports
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php else: ?>
            <main class="col-12">
            <?php endif; ?>