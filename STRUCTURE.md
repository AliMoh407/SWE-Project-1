# Project Structure

This project follows a clean MVC (Model-View-Controller) architecture.

## Root Level Files
- `config.php` - Database configuration and global settings
- `login.php` - Login entry point
- `logout.php` - Logout handler
- `dashboard.php` - Dashboard entry point

## Entry Points (Root Level)
- `admin_users.php` - User management
- `admin_activity_logs.php` - Activity logs
- `admin_reports.php` - System reports
- `doctor_requests.php` - Create item requests
- `doctor_request_history.php` - View request history
- `pharmacist_inventory.php` - Inventory management
- `pharmacist_notifications.php` - Notifications

## Folder Structure

```
/
├── assets/              # Static assets (CSS, JS, images)
│   ├── css/
│   └── js/
├── config.php           # Configuration file
├── controllers/         # Controller classes (business logic)
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── UserController.php
│   ├── ActivityLogController.php
│   ├── ReportController.php
│   ├── DoctorRequestController.php
│   ├── DoctorHistoryController.php
│   ├── InventoryController.php
│   └── NotificationController.php
├── database/            # Database schema and documentation
│   ├── schema.sql
│   └── README.md
├── includes/           # Shared includes (header, footer, navigation)
│   ├── header.php
│   ├── footer.php
│   └── navigation.php
├── models/             # Model classes (database access)
│   ├── User.php
│   ├── Inventory.php
│   ├── ActivityLog.php
│   ├── Request.php
│   └── Notification.php
├── views/              # View templates (HTML/PHP)
│   ├── admin/
│   ├── auth/
│   ├── dashboard/
│   ├── doctor/
│   └── pharmacist/
└── [entry point files]  # PHP files that route to controllers
```

## MVC Flow

1. **Entry Point** (e.g., `admin_users.php`)
   - Requires `config.php`
   - Instantiates appropriate controller
   - Calls controller method

2. **Controller** (e.g., `UserController`)
   - Handles business logic
   - Interacts with models
   - Prepares data for views
   - Includes view file

3. **Model** (e.g., `UserModel`)
   - Database operations
   - Data validation
   - Returns data to controller

4. **View** (e.g., `views/admin/users.php`)
   - Displays HTML
   - Uses data from controller
   - Includes header/footer/navigation

## URL Structure

All entry points are at root level:
- `/admin_users.php`
- `/doctor_requests.php`
- `/pharmacist_inventory.php`
- etc.

## Notes

- The old `admin/`, `doctor/`, and `pharmacist/` folders have been removed
- All entry points are now at root level with descriptive names
- Views remain organized in subfolders by feature area
- Models, Controllers, and Views are clearly separated

