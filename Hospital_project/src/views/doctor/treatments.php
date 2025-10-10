<?php
/**
 * Treatments Management - Doctor View
 * Hospital Management System
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';

// Initialize authentication
$auth = new Auth($pdo);
$auth->requireRole(ROLE_DOCTOR);

$currentUser = $auth->getCurrentUser();

// Get doctor information
try {
    $stmt = $pdo->prepare("
        SELECT d.name as doctor_name, d.specialization, d.doctor_id
        FROM doctor d
        WHERE d.doctor_id = (SELECT doctor_id FROM users WHERE user_id = ?)
    ");
    $stmt->execute([$currentUser['user_id']]);
    $doctorInfo = $stmt->fetch();
    $doctorName = $doctorInfo['doctor_name'] ?? 'Doctor';
    $specialization = $doctorInfo['specialization'] ?? 'General';
    $doctorId = $doctorInfo['doctor_id'] ?? null;
} catch (PDOException $e) {
    $doctorName = 'Doctor';
    $specialization = 'General';
    $doctorId = null;
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            try {
                $stmt = $pdo->prepare("
                    SELECT t.*, p.name as patient_name, p.phone as patient_phone, p.DOB, p.gender,
                           d.name as doctor_name, d.specialization
                    FROM treatment t
                    JOIN patient p ON t.patient_id = p.patient_id
                    JOIN doctor d ON t.doctor_id = d.doctor_id
                    WHERE t.treatment_id = ?
                ");
                $stmt->execute([$id]);
                $treatment = $stmt->fetch();
                
                if (!$treatment) {
                    $result = ['error' => 'Treatment not found'];
                } else {
                    $result = ['treatment' => $treatment];
                }
            } catch (PDOException $e) {
                $result = ['error' => 'Database error occurred'];
            }
        } else {
            $result = ['error' => 'Treatment ID required'];
        }
        break;
        
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'notes' => trim($_POST['notes'] ?? ''),
                'treatment_date' => $_POST['treatment_date'] ?? date('Y-m-d H:i:s'),
                'treatment_fee' => floatval($_POST['treatment_fee'] ?? 0),
                'patient_id' => intval($_POST['patient_id'] ?? 0),
                'doctor_id' => $doctorId
            ];
            
            $errors = [];
            if (empty($data['notes'])) $errors[] = 'Treatment notes are required';
            if (empty($data['patient_id'])) $errors[] = 'Patient is required';
            if ($data['treatment_fee'] < 0) $errors[] = 'Treatment fee cannot be negative';
            
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO treatment (notes, treatment_date, treatment_fee, patient_id, doctor_id) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    if ($stmt->execute([$data['notes'], $data['treatment_date'], $data['treatment_fee'], $data['patient_id'], $data['doctor_id']])) {
                        header('Location: treatments.php?success=' . urlencode('Treatment recorded successfully'));
                        exit();
                    } else {
                        $result = ['error' => 'Failed to record treatment'];
                    }
                } catch (PDOException $e) {
                    $result = ['error' => 'Database error occurred'];
                }
            } else {
                $result = ['errors' => $errors, 'data' => $data];
            }
        }
        break;
        
    default:
        // Get treatments for current doctor with pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        try {
            $sql = "
                SELECT t.*, p.name as patient_name, p.phone as patient_phone
                FROM treatment t
                JOIN patient p ON t.patient_id = p.patient_id
                WHERE t.doctor_id = ?
            ";
            $params = [$doctorId];
            
            if (!empty($search)) {
                $sql .= " AND p.name LIKE ?";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY t.treatment_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $treatments = $stmt->fetchAll();
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) as count
                FROM treatment t
                JOIN patient p ON t.patient_id = p.patient_id
                WHERE t.doctor_id = ?
            ";
            $countParams = [$doctorId];
            
            if (!empty($search)) {
                $countSql .= " AND p.name LIKE ?";
                $countParams[] = "%$search%";
            }
            
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $totalCount = $stmt->fetch()['count'];
            $totalPages = ceil($totalCount / $limit);
            
            $result = [
                'treatments' => $treatments,
                'totalTreatments' => $totalCount,
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

$pageTitle = 'Treatments Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-stethoscope"></i>
        Treatments Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <span class="badge bg-info">
                <i class="fas fa-user-md"></i>
                <?php echo htmlspecialchars($doctorName); ?> - <?php echo htmlspecialchars($specialization); ?>
            </span>
        </div>
        <div class="btn-group me-2">
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Record Treatment
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
    <!-- Treatment List View -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by patient name..." 
                               value="<?php echo htmlspecialchars($result['search']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="treatments.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                My Treatments
                <span class="badge bg-primary ms-2"><?php echo $result['totalTreatments']; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['treatments'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-stethoscope fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No treatments found</h4>
                    <p class="text-muted">
                        <?php if ($result['search']): ?>
                            No treatments match your search criteria.
                        <?php else: ?>
                            No treatments have been recorded yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!$result['search']): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Record First Treatment
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
                                <th>Date</th>
                                <th>Notes</th>
                                <th>Fee</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['treatments'] as $treatment): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $treatment['treatment_id']; ?></span>
                                    </td>
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
                                        $<?php echo number_format($treatment['treatment_fee'], 2); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $treatment['treatment_id']; ?>" 
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
                    <nav aria-label="Treatments pagination">
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

<?php elseif ($action === 'add'): ?>
    <!-- Add Treatment Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus"></i>
                Record New Treatment
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php
                                try {
                                    $stmt = $pdo->prepare("SELECT patient_id, name FROM patient ORDER BY name");
                                    $stmt->execute();
                                    $patients = $stmt->fetchAll();
                                    foreach ($patients as $patient) {
                                        $selected = ($result['data']['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : '';
                                        echo "<option value='{$patient['patient_id']}' $selected>" . htmlspecialchars($patient['name']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Error loading patients</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a patient.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="treatment_date" class="form-label">Treatment Date *</label>
                            <input type="datetime-local" class="form-control" id="treatment_date" name="treatment_date" 
                                   value="<?php echo $result['data']['treatment_date'] ?? date('Y-m-d\TH:i'); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid treatment date.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="treatment_fee" class="form-label">Treatment Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="treatment_fee" name="treatment_fee" 
                                       value="<?php echo $result['data']['treatment_fee'] ?? '0'; ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Treatment Notes *</label>
                    <textarea class="form-control" id="notes" name="notes" rows="5" required><?php echo htmlspecialchars($result['data']['notes'] ?? ''); ?></textarea>
                    <div class="invalid-feedback">
                        Please provide treatment notes.
                    </div>
                </div>
                
                <?php if (!empty($result['errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($result['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between">
                    <a href="treatments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Record Treatment
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'view' && isset($result['treatment'])): ?>
    <!-- Treatment View -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Treatment Details</h5>
                </div>
                <div class="card-body">
                    <h4>Treatment #<?php echo $result['treatment']['treatment_id']; ?></h4>
                    
                    <hr>
                    
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($result['treatment']['patient_name']); ?></p>
                    <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($result['treatment']['patient_phone']); ?>"><?php echo htmlspecialchars($result['treatment']['patient_phone']); ?></a></p>
                    <p><strong>Doctor:</strong> <?php echo htmlspecialchars($result['treatment']['doctor_name']); ?> (<?php echo htmlspecialchars($result['treatment']['specialization']); ?>)</p>
                    <p><strong>Treatment Date:</strong> <?php echo date('M j, Y H:i', strtotime($result['treatment']['treatment_date'])); ?></p>
                    <p><strong>Treatment Fee:</strong> $<?php echo number_format($result['treatment']['treatment_fee'], 2); ?></p>
                    
                    <hr>
                    
                    <h6>Treatment Notes:</h6>
                    <div class="bg-light p-3 rounded">
                        <?php echo nl2br(htmlspecialchars($result['treatment']['notes'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="treatments.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>
