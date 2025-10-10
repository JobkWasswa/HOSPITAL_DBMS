<?php
/**
 * Pharmacy Management - Staff View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';

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

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            try {
                $stmt = $pdo->prepare("
                    SELECT p.*, m.name as medicine_name, m.dosage, m.medicine_price
                    FROM prescription p
                    JOIN medicine m ON p.medicine_id = m.medicine_id
                    WHERE p.prescription_id = ?
                ");
                $stmt->execute([$id]);
                $prescription = $stmt->fetch();
                
                if (!$prescription) {
                    $result = ['error' => 'Prescription not found'];
                } else {
                    $result = ['prescription' => $prescription];
                }
            } catch (PDOException $e) {
                $result = ['error' => 'Database error occurred'];
            }
        } else {
            $result = ['error' => 'Prescription ID required'];
        }
        break;
        
    default:
        // Get prescriptions with pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        try {
            $sql = "
                SELECT p.*, m.name as medicine_name, m.dosage, m.medicine_price,
                       pt.name as patient_name, d.name as doctor_name
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN patient pt ON t.patient_id = pt.patient_id
                JOIN doctor d ON t.doctor_id = d.doctor_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (pt.name LIKE ? OR m.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY p.prescription_id DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $prescriptions = $stmt->fetchAll();
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) as count
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN patient pt ON t.patient_id = pt.patient_id
                WHERE 1=1
            ";
            $countParams = [];
            
            if (!empty($search)) {
                $countSql .= " AND (pt.name LIKE ? OR m.name LIKE ?)";
                $countParams[] = "%$search%";
                $countParams[] = "%$search%";
            }
            
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $totalCount = $stmt->fetch()['count'];
            $totalPages = ceil($totalCount / $limit);
            
            $result = [
                'prescriptions' => $prescriptions,
                'totalPrescriptions' => $totalCount,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'search' => $search
            ];
        } catch (PDOException $e) {
            $result = ['error' => 'Database error occurred'];
        }
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Pharmacy Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-pills"></i>
        Pharmacy Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="?action=medicines" class="btn btn-outline-primary">
                <i class="fas fa-medkit"></i>
                Medicine Inventory
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
    <!-- Prescription List View -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by patient name or medicine..." 
                               value="<?php echo htmlspecialchars($result['search'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="pharmacy.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Prescriptions List
                <span class="badge bg-primary ms-2"><?php echo $result['totalPrescriptions'] ?? 0; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['prescriptions'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No prescriptions found</h4>
                    <p class="text-muted">
                        <?php if (!empty($result['search'])): ?>
                            No prescriptions match your search criteria.
                        <?php else: ?>
                            No prescriptions have been issued yet.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Quantity</th>
                                <th>Instructions</th>
                                <th>Doctor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['prescriptions'] as $prescription): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $prescription['prescription_id']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prescription['patient_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($prescription['medicine_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($prescription['dosage']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $prescription['quantity']; ?></span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($prescription['dosage_instructions'], 0, 30)) . (strlen($prescription['dosage_instructions']) > 30 ? '...' : ''); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($prescription['doctor_name']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $prescription['prescription_id']; ?>" 
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
                    <nav aria-label="Prescriptions pagination">
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

<?php elseif ($action === 'view' && isset($result['prescription'])): ?>
    <!-- Prescription View -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Prescription Details</h5>
                </div>
                <div class="card-body">
                    <h4>Prescription #<?php echo $result['prescription']['prescription_id']; ?></h4>
                    
                    <hr>
                    
                    <p><strong>Medicine:</strong> <?php echo htmlspecialchars($result['prescription']['medicine_name']); ?></p>
                    <p><strong>Dosage:</strong> <?php echo htmlspecialchars($result['prescription']['dosage']); ?></p>
                    <p><strong>Quantity:</strong> <?php echo $result['prescription']['quantity']; ?></p>
                    <p><strong>Instructions:</strong> <?php echo htmlspecialchars($result['prescription']['dosage_instructions']); ?></p>
                    <p><strong>Price per unit:</strong> $<?php echo number_format($result['prescription']['medicine_price'], 2); ?></p>
                    <p><strong>Total Cost:</strong> $<?php echo number_format($result['prescription']['quantity'] * $result['prescription']['medicine_price'], 2); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="pharmacy.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>
