<?php
/**
 * Main Entry Point
 * Hospital Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/helpers/Auth.php';

// Initialize authentication
$auth = new Auth($pdo);

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

// If not logged in, redirect to landing page
header("Location: " . APP_URL . "/public/landing.php");
exit();
?>
