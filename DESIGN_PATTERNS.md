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

## Patterns NOT Currently Implemented (But Could Be Added)

### 1. Singleton Pattern
- **Current:** Database connection is created once in `config.php` but not as a true singleton
- **Could improve:** Create a `Database` singleton class to ensure only one connection exists

### 2. Factory Pattern
- **Current:** Controllers are instantiated directly in route files
- **Could improve:** Use a `ControllerFactory` to create controllers based on route

### 3. Strategy Pattern
- **Current:** Role-based access is handled with if/else statements
- **Could improve:** Use strategy pattern for different role behaviors

### 4. Observer Pattern
- **Current:** Activity logging is done manually in controllers
- **Could improve:** Use observer pattern to automatically log all actions

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
2. ✅ Dependency Injection (partial)
3. ✅ Repository Pattern
4. ✅ Front Controller Pattern
5. ✅ Template Method Pattern
6. ✅ Service Layer Pattern
7. ✅ DAO Pattern

**Architecture Quality:**
- **Separation of Concerns:** ✅ Excellent
- **Single Responsibility:** ✅ Good
- **Dependency Management:** ⚠️ Could be improved (some global variables)
- **Testability:** ⚠️ Moderate (global dependencies make testing harder)
- **Maintainability:** ✅ Good
- **Scalability:** ✅ Good structure for growth

---

## Recommendations for Improvement

1. **Complete Dependency Injection:** Remove global variables, inject all dependencies
2. **Add Service Layer:** Create service classes to handle complex business logic
3. **Implement Factory Pattern:** For controller creation
4. **Add Interface Abstractions:** Create interfaces for models to improve testability
5. **Implement Singleton for Database:** Ensure single connection instance
6. **Add Error Handling Strategy:** Consistent error handling pattern

