<?php
/**
 * Billing Management - Staff View (Accountant)
 * Hospital Management System
 * * FIX: Updated to use consolidated 'users' table and 'ROLE_ACCOUNTANT' constant.
 */

require_once '../../../config/config.php';
require_once '../../../src/helpers/Auth.php';
// Assuming you still have a Payment model/class available
require_once '../../../src/models/Payment.php'; 
// Constants for roles and statuses are loaded via config/config.php -> config/constants.php

// Initialize authentication
$auth = new Auth($pdo);

// 1. FIX: Use ROLE_ACCOUNTANT for access check instead of deprecated ROLE_STAFF
// This enforces that only the accountant can see the billing page.
$auth->requireRole(ROLE_ACCOUNTANT);

$currentUser = $auth->getCurrentUser();

// 2. FIX: The staff role is now directly available from $currentUser['role'] 
// (which is 'accountant' since requireRole passed)
$staffRole = $currentUser['role'] ?? null; 

// 3. FIX: Check for the required permission role using the constant
// Since this page is specifically for the accountant, the permission role is ROLE_ACCOUNTANT.
$REQUIRED_PERMISSION_ROLE = ROLE_ACCOUNTANT; 

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'view':
        if ($id) {
            try {
                // Query remains valid as it uses payment and patient tables
                $stmt = $pdo->prepare("
                    SELECT p.*, pt.name as patient_name, pt.phone as patient_phone
                    FROM payment p
                    JOIN patient pt ON p.patient_id = pt.patient_id
                    WHERE p.bill_id = ?
                ");
                $stmt->execute([$id]);
                $payment = $stmt->fetch();
                
                if (!$payment) {
                    $result = ['error' => 'Payment record not found'];
                } else {
                    $result = ['payment' => $payment];
                }
            } catch (PDOException $e) {
                $result = ['error' => 'Database error occurred'];
            }
        } else {
            $result = ['error' => 'Payment ID required'];
        }
        break;
        
    case 'add':
        // 4. FIX: Use the new ROLE_ACCOUNTANT constant for permission check
        if ($staffRole === $REQUIRED_PERMISSION_ROLE) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'payment_method' => $_POST['payment_method'] ?? '',
                    // Note: PAYMENT_PENDING should be defined in constants.php
                    'payment_status' => $_POST['payment_status'] ?? PAYMENT_PENDING,
                    'payment_date' => $_POST['payment_date'] ?? date('Y-m-d H:i:s'),
                    'patient_id' => intval($_POST['patient_id'] ?? 0)
                ];
                
                $errors = [];
                if (empty($data['payment_method'])) $errors[] = 'Payment method is required';
                if (empty($data['patient_id'])) $errors[] = 'Patient is required';
                
                if (empty($errors)) {
                    try {
                        // Auto-calculate total amount from related costs (can be zero)
                        $paymentModel = new Payment($pdo);
                        $computedTotal = $paymentModel->calculatePatientBill($data['patient_id']);

                        $stmt = $pdo->prepare("INSERT INTO payment (total_amount, payment_method, payment_status, payment_date, patient_id) VALUES (?, ?, ?, ?, ?)");
                        if ($stmt->execute([$computedTotal, $data['payment_method'], $data['payment_status'], $data['payment_date'], $data['patient_id']])) {
                            header('Location: billing.php?success=' . urlencode('Payment record created successfully'));
                            exit();
                        } else {
                            $result = ['error' => 'Failed to create payment record'];
                        }
                    } catch (PDOException $e) {
                        $result = ['error' => 'Database error occurred: ' . $e->getMessage()];
                    }
                } else {
                    $result = ['errors' => $errors, 'data' => $data];
                }
            }
        } else {
            $result = ['error' => 'Access denied. Only accountants can create payment records.'];
        }
        break;

    case 'delete':
        // 5. FIX: Use the new ROLE_ACCOUNTANT constant for permission check
        if ($staffRole === $REQUIRED_PERMISSION_ROLE && $id) {
            try {
                $paymentModel = new Payment($pdo);
                if ($paymentModel->delete($id)) {
                    header('Location: billing.php?success=' . urlencode('Payment record deleted successfully'));
                    exit();
                } else {
                    $result = ['error' => 'Failed to delete payment record'];
                }
            } catch (PDOException $e) {
                $result = ['error' => 'Database error occurred: ' . $e->getMessage()];
            }
        } else {
            $result = ['error' => 'Access denied. Only accountants can delete payment records.'];
        }
        break;
        
    case 'update_status':
        // 6. FIX: Use the new ROLE_ACCOUNTANT constant for permission check
        if ($staffRole === $REQUIRED_PERMISSION_ROLE && $id) {
            $newStatus = $_POST['status'] ?? '';
            // Note: PAYMENT_* constants should be defined in constants.php
            if (in_array($newStatus, [PAYMENT_PENDING, PAYMENT_PAID, PAYMENT_DECLINED])) {
                try {
                    $stmt = $pdo->prepare("UPDATE payment SET payment_status = ? WHERE bill_id = ?");
                    if ($stmt->execute([$newStatus, $id])) {
                        header('Location: billing.php?success=' . urlencode('Payment status updated successfully'));
                        exit();
                    } else {
                        $result = ['error' => 'Failed to update payment status'];
                    }
                } catch (PDOException $e) {
                    $result = ['error' => 'Database error occurred: ' . $e->getMessage()];
                }
            } else {
                $result = ['error' => 'Invalid payment status'];
            }
        } else {
            $result = ['error' => 'Access denied. Only accountants can update payment status.'];
        }
        break;
        
    default:
        // Pagination logic (no role changes needed here)
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        // Note: RECORDS_PER_PAGE should be defined in config/constants.php
        $limit = RECORDS_PER_PAGE ?? 20; 
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        
        try {
            // SQL queries remain the same as they only join payment and patient
            $sql = "
                SELECT p.*, pt.name as patient_name, pt.phone as patient_phone
                FROM payment p
                JOIN patient pt ON p.patient_id = pt.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND pt.name LIKE ?";
                $params[] = "%$search%";
            }
            
            if (!empty($status)) {
                $sql .= " AND p.payment_status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY p.payment_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll();
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) as count
                FROM payment p
                JOIN patient pt ON p.patient_id = pt.patient_id
                WHERE 1=1
            ";
            $countParams = [];
            
            if (!empty($search)) {
                $countSql .= " AND pt.name LIKE ?";
                $countParams[] = "%$search%";
            }
            
            if (!empty($status)) {
                $countSql .= " AND p.payment_status = ?";
                $countParams[] = $status;
            }
            
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $totalCount = $stmt->fetch()['count'];
            $totalPages = ceil($totalCount / $limit);
            
            $result = [
                'payments' => $payments,
                'totalPayments' => $totalCount,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'search' => $search,
                'status' => $status
            ];
        } catch (PDOException $e) {
            $result = ['error' => 'Database error occurred'];
        }
        break;
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $result['error'] ?? '';

