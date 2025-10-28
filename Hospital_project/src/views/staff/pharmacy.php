<?php
/**
 * Pharmacy Management - View Accessible by All Logged-in Users
 * Hospital Management System
 */

// Load Configuration and Database Connection ($pdo)
require_once '../../../config/config.php';
// Load ALL defined constants (for role definitions, etc.)
require_once '../../../config/constants.php'; 
require_once '../../../src/helpers/Auth.php';

// Define the pagination constant if it's not in constants.php
if (!defined('RECORDS_PER_PAGE')) {
    // Default pagination limit
    define('RECORDS_PER_PAGE', 20); 
}

// Initialize authentication
$auth = new Auth($pdo);
// >>> FIX 1: Allow access to all authenticated users <<<
// We ensure the user is logged in, but we do NOT check for a specific role.
$auth->requireLogin(); // Assuming this helper function simply checks if a user session exists

$currentUser = $auth->getCurrentUser();

// The user's role is not required for access, but we can fetch their name/role for display if needed.
$userName = htmlspecialchars($currentUser['username'] ?? 'User');


$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            try {
                // >>> FIX APPLIED HERE: Corrected SQL to select fields explicitly and join all necessary tables (t, m, pt, u) <<<
                $stmt = $pdo->prepare("
                    SELECT 
                        t.treatment_id, 
                        t.notes AS dosage_instructions, -- Maps notes field to be used as instructions
                        t.treatment_date,
                        m.name AS medicine_name, 
                        m.dosage, 
                        m.medicine_price,
                        pt.name AS patient_name, -- Fetch patient name
                        u.username AS prescribing_user_name
                    FROM treatment t
                    JOIN medicine m ON t.medicine_id = m.medicine_id
                    JOIN patient pt ON t.patient_id = pt.patient_id -- Added patient join
                    JOIN users u ON t.user_id = u.user_id
                    WHERE t.treatment_id = ? -- Filter by the treatment ID
                ");
                $stmt->execute([$id]);
                $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$prescription) {
                    $result = ['error' => 'Treatment record (Prescription) not found'];
                } else {
                    $result = ['prescription' => $prescription];
                }
            } catch (PDOException $e) {
                error_log("Treatment View Error (ID: $id): " . $e->getMessage());
                $result = ['error' => 'Database error occurred'];
            }
        } else {
            $result = ['error' => 'Treatment ID required'];
        }
        break;
        
    default:
        // Get prescriptions/treatments with pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        try {
            // NOTE: Your schema does not have a 'prescription' table, 
            // but the 'treatment' table links patient, user (doctor), and medicine. 
            // I'm assuming 'treatment' now serves the role of a prescription record.
            
            $sql = "
                SELECT 
                    t.treatment_id, 
                    t.notes AS dosage_instructions, -- Assuming notes are the instructions
                    t.treatment_date,
                    m.name AS medicine_name, 
                    m.dosage, 
                    m.medicine_price,
                    pt.name AS patient_name, 
                    u.username AS prescribing_user_name -- Renamed Doctor Name to User Name
                FROM treatment t
                JOIN medicine m ON t.medicine_id = m.medicine_id
                JOIN patient pt ON t.patient_id = pt.patient_id
                JOIN users u ON t.user_id = u.user_id  -- Joined 'users' instead of deleted 'doctor'
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (pt.name LIKE ? OR m.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY t.treatment_id DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) as count
                FROM treatment t
                JOIN medicine m ON t.medicine_id = m.medicine_id
                JOIN patient pt ON t.patient_id = pt.patient_id
                JOIN users u ON t.user_id = u.user_id
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
            $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $totalPages = ceil($totalCount / $limit);
            
            $result = [
                'prescriptions' => $prescriptions,
                'totalPrescriptions' => $totalCount,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'search' => $search
            ];
        } catch (PDOException $e) {
            error_log("Prescription list error: " . $e->getMessage());
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
    <!-- Prescription/Treatment List View -->
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
                Prescriptions (Treatments) List
                <span class="badge bg-primary ms-2"><?php echo $result['totalPrescriptions'] ?? 0; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['prescriptions'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No treatments requiring pharmacy action found</h4>
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
                                <th>Instructions</th>
                                <th>Prescribed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['prescriptions'] as $prescription): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $prescription['treatment_id']; ?></span>
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
                                        <?php echo htmlspecialchars(substr($prescription['dosage_instructions'], 0, 30)) . (strlen($prescription['dosage_instructions']) > 30 ? '...' : ''); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($prescription['prescribing_user_name']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $prescription['treatment_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- A 'Dispense' action button would typically be placed here -->
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
                    <h5 class="card-title mb-0">Prescription Details (Treatment #<?php echo $result['prescription']['treatment_id']; ?>)</h5>
                </div>
                <div class="card-body">
                    <hr>
                    <!-- Displaying the Patient Name from the fixed query -->
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($result['prescription']['patient_name'] ?? 'N/A'); ?></p>
                    <p><strong>Medicine:</strong> <?php echo htmlspecialchars($result['prescription']['medicine_name']); ?></p>
                    <p><strong>Dosage:</strong> <?php echo htmlspecialchars($result['prescription']['dosage']); ?></p>
                    <p><strong>Instructions:</strong> <?php echo htmlspecialchars($result['prescription']['dosage_instructions']); ?></p>
                    <p><strong>Prescribed By:</strong> <?php echo htmlspecialchars($result['prescription']['prescribing_user_name']); ?></p>
                    <p><strong>Price per unit:</strong> $<?php echo number_format($result['prescription']['medicine_price'], 2); ?></p>
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
