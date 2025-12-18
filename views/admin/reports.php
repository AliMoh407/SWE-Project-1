<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>System Reports</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="printReport()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button class="btn btn-secondary" onclick="exportReport()">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </div>
    
    <div class="reports-grid">
        <!-- Overview Statistics -->
        <div class="report-section">
            <h2>Overview Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_inventory; ?></h3>
                        <p>Inventory Items</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $low_stock_count; ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $expiring_soon_count; ?></h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Role Distribution -->
        <div class="report-section">
            <h2>User Role Distribution</h2>
            <div class="chart-container">
                <canvas id="roleChart"></canvas>
            </div>
            <div class="role-stats">
                <?php foreach ($role_distribution as $role => $count): ?>
                <div class="role-stat">
                    <span class="role-label"><?php echo ucfirst($role); ?></span>
                    <span class="role-count"><?php echo $count; ?></span>
                    <div class="role-bar">
                        <div class="role-fill" style="width: <?php echo ($count / $total_users) * 100; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Inventory Categories -->
        <div class="report-section">
            <h2>Inventory Categories</h2>
            <div class="category-stats">
                <?php foreach ($category_distribution as $category => $count): ?>
                <div class="category-stat">
                    <div class="category-info">
                        <h4><?php echo htmlspecialchars($category); ?></h4>
                        <p><?php echo $count; ?> items</p>
                    </div>
                    <div class="category-bar">
                        <div class="category-fill" style="width: <?php echo ($count / $total_inventory) * 100; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        <div class="report-section">
            <h2>Low Stock Items</h2>
            <?php if (empty($low_stock_items)): ?>
            <div class="no-data">
                <i class="fas fa-check-circle"></i>
                <p>No low stock items found!</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo $item['stock']; ?></td>
                            <td><?php echo $item['min_stock']; ?></td>
                            <td>
                                <span class="status-badge status-warning">Low Stock</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Expiring Soon -->
        <div class="report-section">
            <h2>Items Expiring Soon (30 days)</h2>
            <?php if (empty($expiring_soon_items)): ?>
            <div class="no-data">
                <i class="fas fa-check-circle"></i>
                <p>No items expiring soon!</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Expiry Date</th>
                            <th>Days Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiring_soon_items as $item): ?>
                        <?php 
                        $expiry = new DateTime($item['expiry_date']);
                        $today = new DateTime();
                        $days_remaining = $today->diff($expiry)->days;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($item['expiry_date'])); ?></td>
                            <td><?php echo $days_remaining; ?> days</td>
                            <td>
                                <span class="status-badge status-warning">Expiring Soon</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Controlled Medicines -->
        <div class="report-section">
            <h2>Controlled Medicines Summary</h2>
            <div class="controlled-summary">
                <div class="summary-stat">
                    <h3><?php echo $controlled_medicines; ?></h3>
                    <p>Controlled Medicines in Inventory</p>
                </div>
                <div class="summary-info">
                    <p><strong>Note:</strong> Controlled medicines require special authorization and tracking.</p>
                    <p>Only authorized personnel can request and dispense these items.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/reports.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


