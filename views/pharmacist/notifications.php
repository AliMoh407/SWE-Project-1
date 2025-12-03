<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Notifications</h1>
        <div class="page-actions">
            <button class="btn btn-secondary" onclick="markAllAsRead()">
                <i class="fas fa-check-double"></i> Mark All Read
            </button>
        </div>
    </div>
    
    <?php if (isset($message) && $message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <!-- Notification Stats -->
    <div class="notification-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($notifications, function($n) { return !$n['read']; })); ?></h3>
                <p>Unread Notifications</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($notifications, function($n) { return $n['priority'] === 'high' && !$n['read']; })); ?></h3>
                <p>High Priority</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($low_stock_items); ?></h3>
                <p>Low Stock Items</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($expiring_soon_items); ?></h3>
                <p>Expiring Soon</p>
            </div>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <div class="notification-tabs">
        <button class="tab-button active" onclick="filterNotifications('all')">
            All Notifications
        </button>
        <button class="tab-button" onclick="filterNotifications('unread')">
            Unread
        </button>
        <button class="tab-button" onclick="filterNotifications('high_priority')">
            High Priority
        </button>
        <button class="tab-button" onclick="filterNotifications('low_stock')">
            Low Stock
        </button>
        <button class="tab-button" onclick="filterNotifications('expiry')">
            Expiry Warnings
        </button>
    </div>
    
    <!-- Notifications List -->
    <div class="notifications-container">
        <?php foreach ($notifications as $notification): ?>
        <div class="notification-item <?php echo $notification['read'] ? 'read' : 'unread'; ?> priority-<?php echo $notification['priority']; ?>" 
             data-type="<?php echo $notification['type']; ?>">
            <div class="notification-icon">
                <?php
                switch ($notification['type']) {
                    case 'low_stock':
                        echo '<i class="fas fa-exclamation-triangle"></i>';
                        break;
                    case 'expiry':
                        echo '<i class="fas fa-clock"></i>';
                        break;
                    case 'request':
                        echo '<i class="fas fa-clipboard-list"></i>';
                        break;
                    case 'system':
                        echo '<i class="fas fa-info-circle"></i>';
                        break;
                    default:
                        echo '<i class="fas fa-bell"></i>';
                }
                ?>
            </div>
            
            <div class="notification-content">
                <div class="notification-header">
                    <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                    <span class="notification-time">
                        <?php echo date('M j, Y g:i A', strtotime($notification['timestamp'])); ?>
                    </span>
                </div>
                <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                
                <?php if ($notification['type'] === 'low_stock' || $notification['type'] === 'expiry'): ?>
                <div class="notification-actions">
                    <a href="<?php echo getBaseUrl(); ?>pharmacist/inventory.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View Item
                    </a>
                    <button class="btn btn-sm btn-secondary" onclick="showAlternatives(<?php echo $notification['item_id']; ?>)">
                        <i class="fas fa-exchange-alt"></i> Show Alternatives
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="notification-actions-right">
                <?php if (!$notification['read']): ?>
                <button class="btn btn-sm btn-success" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                    <i class="fas fa-check"></i>
                </button>
                <?php endif; ?>
                <button class="btn btn-sm btn-danger" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="<?php echo getBaseUrl(); ?>pharmacist/inventory.php?status=low_stock" class="action-card">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>View Low Stock Items</h3>
                <p>Check all items with low inventory</p>
            </a>
            
            <a href="<?php echo getBaseUrl(); ?>pharmacist/inventory.php?status=expiring_soon" class="action-card">
                <i class="fas fa-clock"></i>
                <h3>View Expiring Items</h3>
                <p>Items expiring in the next 30 days</p>
            </a>
            
            <button class="action-card" onclick="showReorderModal()">
                <i class="fas fa-shopping-cart"></i>
                <h3>Generate Reorder List</h3>
                <p>Create a list of items to reorder</p>
            </button>
        </div>
    </div>
</main>

<!-- Alternatives Modal -->
<div id="alternativesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Alternative Items</h2>
            <span class="close" onclick="closeModal('alternativesModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="alternativesContent">
                <p>Loading alternatives...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('alternativesModal')">Close</button>
        </div>
    </div>
</div>

<!-- Reorder List Modal -->
<div id="reorderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Reorder List</h2>
            <span class="close" onclick="closeModal('reorderModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="reorder-list">
                <?php foreach ($low_stock_items as $item): ?>
                <div class="reorder-item">
                    <div class="item-info">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>Current Stock: <?php echo $item['stock']; ?> | Min Required: <?php echo $item['min_stock']; ?></p>
                    </div>
                    <div class="reorder-quantity">
                        <label>Reorder Quantity:</label>
                        <input type="number" value="<?php echo $item['min_stock'] * 3; ?>" min="1">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('reorderModal')">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="generateReorderReport()">
                <i class="fas fa-file-pdf"></i> Generate Report
            </button>
        </div>
    </div>
</div>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/notifications.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


