<?php
session_start();

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_PHARMACIST', 'pharmacist');

// ===== Database connection (MySQL / phpMyAdmin) =====
// Change these values to match your database in phpMyAdmin
$DB_HOST = 'localhost';      // usually 'localhost' on XAMPP
$DB_USER = 'root';           // default XAMPP user
$DB_PASS = '';               // default XAMPP password is empty
$DB_NAME = 'meditrack';      // TODO: change to your database name

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// ===== Models (OOP layer) =====
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Inventory.php';
require_once __DIR__ . '/models/ActivityLog.php';
require_once __DIR__ . '/models/Request.php';
require_once __DIR__ . '/models/Notification.php';

// Global model instances
$userModel = new UserModel($conn);
$inventoryModel = new InventoryModel($conn);
$activityLogModel = new ActivityLogModel($conn);
$requestModel = new RequestModel($conn);
$notificationModel = new NotificationModel($conn);

// Keep legacy arrays for backward compatibility, but now filled via models
// Wrap in try-catch to handle cases where tables don't exist yet
try {
    $users = $userModel->getAll();
    $inventory = $inventoryModel->getAll();
    $activity_logs = $activityLogModel->getAll();
} catch (Exception $e) {
    // If tables don't exist yet, use empty arrays
    $users = [];
    $inventory = [];
    $activity_logs = [];
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $users;
    if (isLoggedIn()) {
        foreach ($users as $user) {
            if ($user['id'] == $_SESSION['user_id']) {
                return $user;
            }
        }
    }
    return null;
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

function redirectToLogin() {
    header('Location: login.php');
    exit();
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirectToLogin();
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit();
    }
}

function getInventoryItem($id) {
    global $inventoryModel;
    return $inventoryModel->findById((int)$id);
}

function isLowStock($item) {
    return $item['stock'] <= $item['min_stock'];
}

function isExpiringSoon($item, $days = 30) {
    $expiry = new DateTime($item['expiry_date']);
    $today = new DateTime();
    $diff = $today->diff($expiry);
    return $diff->days <= $days;
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    
    // If we're in a subdirectory, we need to go up one level
    if (strpos($script, '/admin/') !== false || 
        strpos($script, '/doctor/') !== false || 
        strpos($script, '/pharmacist/') !== false) {
        $path = dirname($path);
    }
    
    // Ensure path ends with /
    if ($path !== '/' && substr($path, -1) !== '/') {
        $path .= '/';
    }
    
    return $protocol . '://' . $host . $path;
}
?>
