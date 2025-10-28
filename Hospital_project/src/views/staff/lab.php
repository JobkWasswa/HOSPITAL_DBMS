<?php
/**
 * Lab Tests Management - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
// Include constants, although ROLE_STAFF is now deprecated.
require_once '../../../config/constants.php'; 
require_once '../../../src/helpers/Auth.php';

// Initialize authentication
$auth = new Auth($pdo);
// Removed: $auth->requireRole(ROLE_STAFF); as requested, allowing access to all authenticated users.
$currentUser = $auth->getCurrentUser();

// Get specific role from the combined 'users' table for UI customization
try {
    // Assuming the specific role name is stored in the 'role' column of the 'users' table
    $stmt = $pdo->prepare("
        SELECT u.role 
        FROM users u
        WHERE u.user_id = ?
    ");
    $stmt->execute([$currentUser['user_id']]);
    $userInfo = $stmt->fetch();
    // Use the role from the users table, defaulting to 'Staff'
    // This variable is primarily used to check if the user is a 'Lab Technician' to show/hide specific UI elements.
    $staffRole = $userInfo['role'] ?? 'Staff'; 
} catch (PDOException $e) {
    // Fallback if DB connection fails, prevents variable access errors later
    $staffRole = 'Staff';
    error_log("Error fetching user role for Lab view: " . $e->getMessage());
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            try {
                $stmt = $pdo->prepare("
                    SELECT lt.*, p.name as patient_name, p.phone as patient_phone
                    FROM lab_test lt
                    JOIN patient p ON lt.patient_id = p.patient_id
                    WHERE lt.test_id = ?
                ");
                $stmt->execute([$id]);
                $labTest = $stmt->fetch();
                
                if (!$labTest) {
                    $result = ['error' => 'Lab test not found'];
                } else {
                    $result = ['labTest' => $labTest];
                }
            } catch (PDOException $e) {
                $result = ['error' => 'Database error occurred'];
            }
        } else {
            $result = ['error' => 'Lab test ID required'];
        }
        break;
        
    case 'add':
        // In staff area, prohibit creating lab tests; handled by doctors workflow
        $result = ['error' => 'Access denied. Lab tests are ordered by doctors and managed by lab technicians.'];
        break;
        
    default:
        // Ensure constants are defined before use
        if (!defined('RECORDS_PER_PAGE')) {
            define('RECORDS_PER_PAGE', 10);
        }

        // Get lab tests with pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        try {
            $sql = "
                SELECT lt.*, p.name as patient_name, p.phone as patient_phone
                FROM lab_test lt
                JOIN patient p ON lt.patient_id = p.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.name LIKE ? OR lt.test_type LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY lt.test_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $labTests = $stmt->fetchAll();
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) as count
                FROM lab_test lt
                JOIN patient p ON lt.patient_id = p.patient_id
                WHERE 1=1
            ";
            $countParams = [];
            
            if (!empty($search)) {
                $countSql .= " AND (p.name LIKE ? OR lt.test_type LIKE ?)";
                $countParams[] = "%$search%";
                $countParams[] = "%$search%";
            }
            
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $totalCount = $stmt->fetch()['count'];
            $totalPages = ceil($totalCount / $limit);
            
            $result = [
                'labTests' => $labTests,
                'totalLabTests' => $totalCount,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'search' => $search
            ];
        } catch (PDOException $e) {
            $result = ['error' => 'Database error occurred'];
            error_log("Lab list fetching error: " . $e->getMessage());
        }
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Lab Tests Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-flask"></i>
        Lab Tests Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($staffRole === 'Lab Technician'): ?>
            <div class="btn-group me-2">
                <!-- Add disabled button with note -->
                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="fas fa-plus"></i>
                    Add Lab Test (Doctor orders only)
                </button>
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
    <!-- Lab Tests List View -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by patient name or test type..." 
                               value="<?php echo htmlspecialchars($result['search'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="lab.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Lab Tests List
                <span class="badge bg-primary ms-2"><?php echo $result['totalLabTests'] ?? 0; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['labTests'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No lab tests found</h4>
                    <p class="text-muted">
                        <?php if (!empty($result['search'])): ?>
                            No lab tests match your search criteria.
                        <?php else: ?>
                            No lab tests have been performed yet.
                        <?php endif; ?>
                    </p>
                    <?php if (empty($result['search']) && $staffRole === 'Lab Technician'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add First Lab Test
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
                                <th>Test Type</th>
                                <th>Results</th>
                                <th>Date</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['labTests'] as $labTest): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $labTest['test_id']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($labTest['patient_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($labTest['test_type']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($labTest['results'], 0, 20)) . (strlen($labTest['results']) > 20 ? '...' : ''); ?>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y H:i', strtotime($labTest['test_date'])); ?>
                                    </td>
                                    <td>
                                        $<?php echo number_format($labTest['test_cost'], 2); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $labTest['test_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($result['totalPages'] > 1): ?>
                    <nav aria-label="Lab tests pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($result['currentPage'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&page=<?php echo $result['currentPage'] - 1; ?>">
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
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($result['currentPage'] < $result['totalPages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&page=<?php echo $result['currentPage'] + 1; ?>">
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

<?php elseif ($action === 'view' && isset($result['labTest'])): ?>
    <!-- Lab Test View -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Lab Test Report #<?php echo $result['labTest']['test_id']; ?>
                    </h5>
                </div>
                <div class="card-body">
                    
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Patient:</strong></p>
                            <p class="lead text-primary"><?php echo htmlspecialchars($result['labTest']['patient_name']); ?></p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <p class="mb-1"><strong>Test Date:</strong></p>
                            <p class="lead"><?php echo date('M j, Y H:i', strtotime($result['labTest']['test_date'])); ?></p>
                        </div>
                    </div>

                    <hr class="my-3">
                    
                    <h5 class="mt-4 mb-2">Test Information</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Test Type:
                            <span class="fw-bold"><?php echo htmlspecialchars($result['labTest']['test_type']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cost:
                            <span class="badge bg-success fs-6">$<?php echo number_format($result['labTest']['test_cost'], 2); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Contact Phone:
                            <span class="fw-bold"><a href="tel:<?php echo htmlspecialchars($result['labTest']['patient_phone']); ?>"><?php echo htmlspecialchars($result['labTest']['patient_phone']); ?></a></span>
                        </li>
                    </ul>
                    
                    <h5 class="mt-4 mb-2"><i class="fas fa-clipboard-list"></i> Results / Notes</h5>
                    <div class="alert alert-light border p-3">
                        <?php 
                        $resultsContent = htmlspecialchars($result['labTest']['results']);
                        echo nl2br($resultsContent ? $resultsContent : 'No detailed results recorded yet.'); 
                        ?>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <!-- Placeholder for Edit/Update Result Action -->
                    <button class="btn btn-warning me-2" disabled>
                        <i class="fas fa-edit"></i> Edit Results (Future Feature)
                    </button>
                    <a href="lab.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>
