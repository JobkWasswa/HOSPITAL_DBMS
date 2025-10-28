<?php
/**
 * System Constants
 * Hospital Management System
 * * MODIFIED to match the new consolidated 'users' table roles.
 */

// =================================================================
// 1. User roles (Must match ENUM in users.role column)
// =================================================================

// Primary Roles
define('ROLE_DOCTOR', 'doctor');

define('ROLE_NURSE', 'nurse'); 
define('ROLE_RECEPTIONIST', 'receptionist');
define('ROLE_ACCOUNTANT', 'accountant');

// DEPRECATED: ROLE_STAFF is removed as all staff roles are now primary.
// define('ROLE_STAFF', 'staff'); 


// =================================================================
// 2. Deprecated Bed/Staff Sub-Roles (REMOVED)
// =================================================================
/* // Staff sub-roles - DEPRECATED/REMOVED
define('STAFF_NURSE', 'Nurse');
define('STAFF_RECEPTIONIST', 'Receptionist');
define('STAFF_ACCOUNTANT', 'Accountant');
define('STAFF_CLEANER', 'Cleaner');

// Bed types - DEPRECATED/REMOVED (Bed is consolidated/removed)
define('BED_STANDARD', 'Standard');
define('BED_ICU', 'ICU');
define('BED_EMERGENCY', 'Emergency');

// Bed status - DEPRECATED/REMOVED
define('BED_AVAILABLE', 'Available');
define('BED_OCCUPIED', 'Occupied');
define('BED_MAINTENANCE', 'Maintenance');
*/


// =================================================================
// 3. Other Constants (Kept as they appear valid for other tables)
// =================================================================

// Appointment status
define('APPOINTMENT_SCHEDULED', 'Scheduled');
define('APPOINTMENT_COMPLETED', 'Completed');
define('APPOINTMENT_CANCELLED', 'Cancelled');
define('APPOINTMENT_NO_SHOW', 'No show');

// Payment status
define('PAYMENT_PENDING', 'Pending');
define('PAYMENT_PAID', 'Paid');
define('PAYMENT_DECLINED', 'Declined');

// Payment methods
define('PAYMENT_CASH', 'Cash');
define('PAYMENT_CARD', 'Card');
define('PAYMENT_INSURANCE', 'Insurance');

// Room types
define('ROOM_GENERAL', 'General');
define('ROOM_PRIVATE', 'Private');
define('ROOM_ICU', 'ICU');

// Room status
define('ROOM_AVAILABLE', 'Available');
define('ROOM_OCCUPIED', 'Occupied');
define('ROOM_MAINTENANCE', 'Maintenance');

// Gender options
define('GENDER_MALE', 'Male');
define('GENDER_FEMALE', 'Female');
define('GENDER_OTHER', 'Other');

// Shift types (Match ENUM in users.shift column)
define('SHIFT_MORNING', 'Morning');
define('SHIFT_EVENING', 'Evening');
define('SHIFT_NIGHT', 'Night');

// HTTP status codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_ERROR', 500);

// Success/Error messages
define('MSG_LOGIN_SUCCESS', 'Login successful');
define('MSG_LOGIN_FAILED', 'Invalid username or password');
define('MSG_LOGOUT_SUCCESS', 'Logged out successfully');
define('MSG_ACCESS_DENIED', 'Access denied');
define('MSG_RECORD_SAVED', 'Record saved successfully');
define('MSG_RECORD_UPDATED', 'Record updated successfully');
define('MSG_RECORD_DELETED', 'Record deleted successfully');
define('MSG_RECORD_NOT_FOUND', 'Record not found');
define('MSG_VALIDATION_ERROR', 'Please check your input');
define('MSG_DATABASE_ERROR', 'Database error occurred');
?>