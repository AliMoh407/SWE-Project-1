# Design Patterns in MediTrack Project

This document outlines the design patterns implemented in the MediTrack application.

## 1. MVC (Model-View-Controller) Architecture Pattern

**Status:** ✅ **Fully Implemented**

This is the primary architectural pattern used throughout the project.

- **Models** (`models/`): Handle data access and business logic
  - `UserModel`, `InventoryModel`, `ActivityLogModel`, `RequestModel`, `NotificationModel`
  - Each model encapsulates database operations for a specific entity

- **Views** (`views/`): Handle presentation layer
  - Organized by feature: `admin/`, `doctor/`, `pharmacist/`, `dashboard/`, `auth/`
  - Views are pure presentation, receiving data from controllers

- **Controllers** (`controllers/`): Handle request processing and orchestration
  - `UserController`, `DashboardController`, `AuthController`, etc.
  - Controllers receive requests, interact with models, and render views

**Example:**
```php
// Route (routes/admin_users.php)
$controller = new UserController($conn, $userModel);
$controller->index();

// Controller (controllers/UserController.php)
public function index(): void {
    $users = $this->userModel->getAll();
    require __DIR__ . '/../views/admin/users.php';
}

// Model (models/User.php)
public function getAll(): array {
    // Database operations
}
```

---

## 2. Dependency Injection Pattern

**Status:** ✅ **Partially Implemented**

Controllers receive their dependencies through constructor injection rather than creating them internally.

**Example:**
```php
// UserController receives dependencies via constructor
class UserController {
    private mysqli $conn;
    private UserModel $userModel;
    
    public function __construct(mysqli $conn, UserModel $userModel) {
        $this->conn = $conn;
        $this->userModel = $userModel;
    }
}

// Dependencies are injected when creating the controller
$controller = new UserController($conn, $userModel);
```

**Note:** Some controllers (like `DashboardController`, `ActivityLogController`) don't receive dependencies and use global variables instead. This could be improved for better dependency injection.

---

## 3. Repository Pattern

**Status:** ✅ **Fully Implemented**

Models act as repositories, encapsulating all database access for specific entities. They provide a clean interface for data operations.

**Characteristics:**
- Each model represents a single entity/table
- Models provide CRUD operations
- Database queries are abstracted away from controllers
- Models return domain objects/arrays

**Example:**
```php
class UserModel {
    public function getAll(): array
    public function findById(int $id): ?array
    public function findByCredentials(string $username, string $password): ?array
    public function create(array $data): bool
    public function update(int $id, array $data): bool
    public function delete(int $id): bool
}
```

---

## 4. Front Controller Pattern

**Status:** ✅ **Fully Implemented**

All requests go through entry point files in the `routes/` folder, which act as front controllers that delegate to specific controllers.

**Flow:**
1. Request arrives at route file (e.g., `routes/admin_users.php`)
2. Route file instantiates appropriate controller
3. Controller handles the request and renders view

**Example:**
```php
// routes/admin_users.php (Front Controller)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/UserController.php';

$controller = new UserController($conn, $userModel);
$controller->index();
```

---

## 5. Template Method Pattern

**Status:** ✅ **Partially Implemented**

Views follow a consistent template structure:
- All views include `header.php` at the start
- All views include `footer.php` at the end
- Navigation is included conditionally
- Consistent layout structure

**Example:**
```php
// views/admin/users.php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
// ... view content ...
include __DIR__ . '/../../includes/footer.php';
```

---

## 6. Service Layer Pattern

**Status:** ✅ **Fully Implemented**

Controllers act as a service layer between routes and models, containing business logic and orchestration.

**Responsibilities:**
- Validate requests
- Coordinate between multiple models
- Handle business rules
- Prepare data for views
- Manage session state

**Example:**
```php
class DoctorRequestController {
    public function create(): void {
        // Business logic: validate, check permissions, create request
        if ($item['controlled']) {
            $requestData['status'] = 'Pending';
            $requestData['priority'] = 'high';
        }
        // Orchestrate: create request, log activity
        $requestModel->create($requestData);
        $activityLogModel->create(...);
    }
}
```

---

## 7. Data Access Object (DAO) Pattern

**Status:** ✅ **Fully Implemented**

Models implement the DAO pattern by providing a data access interface that abstracts database operations.

