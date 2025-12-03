<?php if (isLoggedIn()): ?>
<nav class="navigation">
    <ul class="nav-menu">
        <li><a href="<?php echo getBaseUrl(); ?>dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
        
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <li><a href="<?php echo getBaseUrl(); ?>admin/users.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/users.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> User Management
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>admin/activity_logs.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/activity_logs.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Activity Logs
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>admin/reports.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/reports.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a></li>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_PHARMACIST)): ?>
        <li><a href="<?php echo getBaseUrl(); ?>pharmacist/inventory.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'pharmacist/inventory.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-boxes"></i> Inventory
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>pharmacist/notifications.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'pharmacist/notifications.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i> Notifications
        </a></li>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_DOCTOR)): ?>
        <li><a href="<?php echo getBaseUrl(); ?>doctor/requests.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'doctor/requests.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-list"></i> Request Items
        </a></li>
        <li><a href="<?php echo getBaseUrl(); ?>doctor/request_history.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'doctor/request_history.php') !== false ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Request History
        </a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
