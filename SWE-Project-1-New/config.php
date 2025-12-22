<?php
session_start();

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_PHARMACIST', 'pharmacist');

// ===== Load Core Classes =====
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ControllerFactory.php';
require_once __DIR__ . '/core/RoleStrategy.php';
require_once __DIR__ . '/core/Observer.php';

// ===== Database connection using Singleton Pattern =====
$db = Database::getInstance();
$conn = $db->getConnection();

// ===== Models (OOP layer) =====
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Inventory.php';
require_once __DIR__ . '/models/ActivityLog.php';
require_once __DIR__ . '/models/Request.php';
require_once __DIR__ . '/models/Notification.php';

// ===== Get Model Instances from ControllerFactory =====
$factory = ControllerFactory::getInstance();
$userModel = $factory->getUserModel();
$inventoryModel = $factory->getInventoryModel();
$activityLogModel = $factory->getActivityLogModel();
$requestModel = $factory->getRequestModel();
$notificationModel = $factory->getNotificationModel();

// ===== Initialize Observer Pattern for Activity Logging =====
$eventNotifier = EventNotifier::getInstance();
$activityLogObserver = new ActivityLogObserver($activityLogModel);
$eventNotifier->attach($activityLogObserver);

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
    header('Location: ' . getBaseUrl() . 'login.php');
    exit();
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirectToLogin();
    }
}

function requireRole($role) {
    requireLogin();
    
    // Use Strategy Pattern for role-based access
    $strategy = RoleStrategyFactory::createForCurrentUser();
    if ($strategy && $strategy->getRoleName() !== $role) {
        header('Location: ' . getBaseUrl() . 'routes/dashboard.php');
        exit();
    }
    
    // Fallback to old method if strategy is not available
    if (!hasRole($role)) {
        header('Location: ' . getBaseUrl() . 'routes/dashboard.php');
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
    
    // Get the directory of the script
    $scriptDir = dirname($script);
    
    // Remove 'routes' or any subdirectory from the path to get project root
    // Example: /SWE Project 1/routes/dashboard.php -> /SWE Project 1/
    $path = $scriptDir;
    
    // If path contains '/routes', remove it to get back to project root
    if (strpos($path, '/routes') !== false) {
        $path = str_replace('/routes', '', $path);
    }
    
    // Ensure path ends with /
    if ($path !== '/' && substr($path, -1) !== '/') {
        $path .= '/';
    }
    
    return $protocol . '://' . $host . $path;
}
?>
