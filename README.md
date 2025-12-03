# Medical Inventory Management System

A comprehensive web-based medical inventory management system built with PHP, HTML, CSS, and JavaScript. This system provides role-based access control and simulates a complete inventory management workflow without requiring a database.

## Features

### 🔐 Role-Based Access Control
- **Admin**: Full system access with user management, activity logs, and reports
- **Doctor**: Item request functionality with controlled medicine restrictions
- **Pharmacist**: Inventory management, notifications, and stock monitoring
- **Patient**: Basic access (demo purposes)

### 👨‍💼 Admin Features
- **User Management**: Create, edit, delete user accounts with role assignment
- **Activity Logs**: View system activity and transaction history
- **Reports**: Comprehensive system reports including:
  - User statistics and role distribution
  - Inventory overview and category breakdown
  - Low stock alerts
  - Items expiring soon
  - Controlled medicines summary

### 💊 Pharmacist Features
- **Inventory Management**: 
  - View and search inventory items
  - Add, edit, and update item details
  - Stock adjustment with reason tracking
  - Filter by category, status, and search terms
- **Notifications**: 
  - Low stock alerts
  - Expiry warnings
  - System notifications
  - Alternative item suggestions
- **Quick Actions**:
  - Generate reorder lists
  - Export inventory data
  - Print reports

### 👩‍⚕️ Doctor Features
- **Item Requests**:
  - Search and select items from inventory
  - Submit requests with patient information
  - Controlled medicine authorization workflow
  - Real-time availability checking
- **Request History**:
  - View all submitted requests
  - Filter by status, date, and search terms
  - Request details and timeline
  - Export and print capabilities

### 🎨 User Interface
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional design with intuitive navigation
- **Interactive Elements**: Modals, alerts, search, and filtering
- **Accessibility**: Proper form labels, keyboard navigation, and screen reader support

## Demo Accounts

Use these accounts to test different user roles:

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Doctor | doctor1 | doctor123 |
| Pharmacist | pharmacist1 | pharma123 |
| Patient | patient1 | patient123 |

## Installation

1. **Requirements**:
   - PHP 7.4 or higher
   - Web server (Apache/Nginx)
   - Modern web browser

2. **Setup**:
   ```bash
   # Clone or download the project files
   # Place files in your web server directory (e.g., htdocs, www, public_html)
   
   # For XAMPP/WAMP:
   # Copy files to C:\xampp\htdocs\medical-inventory
   
   # For LAMP:
   # Copy files to /var/www/html/medical-inventory
   ```

3. **Access**:
   - Open your web browser
   - Navigate to `http://localhost/medical-inventory/login.php`
   - Use one of the demo accounts to log in

## File Structure

```
medical-inventory/
├── config.php                 # Main configuration and data
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── dashboard.php              # Main dashboard
├── includes/
│   ├── header.php            # Common header
│   ├── navigation.php        # Role-based navigation
│   └── footer.php            # Common footer
├── admin/
│   ├── users.php             # User management
│   ├── activity_logs.php     # Activity logs
│   └── reports.php           # System reports
├── pharmacist/
│   ├── inventory.php         # Inventory management
│   └── notifications.php     # Notifications center
├── doctor/
│   ├── requests.php          # Item requests
│   └── request_history.php   # Request history
└── assets/
    ├── css/
    │   └── style.css         # Main stylesheet
    └── js/
        ├── main.js           # Common JavaScript
        ├── user-management.js
        ├── inventory.js
        ├── doctor-requests.js
        ├── notifications.js
        ├── activity-logs.js
        ├── reports.js
        └── request-history.js
```

## Key Features Explained

### 🔒 Security Features
- Session-based authentication
- Role-based access control
- Form validation and sanitization
- CSRF protection simulation

### 📊 Data Management
- Simulated database using PHP arrays
- Sample inventory data with realistic medical items
- Activity logging and audit trails
- Data export capabilities (CSV, PDF simulation)

### 🎯 Business Logic
- **Controlled Medicines**: Special authorization workflow
- **Low Stock Alerts**: Automatic notifications when stock falls below minimum
- **Expiry Tracking**: Alerts for items expiring within 30 days
- **Alternative Suggestions**: Smart recommendations when items are unavailable

### 📱 Responsive Design
- Mobile-first approach
- Flexible grid layouts
- Touch-friendly interfaces
- Optimized for all screen sizes

## Customization

### Adding New Roles
1. Update `config.php` with new role constant
2. Add role to navigation in `includes/navigation.php`
3. Create role-specific pages and functionality

### Modifying Inventory Data
Edit the `$inventory` array in `config.php` to add, remove, or modify items.

### Styling Changes
Modify `assets/css/style.css` to customize colors, fonts, and layout.

### Adding New Features
- Create new PHP pages following the existing structure
- Add corresponding JavaScript files for interactivity
- Update navigation menus as needed

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Future Enhancements

This system is designed as a frontend simulation. For production use, consider:

1. **Database Integration**: Replace PHP arrays with MySQL/PostgreSQL
2. **Real Authentication**: Implement secure user authentication
3. **API Integration**: Connect with real medical databases
4. **Advanced Reporting**: Add charts and analytics
5. **Mobile App**: Create companion mobile application
6. **Barcode Scanning**: Add inventory scanning capabilities
7. **Automated Alerts**: Email/SMS notifications
8. **Audit Trail**: Enhanced logging and compliance features

## Support

For questions or issues:
- Check the demo accounts and try different roles
- Review the code comments for implementation details
- Ensure PHP and web server are properly configured

## License

This project is created for educational and demonstration purposes. Feel free to modify and extend it for your specific needs.

---

**Note**: This is a frontend simulation system designed for demonstration purposes. It uses dummy data and simulated database operations. For production use, implement proper database integration, security measures, and real authentication systems.
