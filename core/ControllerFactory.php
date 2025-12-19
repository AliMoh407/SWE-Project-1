<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Observer.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Request.php';
require_once __DIR__ . '/../models/Notification.php';

// Load all controllers
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/ActivityLogController.php';
require_once __DIR__ . '/../controllers/ReportController.php';
require_once __DIR__ . '/../controllers/DoctorRequestController.php';
require_once __DIR__ . '/../controllers/DoctorHistoryController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../controllers/PharmacistRequestController.php';

/**
 * Controller Factory Pattern
 * Centralizes controller creation and dependency injection
 */
class ControllerFactory
{
    private mysqli $conn;
    private UserModel $userModel;
    private InventoryModel $inventoryModel;
    private ActivityLogModel $activityLogModel;
    private RequestModel $requestModel;
    private NotificationModel $notificationModel;
    
    private static ?ControllerFactory $instance = null;
    
    /**
     * Private constructor - use getInstance() instead
     */
    private function __construct()
    {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
        
        // Initialize all models
        $this->userModel = new UserModel($this->conn);
        $this->inventoryModel = new InventoryModel($this->conn);
        $this->activityLogModel = new ActivityLogModel($this->conn);
        $this->requestModel = new RequestModel($this->conn);
        $this->notificationModel = new NotificationModel($this->conn);
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): ControllerFactory
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Create a controller based on controller name
     */
    public function create(string $controllerName): object
    {
        $controllerClass = $this->getControllerClass($controllerName);
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class {$controllerClass} not found");
        }
        
        return match($controllerClass) {
            'AuthController' => new AuthController($this->userModel),
            'DashboardController' => new DashboardController(),
            'UserController' => new UserController($this->conn, $this->userModel),
            'InventoryController' => new InventoryController($this->inventoryModel, $this->activityLogModel),
            'ActivityLogController' => new ActivityLogController(),
            'ReportController' => new ReportController(),
            'DoctorRequestController' => new DoctorRequestController(
                $this->inventoryModel,
                $this->requestModel,
                $this->activityLogModel
            ),
            'DoctorHistoryController' => new DoctorHistoryController(),
            'NotificationController' => new NotificationController(),
            'PharmacistRequestController' => new PharmacistRequestController(
                $this->requestModel,
                $this->inventoryModel,
                EventNotifier::getInstance()
            ),
            default => throw new Exception("Unknown controller: {$controllerClass}")
        };
    }
    
    /**
     * Get controller class name from route or controller name
     */
    private function getControllerClass(string $name): string
    {
        // Handle different input formats
        // "admin_users" -> "UserController"
        // "UserController" -> "UserController"
        // "user" -> "UserController"
        
        $name = strtolower($name);
        
        // Map route names to controller classes
        $routeMap = [
            'admin_users' => 'UserController',
            'admin_activity_logs' => 'ActivityLogController',
            'admin_reports' => 'ReportController',
            'dashboard' => 'DashboardController',
            'doctor_requests' => 'DoctorRequestController',
            'doctor_request_history' => 'DoctorHistoryController',
            'pharmacist_inventory' => 'InventoryController',
            'pharmacist_requests' => 'PharmacistRequestController',
            'pharmacist_notifications' => 'NotificationController',
            'login' => 'AuthController',
        ];
        
        if (isset($routeMap[$name])) {
            return $routeMap[$name];
        }
        
        // If it already ends with "Controller", return as is
        if (substr($name, -10) === 'controller') {
            return ucfirst($name);
        }
        
        // Otherwise, convert to Controller format
        // "user" -> "UserController"
        $name = ucfirst($name);
        if (substr($name, -10) !== 'Controller') {
            $name .= 'Controller';
        }
        
        return $name;
    }
    
    /**
     * Get model instances (for backward compatibility)
     */
    public function getUserModel(): UserModel
    {
        return $this->userModel;
    }
    
    public function getInventoryModel(): InventoryModel
    {
        return $this->inventoryModel;
    }
    
    public function getActivityLogModel(): ActivityLogModel
    {
        return $this->activityLogModel;
    }
    
    public function getRequestModel(): RequestModel
    {
        return $this->requestModel;
    }
    
    public function getNotificationModel(): NotificationModel
    {
        return $this->notificationModel;
    }
    
    public function getConnection(): mysqli
    {
        return $this->conn;
    }
}

