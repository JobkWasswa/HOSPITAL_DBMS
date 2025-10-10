<?php
/**
 * General Configuration
 * Hospital Management System
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'Hospital Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Hospital_project');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Pagination settings
define('RECORDS_PER_PAGE', 20);
define('MAX_PAGINATION_LINKS', 10);

// Date and time settings
date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/database.php';
?>