$pageTitle = 'Billing Management';
include '../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-credit-card"></i>
        Billing Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($staffRole === ROLE_ACCOUNTANT): ?>
            <div class="btn-group me-2">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create Payment Record
                </a>
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
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by patient name..." 
                               value="<?php echo htmlspecialchars($result['search']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="<?php echo PAYMENT_PENDING; ?>" <?php echo $result['status'] === PAYMENT_PENDING ? 'selected' : ''; ?>>Pending</option>
                        <option value="<?php echo PAYMENT_PAID; ?>" <?php echo $result['status'] === PAYMENT_PAID ? 'selected' : ''; ?>>Paid</option>
                        <option value="<?php echo PAYMENT_DECLINED; ?>" <?php echo $result['status'] === PAYMENT_DECLINED ? 'selected' : ''; ?>>Declined</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="billing.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Payment Records
                <span class="badge bg-primary ms-2"><?php echo $result['totalPayments']; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($result['payments'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No payment records found</h4>
                    <p class="text-muted">
                        <?php if ($result['search'] || $result['status']): ?>
                            No payment records match your search criteria.
                        <?php else: ?>
                            No payment records have been created yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!$result['search'] && !$result['status'] && $staffRole === ROLE_ACCOUNTANT): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create First Payment Record
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
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['payments'] as $payment): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo $payment['bill_id']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['patient_name']); ?></strong>
                                    </td>
                                    <td>
                                        $<?php echo number_format($payment['total_amount'], 2); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($payment['payment_method']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Note: Assuming PAYEMENT_* constants are correctly defined.
                                        $statusClass = match($payment['payment_status']) {
                                            PAYMENT_PENDING => 'bg-warning',
                                            PAYMENT_PAID => 'bg-success',
                                            PAYMENT_DECLINED => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($payment['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y H:i', strtotime($payment['payment_date'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=view&id=<?php echo $payment['bill_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($staffRole === ROLE_ACCOUNTANT): ?>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $payment['bill_id']; ?>, '<?php echo PAYMENT_PAID; ?>')">Mark as Paid</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $payment['bill_id']; ?>, '<?php echo PAYMENT_DECLINED; ?>')">Mark as Declined</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $payment['bill_id']; ?>, '<?php echo PAYMENT_PENDING; ?>')">Mark as Pending</a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $payment['bill_id']; ?>)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($result['totalPages'] > 1): ?>
                    <nav aria-label="Payments pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($result['currentPage'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&status=<?php echo urlencode($result['status']); ?>&page=<?php echo $result['currentPage'] - 1; ?>">
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
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&status=<?php echo urlencode($result['status']); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($result['currentPage'] < $result['totalPages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($result['search']); ?>&status=<?php echo urlencode($result['status']); ?>&page=<?php echo $result['currentPage'] + 1; ?>">
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
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus"></i>
                Create Payment Record
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
                                // Fetch patient list
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
                            <label class="form-label">Amount</label>
                            <div class="form-text">
                                Total is calculated automatically from treatments, lab tests, prescriptions, and admissions.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="<?php echo PAYMENT_CASH; ?>" <?php echo ($result['data']['payment_method'] ?? '') === PAYMENT_CASH ? 'selected' : ''; ?>>Cash</option>
                                <option value="<?php echo PAYMENT_CARD; ?>" <?php echo ($result['data']['payment_method'] ?? '') === PAYMENT_CARD ? 'selected' : ''; ?>>Card</option>
                                <option value="<?php echo PAYMENT_INSURANCE; ?>" <?php echo ($result['data']['payment_method'] ?? '') === PAYMENT_INSURANCE ? 'selected' : ''; ?>>Insurance</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a payment method.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="<?php echo PAYMENT_PENDING; ?>" <?php echo ($result['data']['payment_status'] ?? PAYMENT_PENDING) === PAYMENT_PENDING ? 'selected' : ''; ?>>Pending</option>
                                <option value="<?php echo PAYMENT_PAID; ?>" <?php echo ($result['data']['payment_status'] ?? '') === PAYMENT_PAID ? 'selected' : ''; ?>>Paid</option>
                                <option value="<?php echo PAYMENT_DECLINED; ?>" <?php echo ($result['data']['payment_status'] ?? '') === PAYMENT_DECLINED ? 'selected' : ''; ?>>Declined</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="datetime-local" class="form-control" id="payment_date" name="payment_date" 
                                       value="<?php echo $result['data']['payment_date'] ?? date('Y-m-d\TH:i'); ?>">
                        </div>
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
                    <a href="billing.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Create Payment Record
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'view' && isset($result['payment'])): ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <h4>Payment #<?php echo $result['payment']['bill_id']; ?></h4>
                    
                    <hr>
                    
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($result['payment']['patient_name']); ?></p>
                    <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($result['payment']['patient_phone']); ?>"><?php echo htmlspecialchars($result['payment']['patient_phone']); ?></a></p>
                    <p><strong>Amount:</strong> $<?php echo number_format($result['payment']['total_amount'], 2); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($result['payment']['payment_method']); ?></p>
                    <p><strong>Status:</strong> 
                        <?php
                        $statusClass = match($result['payment']['payment_status']) {
                            PAYMENT_PENDING => 'bg-warning',
                            PAYMENT_PAID => 'bg-success',
                            PAYMENT_DECLINED => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($result['payment']['payment_status']); ?>
                        </span>
                    </p>
                    <p><strong>Payment Date:</strong> <?php echo date('M j, Y H:i', strtotime($result['payment']['payment_date'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="billing.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php endif; ?>

<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="status" id="statusInput">
</form>

<script>
function updateStatus(paymentId, status) {
    if (confirm('Are you sure you want to update the payment status to ' + status + '?')) {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusForm').action = '?action=update_status&id=' + paymentId;
        document.getElementById('statusForm').submit();
    }
}

function confirmDelete(paymentId) {
    if (confirm('Are you sure you want to delete this payment record? This action cannot be undone.')) {
        window.location.href = 'billing.php?action=delete&id=' + paymentId;
    }
}
</script>

<?php include '../layouts/footer.php'; ?>