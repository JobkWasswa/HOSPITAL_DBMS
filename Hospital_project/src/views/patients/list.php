<?php
/**
 * Patient List View
 * Hospital Management System
 */

require_once '../../config/db.php';
require_once '../../src/helpers/auth.php';
require_once '../../src/models/Patient.php';

// Require login
$auth->requireLogin();

// Initialize patient model
$patientModel = new Patient($pdo);

// Handle search and pagination
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Get patients
$patients = $patientModel->getAll($search, $limit, $offset);
$totalPatients = $patientModel->getTotalCount($search);
$totalPages = ceil($totalPatients / $limit);

// Handle patient deletion
if (isset($_GET['delete']) && $auth->hasAnyRole(['admin', 'receptionist'])) {
    $patientId = intval($_GET['delete']);
    if ($patientModel->delete($patientId)) {
        $success = 'Patient deleted successfully.';
    } else {
        $error = 'Failed to delete patient.';
    }
    // Redirect to avoid resubmission
    header('Location: list.php?search=' . urlencode($search) . '&page=' . $page);
    exit();
}

$pageTitle = 'Patient Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users"></i>
        Patient Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-user-plus"></i>
                Add New Patient
            </a>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search and Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search by name, patient number, or phone..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="list.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Patients Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Patients List
            <span class="badge bg-primary ms-2"><?php echo $totalPatients; ?> total</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($patients)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No patients found</h4>
                <p class="text-muted">
                    <?php if ($search): ?>
                        No patients match your search criteria.
                    <?php else: ?>
                        No patients have been registered yet.
                    <?php endif; ?>
                </p>
                <?php if (!$search): ?>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Add First Patient
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Patient #</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Phone</th>
                            <th>Blood Type</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <?php
                            $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($patient['patient_number']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <?php echo strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong>
                                            <?php if ($patient['email']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($patient['email']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $patient['gender'] === 'Male' ? 'primary' : ($patient['gender'] === 'Female' ? 'danger' : 'secondary'); ?>">
                                        <?php echo htmlspecialchars($patient['gender']); ?>
                                    </span>
                                </td>
                                <td><?php echo $age; ?> years</td>
                                <td>
                                    <?php if ($patient['phone']): ?>
                                        <a href="tel:<?php echo htmlspecialchars($patient['phone']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($patient['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient['blood_type']): ?>
                                        <span class="badge bg-danger"><?php echo htmlspecialchars($patient['blood_type']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($patient['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="profile.php?id=<?php echo $patient['patient_id']; ?>" 
                                           class="btn btn-outline-primary" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=edit&id=<?php echo $patient['patient_id']; ?>" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($auth->hasAnyRole(['admin', 'receptionist'])): ?>
                                        <a href="?delete=<?php echo $patient['patient_id']; ?>" 
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
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Patients pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">
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

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: bold;
}
</style>

<?php include '../layouts/footer.php'; ?>

