# Project Structure

This project follows a clean MVC (Model-View-Controller) architecture.

## Root Level Files
- `config.php` - Database configuration and global settings
- `login.php` - Login entry point
- `logout.php` - Logout handler

## Entry Points (routes/ folder)
- `routes/dashboard.php` - Dashboard
- `routes/admin_users.php` - User management
- `routes/admin_activity_logs.php` - Activity logs
- `routes/admin_reports.php` - System reports
- `routes/doctor_requests.php` - Create item requests
- `routes/doctor_request_history.php` - View request history
- `routes/pharmacist_inventory.php` - Inventory management
- `routes/pharmacist_notifications.php` - Notifications

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
├── routes/             # Entry point files (route to controllers)
│   ├── dashboard.php
│   ├── admin_users.php
│   ├── admin_activity_logs.php
│   ├── admin_reports.php
│   ├── doctor_requests.php
│   ├── doctor_request_history.php
│   ├── pharmacist_inventory.php
│   └── pharmacist_notifications.php
└── views/              # View templates (HTML/PHP)
    ├── admin/
    ├── auth/
    ├── dashboard/
    ├── doctor/
    └── pharmacist/
```

## MVC Flow

1. **Entry Point** (e.g., `routes/admin_users.php`)
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

All entry points are in the `routes/` folder:
- `/routes/dashboard.php`
- `/routes/admin_users.php`
- `/routes/doctor_requests.php`
- `/routes/pharmacist_inventory.php`
- etc.

## Notes

- All entry point files are organized in the `routes/` folder
- Root level only contains `config.php`, `login.php`, and `logout.php`
- Views remain organized in subfolders by feature area
- Models, Controllers, Views, and Routes are clearly separated
- Clean MVC architecture with proper separation of concerns