**Characteristics:**
- Models encapsulate SQL queries
- Controllers don't know about database structure
- Easy to change database implementation
- Prepared statements for security

---

## 8. Singleton Pattern

**Status:** ✅ **Fully Implemented**

The `Database` class ensures only one database connection exists throughout the application lifecycle.

**Implementation:**
- Located in `core/Database.php`
- Private constructor prevents direct instantiation
- `getInstance()` method returns the singleton instance
- Connection is established once and reused

**Example:**
```php
// Get database instance (singleton)
$db = Database::getInstance();
$conn = $db->getConnection();
```

---

## 9. Factory Pattern

**Status:** ✅ **Fully Implemented**

The `ControllerFactory` centralizes controller creation and handles dependency injection automatically.

**Implementation:**
- Located in `core/ControllerFactory.php`
- Maps route names to controller classes
- Automatically injects dependencies (models, connections)
- Singleton pattern ensures single factory instance

**Example:**
```php
// Route file (routes/admin_users.php)
$factory = ControllerFactory::getInstance();
$controller = $factory->create('admin_users');
$controller->index();
```

**Benefits:**
- Centralized controller creation
- Automatic dependency injection
- Easy to add new controllers
- Consistent initialization

---

## 10. Strategy Pattern

**Status:** ✅ **Fully Implemented**

Role-based access control uses the Strategy pattern to encapsulate role-specific behaviors.

**Implementation:**
- Located in `core/RoleStrategy.php`
- `RoleStrategy` interface defines contract
- Concrete strategies: `AdminRoleStrategy`, `DoctorRoleStrategy`, `PharmacistRoleStrategy`
- `RoleStrategyFactory` creates appropriate strategy based on role

**Example:**
```php
// Get strategy for current user
$strategy = RoleStrategyFactory::createForCurrentUser();
if ($strategy && $strategy->canAccess('manage_inventory')) {
    // Allow access
}
```

**Benefits:**
- Encapsulates role-specific logic
- Easy to add new roles
- Centralized access control
- Testable and maintainable

---

## 11. Observer Pattern

**Status:** ✅ **Fully Implemented**

Activity logging is handled automatically through the Observer pattern, decoupling logging from business logic.

**Implementation:**
- Located in `core/Observer.php`
- `EventNotifier` (Subject) manages observers
- `ActivityLogObserver` automatically logs events
- Controllers notify events instead of directly logging

**Example:**
```php
// In controller (InventoryController)
$this->eventNotifier->notify('inventory.add', [
    'name' => $item['name'],
    'status' => 'Completed'
]);
// ActivityLogObserver automatically logs this
```

**Benefits:**
- Decouples logging from business logic
- Easy to add new observers (e.g., email notifications, audit trails)
- Automatic activity logging
- Consistent event handling

---

## Patterns NOT Currently Implemented (But Could Be Added)

### 5. Facade Pattern
- **Current:** Controllers directly interact with multiple models
- **Could improve:** Create facades to simplify complex operations

### 6. Adapter Pattern
- **Current:** Direct MySQLi usage
- **Could improve:** Use adapter pattern to support multiple database types

---

## Summary

**Currently Implemented Patterns:**
1. ✅ MVC Architecture
2. ✅ Dependency Injection (improved)
3. ✅ Repository Pattern
4. ✅ Front Controller Pattern
5. ✅ Template Method Pattern
6. ✅ Service Layer Pattern
7. ✅ DAO Pattern
8. ✅ Singleton Pattern
9. ✅ Factory Pattern
10. ✅ Strategy Pattern
11. ✅ Observer Pattern

**Architecture Quality:**
- **Separation of Concerns:** ✅ Excellent
- **Single Responsibility:** ✅ Good
- **Dependency Management:** ⚠️ Could be improved (some global variables)
- **Testability:** ⚠️ Moderate (global dependencies make testing harder)
- **Maintainability:** ✅ Good
- **Scalability:** ✅ Good structure for growth

---

## Recommendations for Improvement

1. **Complete Dependency Injection:** Remove remaining global variables, inject all dependencies
2. **Add Service Layer:** Create service classes to handle complex business logic
3. **Add Interface Abstractions:** Create interfaces for models to improve testability
4. **Add Error Handling Strategy:** Consistent error handling pattern
5. **Implement Facade Pattern:** Simplify complex operations with facades
6. **Add Unit Tests:** Test individual components with dependency injection

