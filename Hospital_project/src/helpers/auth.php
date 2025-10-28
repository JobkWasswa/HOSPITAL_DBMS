<?php
/**
 * Authentication Helper
 * Hospital Management System
 * FIX: Added requireRoles() method to support access for multiple staff roles.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Login user with username and password
     */
    public function login($username, $password) {
        try {
            // FIX: Removed LEFT JOINs for 'staff' and 'doctor' tables.
            // All necessary fields (role, name, specialization) are now in the 'users' table.
            $stmt = $this->pdo->prepare("
                SELECT 
                    `user_id`, 
                    `username`, 
                    `password_hash`, 
                    `role`, 
                    `name`, 
                    `specialization` 
                FROM `users` 
                WHERE `username` = ? AND `is_active` = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // The password_verify check is correct.
            if ($user && password_verify($password, $user['password_hash'])) { 
                // Start session if not already started (best practice, though usually done in index/config)
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // FIX: Use the consolidated 'role' from the users table.
                $_SESSION['role'] = $user['role']; 
                
                // FIX: Store the user's name (pulled from the new 'name' column in 'users')
                $_SESSION['user_name'] = $user['name'] ?? $user['username']; 
                
                // FIX: Store specialization (will be NULL for non-doctors)
                $_SESSION['specialization'] = $user['specialization'] ?? null;
                
                // FIX: Remove deprecated session keys from previous structure
                unset($_SESSION['staff_role']); 
                unset($_SESSION['doctor_name']);
                
                $_SESSION['login_time'] = time();
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Ensure session is started before accessing session variables
        if (session_status() === PHP_SESSION_NONE) {
             session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        // Ensure session is started before accessing session variables
        if (session_status() === PHP_SESSION_NONE) {
             session_start();
        }
        
        // Assuming SESSION_TIMEOUT constant is defined
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['login_time']) && 
               (defined('SESSION_TIMEOUT') ? (time() - $_SESSION['login_time']) < SESSION_TIMEOUT : true);
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        // FIX: Return the consolidated keys
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'name' => $_SESSION['user_name'], // Renamed from 'doctor_name'/'staff_role'
            'specialization' => $_SESSION['specialization'] ?? null
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        return $this->isLoggedIn() && ($_SESSION['role'] ?? null) === $role;
    }
    
    // --- NEW METHOD TO FIX STAFF DASHBOARD ACCESS ---
    /**
     * Check if user has one of the specified roles.
     * @param array $allowedRoles Array of ROLE constants (e.g., [ROLE_NURSE, ROLE_RECEPTIONIST])
     */
    public function hasAnyRole(array $allowedRoles) {
        return $this->isLoggedIn() && in_array($_SESSION['role'] ?? null, $allowedRoles);
    }

    /**
     * Check if user has specific staff role
     * FIX: This method is deprecated; it now calls hasRole().
     */
    public function hasStaffRole($staffRole) {
        // The check now verifies if the primary role matches the staffRole argument.
        return $this->hasRole($staffRole); 
    }
    
    /**
     * Require login - redirect if not logged in
     */
    public function requireLogin($redirectTo = '/public/login.php') {
        if (!$this->isLoggedIn()) {
            // Assuming APP_URL is defined in config/config.php
            header("Location: " . APP_URL . $redirectTo . "?error=access_denied");
            exit();
        }
    }
    
    /**
     * Require specific role
     */
    public function requireRole($role, $redirectTo = '/public/login.php') {
        $this->requireLogin($redirectTo);
        
        if (!$this->hasRole($role)) {
            // If they have a role but not the required one, redirect to their own dashboard
            $user = $this->getCurrentUser();
            if ($user['role'] === ROLE_DOCTOR) {
                 header("Location: " . APP_URL . "/src/views/doctor/dashboard.php");
            } else {
                 header("Location: " . APP_URL . "/src/views/staff/dashboard.php");
            }
            exit();
        }
    }
    
    // --- NEW METHOD TO FIX STAFF DASHBOARD ACCESS ---
    /**
     * Require the current user to have one of the specified roles.
     * Used for pages shared by multiple roles (like the staff dashboard).
     * @param array $allowedRoles Array of ROLE constants (e.g., [ROLE_NURSE, ROLE_RECEPTIONIST])
     */
    public function requireRoles(array $allowedRoles) {
        $this->requireLogin(); // Ensure user is logged in first
        
        if (!$this->hasAnyRole($allowedRoles)) {
            // Logged in but not in the allowed roles, redirect them to their correct dashboard
            $user = $this->getCurrentUser();

            // Handle the redirect based on the role they DO have
            if ($user['role'] === ROLE_DOCTOR) {
                 header("Location: " . APP_URL . "/src/views/doctor/dashboard.php");
            } else {
                 // Fallback for any other unexpected role
                 header("Location: " . APP_URL . "/public/login.php?error=unauthorized_role");
            }
            exit();
        }
    }

    /**
     * Generate password hash
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
          if (session_status() === PHP_SESSION_NONE) {
             session_start();
         }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        // Ensure session is started for security/access
        if (session_status() === PHP_SESSION_NONE) {
             session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>