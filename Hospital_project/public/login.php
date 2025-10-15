<?php
/**
 * Login Page
 * Hospital Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/helpers/Auth.php';

// Initialize authentication
$auth = new Auth($pdo);

$error = '';
$success = '';

// =================================================================
// FIX START: Handle Logout BEFORE checking login status
// =================================================================
// Around Line 15
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // 1. Perform the logout
    $auth->logout();
    
    // 2. Redirect to the clean login page URL.
    // Try redirecting directly to /login.php without /public/,
    // or try using a full relative path (less robust).
    
    // MOST ROBUST FIX (Use /public/ only if APP_URL doesn't point there)
    // Assuming APP_URL = http://localhost/Hospital_project
    header("Location: " . APP_URL . "/public/login.php?message=logged_out");
    exit();
}

// Handle success message after a redirect (e.g., after logout)
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success = 'You have been logged out successfully.';
}
// =================================================================
// FIX END
// =================================================================


// Check if user is already logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    
    // Redirect based on role
    if ($user['role'] === ROLE_DOCTOR) {
        header("Location: " . APP_URL . "/src/views/doctor/dashboard.php");
    } else {
        header("Location: " . APP_URL . "/src/views/staff/dashboard.php");
    }
    exit();
}


// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if ($auth->login($username, $password)) {
            $user = $auth->getCurrentUser();
            
            // Redirect based on role
            if ($user['role'] === ROLE_DOCTOR) {
                header("Location: " . APP_URL . "/src/views/doctor/dashboard.php");
            } else {
                header("Location: " . APP_URL . "/src/views/staff/dashboard.php");
            }
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}


// Handle access denied
if (isset($_GET['error']) && $_GET['error'] === 'access_denied') {
    $error = 'Access denied. Please log in.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-hospital"></i>
                <h2><?php echo APP_NAME; ?></h2>
                <p>Please sign in to continue</p>
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
            
            <form method="POST" class="login-form">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <small class="text-muted">
                    &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>