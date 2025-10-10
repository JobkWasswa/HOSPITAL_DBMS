<?php
/**
 * Authentication Helper
 * Hospital Management System
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
            $stmt = $this->pdo->prepare("
                SELECT u.*, s.role as staff_role, d.name as doctor_name, d.specialization
                FROM users u
                LEFT JOIN staff s ON u.staff_id = s.staff_id
                LEFT JOIN doctor d ON u.doctor_id = d.doctor_id
                WHERE u.username = ? AND u.is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // FIX APPLIED HERE: Changed $user['password'] to $user['password_hash']
            // This fixes the Warning: Undefined array key "password" and the Deprecated notice.
            if ($user && password_verify($password, $user['password_hash'])) { 
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['staff_role'] = $user['staff_role'] ?? null;
                $_SESSION['doctor_name'] = $user['doctor_name'] ?? null;
                $_SESSION['specialization'] = $user['specialization'] ?? null;
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
    // ...existing code...
    public function logout() {
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
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['login_time']) && 
               (time() - $_SESSION['login_time']) < SESSION_TIMEOUT;
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'staff_role' => $_SESSION['staff_role'] ?? null,
            'doctor_name' => $_SESSION['doctor_name'] ?? null,
            'specialization' => $_SESSION['specialization'] ?? null
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['role'] === $role;
    }
    
    /**
     * Check if user has specific staff role
     */
    public function hasStaffRole($staffRole) {
        return $this->isLoggedIn() && 
               $_SESSION['role'] === ROLE_STAFF && 
               $_SESSION['staff_role'] === $staffRole;
    }
    
    /**
     * Require login - redirect if not logged in
     */
    public function requireLogin($redirectTo = '/public/login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: " . APP_URL . $redirectTo);
            exit();
        }
    }
    
    /**
     * Require specific role
     */
    public function requireRole($role, $redirectTo = '/public/login.php') {
        $this->requireLogin($redirectTo);
        if (!$this->hasRole($role)) {
            header("Location: " . APP_URL . $redirectTo . "?error=access_denied");
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
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>