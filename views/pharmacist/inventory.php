<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Inventory Management</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="showAddItemModal()">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <!-- Quick Stats -->
    <div class="stats-overview">
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
                <p>Low Stock</p>
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
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($inventory, function($item) { return $item['controlled']; })); ?></h3>
                <p>Controlled Items</p>
            </div>
        </div>
    </div>
    
    <!-- Search and Filters -->
    <div class="filters-container">
        <form method="GET" class="filters-form">
            <div class="search-container">
                <input type="text" name="search" placeholder="Search items..." 
                       value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <i class="fas fa-search"></i>
            </div>
            
            <select name="category" class="filter-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category); ?>" 
                        <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="low_stock" <?php echo $status_filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                <option value="expiring_soon" <?php echo $status_filter === 'expiring_soon' ? 'selected' : ''; ?>>Expiring Soon</option>
                <option value="controlled" <?php echo $status_filter === 'controlled' ? 'selected' : ''; ?>>Controlled Items</option>
            </select>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-filter"></i> Filter
            </button>
            
            <a href="<?php echo getBaseUrl(); ?>routes/pharmacist_inventory.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>
    
    <!-- Inventory Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Min Stock</th>
                    <th>Expiry Date</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filtered_inventory as $item): ?>
                <tr class="<?php echo isLowStock($item) ? 'low-stock-row' : ''; ?>" data-item-id="<?php echo $item['id']; ?>">
                    <td>
                        <div class="item-info">
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <?php if ($item['controlled']): ?>
                            <span class="controlled-badge">
                                <i class="fas fa-shield-alt"></i> Controlled
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td>
                        <span class="stock-amount <?php echo isLowStock($item) ? 'low-stock' : 'normal-stock'; ?>">
                            <?php echo $item['stock']; ?>
                        </span>
                    </td>
                    <td><?php echo $item['min_stock']; ?></td>
                    <td>
                        <span class="expiry-date <?php echo isExpiringSoon($item) ? 'expiring-soon' : ''; ?>">
                            <?php echo date('M j, Y', strtotime($item['expiry_date'])); ?>
                        </span>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <?php if (isLowStock($item)): ?>
                        <span class="status-badge status-warning">Low Stock</span>
                        <?php endif; ?>
                        <?php if (isExpiringSoon($item)): ?>
                        <span class="status-badge status-warning">Expiring Soon</span>
                        <?php endif; ?>
                        <?php if (!isLowStock($item) && !isExpiringSoon($item)): ?>
                        <span class="status-badge status-success">Normal</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <button class="btn btn-sm btn-secondary" onclick="showEditItemModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info" onclick="showItemDetails(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="showStockAdjustmentModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                            <i class="fas fa-plus-minus"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($filtered_inventory)): ?>
                <tr>
                    <td colspan="8" class="no-data">
                        <i class="fas fa-search"></i>
                        <p>No items found matching your criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add Item Modal -->
<div id="addItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Item</h2>
            <span class="close" onclick="closeModal('addItemModal')">&times;</span>
        </div>
        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="add_item">
            
            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" id="item_name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="item_category">Category</label>
                <select id="item_category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Pain Relief">Pain Relief</option>
                    <option value="Medical Equipment">Medical Equipment</option>
                    <option value="Diabetes Care">Diabetes Care</option>
                    <option value="Medical Supplies">Medical Supplies</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="item_stock">Current Stock</label>
                    <input type="number" id="item_stock" name="stock" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="item_min_stock">Minimum Stock</label>
                    <input type="number" id="item_min_stock" name="min_stock" required min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="item_expiry">Expiry Date</label>
                <input type="date" id="item_expiry" name="expiry_date" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="item_price">Price ($)</label>
                    <input type="number" id="item_price" name="price" step="0.01" required min="0">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="item_controlled" name="controlled">
                        <span class="checkmark"></span>
                        Controlled Medicine
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="editItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Item</h2>
            <span class="close" onclick="closeModal('editItemModal')">&times;</span>
        </div>
        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="edit_item">
            <input type="hidden" id="edit_item_id" name="item_id">
            
            <div class="form-group">
                <label for="edit_item_name">Item Name</label>
                <input type="text" id="edit_item_name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="edit_item_category">Category</label>
                <select id="edit_item_category" name="category" required>
                    <option value="Pain Relief">Pain Relief</option>
                    <option value="Medical Equipment">Medical Equipment</option>
                    <option value="Diabetes Care">Diabetes Care</option>
                    <option value="Medical Supplies">Medical Supplies</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_item_stock">Current Stock</label>
                    <input type="number" id="edit_item_stock" name="stock" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="edit_item_min_stock">Minimum Stock</label>
                    <input type="number" id="edit_item_min_stock" name="min_stock" required min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_item_expiry">Expiry Date</label>
                <input type="date" id="edit_item_expiry" name="expiry_date" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_item_price">Price ($)</label>
                    <input type="number" id="edit_item_price" name="price" step="0.01" required min="0">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_item_controlled" name="controlled">
                        <span class="checkmark"></span>
                        Controlled Medicine
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div id="stockAdjustmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Adjust Stock</h2>
            <span class="close" onclick="closeModal('stockAdjustmentModal')">&times;</span>
        </div>
        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="adjust_stock">
            <input type="hidden" id="adjust_item_id" name="item_id">
            
            <div class="form-group">
                <label id="adjust_item_name">Item Name</label>
                <p class="current-stock">Current Stock: <span id="current_stock_amount">0</span></p>
            </div>
            
            <div class="form-group">
                <label for="adjustment_type">Adjustment Type</label>
                <select id="adjustment_type" name="adjustment_type" required>
                    <option value="">Select Type</option>
                    <option value="add">Add Stock</option>
                    <option value="subtract">Subtract Stock</option>
                    <option value="set">Set Stock</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="adjustment_amount">Amount</label>
                <input type="number" id="adjustment_amount" name="amount" required min="0">
            </div>
            
            <div class="form-group">
                <label for="adjustment_reason">Reason</label>
                <textarea id="adjustment_reason" name="reason" rows="3" placeholder="Enter reason for stock adjustment..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('stockAdjustmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Adjust Stock</button>
            </div>
        </form>
    </div>
</div>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/inventory.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


