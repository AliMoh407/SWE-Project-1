<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
    </div>
    
    <div class="dashboard-stats">
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($users); ?></h3>
                <p>Total User</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($inventory); ?></h3>
                <p>Inventory Items</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($inventory, 'isLowStock')); ?></h3>
                <p>Low Stock Items</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($activity_logs); ?></h3>
                <p>Recent Activities</p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_PHARMACIST)): ?>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($inventory); ?></h3>
                <p>Total Items</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($inventory, 'isLowStock')); ?></h3>
                <p>Low Stock Items</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($inventory, function($item) { return isExpiringSoon($item); })); ?></h3>
                <p>Expiring Soon</p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_DOCTOR)): ?>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <h3>3</h3>
                <p>Pending Requests</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>12</h3>
                <p>Approved Requests</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="dashboard-content">
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="<?php echo getBaseUrl(); ?>admin/users.php" class="action-card">
                    <i class="fas fa-user-plus"></i>
                    <h3>Manage Users</h3>
                    <p>Create, edit, or delete user accounts</p>
                </a>
                <a href="<?php echo getBaseUrl(); ?>admin/reports.php" class="action-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>View Reports</h3>
                    <p>System reports and analytics</p>
                </a>
                <a href="<?php echo getBaseUrl(); ?>admin/activity_logs.php" class="action-card">
                    <i class="fas fa-history"></i>
                    <h3>Activity Logs</h3>
                    <p>View system activity and transactions</p>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_PHARMACIST)): ?>
        <div class="dashboard-section">
            <h2>Low Stock Alerts</h2>
            <div class="alerts-container">
                <?php foreach (array_filter($inventory, 'isLowStock') as $item): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong><?php echo htmlspecialchars($item['name']); ?></strong> - 
                    Stock: <?php echo $item['stock']; ?> (Min: <?php echo $item['min_stock']; ?>)
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_DOCTOR)): ?>
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="<?php echo getBaseUrl(); ?>doctor/requests.php" class="action-card">
                    <i class="fas fa-plus-circle"></i>
                    <h3>Request Items</h3>
                    <p>Request medicines or equipment</p>
                </a>
                <a href="<?php echo getBaseUrl(); ?>doctor/request_history.php" class="action-card">
                    <i class="fas fa-list"></i>
                    <h3>Request History</h3>
                    <p>View your request history</p>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


