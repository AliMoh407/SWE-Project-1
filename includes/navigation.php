<?php if (isLoggedIn()): ?>
<nav class="navigation">
    <ul class="nav-menu">
        <li><a href="<?php echo getBaseUrl(); ?>routes/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
        
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <li><a href="<?php echo getBaseUrl(); ?>routes/admin_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> User Management
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>routes/admin_activity_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_activity_logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Activity Logs
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>routes/admin_reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a></li>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_PHARMACIST)): ?>
        <li><a href="<?php echo getBaseUrl(); ?>routes/pharmacist_inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pharmacist_inventory.php' ? 'active' : ''; ?>">
            <i class="fas fa-boxes"></i> Inventory
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>routes/pharmacist_requests.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pharmacist_requests.php' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-check"></i> Manage Requests
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>routes/pharmacist_notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pharmacist_notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i> Notifications
        </a></li>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_DOCTOR)): ?>
        <li><a href="<?php echo getBaseUrl(); ?>routes/doctor_requests.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'doctor_requests.php' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-list"></i> Request Items
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>routes/doctor_request_history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'doctor_request_history.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Request History
        </a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
