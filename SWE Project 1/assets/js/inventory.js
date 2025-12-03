// Inventory Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeInventory();
});

function initializeInventory() {
    console.log('Inventory management initialized');
}

function showAddItemModal() {
    showModal('addItemModal');
}

function showEditItemModal(item) {
    // Populate form with item data
    document.getElementById('edit_item_id').value = item.id;
    document.getElementById('edit_item_name').value = item.name;
    document.getElementById('edit_item_category').value = item.category;
    document.getElementById('edit_item_stock').value = item.stock;
    document.getElementById('edit_item_min_stock').value = item.min_stock;
    document.getElementById('edit_item_expiry').value = item.expiry_date;
    document.getElementById('edit_item_price').value = item.price;
    document.getElementById('edit_item_controlled').checked = item.controlled;
    
    showModal('editItemModal');
}

function showItemDetails(item) {
    const content = `
        <div class="item-details">
            <div class="detail-section">
                <h3>Item Information</h3>
                <div class="detail-row">
                    <label>Name:</label>
                    <span>${item.name}</span>
                </div>
                <div class="detail-row">
                    <label>Category:</label>
                    <span>${item.category}</span>
                </div>
                <div class="detail-row">
                    <label>Current Stock:</label>
                    <span class="${item.stock <= item.min_stock ? 'low-stock' : 'normal-stock'}">${item.stock}</span>
                </div>
                <div class="detail-row">
                    <label>Minimum Stock:</label>
                    <span>${item.min_stock}</span>
                </div>
                <div class="detail-row">
                    <label>Expiry Date:</label>
                    <span class="${isExpiringSoon(item) ? 'expiring-soon' : ''}">${formatDate(item.expiry_date)}</span>
                </div>
                <div class="detail-row">
                    <label>Price:</label>
                    <span>${formatCurrency(item.price)}</span>
                </div>
                <div class="detail-row">
                    <label>Controlled:</label>
                    <span>${item.controlled ? 'Yes' : 'No'}</span>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('itemDetailsContent').innerHTML = content;
    showModal('itemDetailsModal');
}

function showStockAdjustmentModal(itemId, itemName) {
    document.getElementById('adjust_item_id').value = itemId;
    document.getElementById('adjust_item_name').textContent = itemName;
    
    // Get current stock from table
    const tableRow = document.querySelector(`tr[data-item-id="${itemId}"]`);
    const currentStock = tableRow ? tableRow.querySelector('.stock-amount').textContent : '0';
    document.getElementById('current_stock_amount').textContent = currentStock;
    
    showModal('stockAdjustmentModal');
}

// Form validation for inventory
function validateItemForm(form) {
    const name = form.querySelector('input[name="name"]');
    const category = form.querySelector('select[name="category"]');
    const stock = form.querySelector('input[name="stock"]');
    const minStock = form.querySelector('input[name="min_stock"]');
    const expiryDate = form.querySelector('input[name="expiry_date"]');
    const price = form.querySelector('input[name="price"]');
    
    let isValid = true;
    
    // Validate name
    if (!name.value.trim()) {
        showFieldError(name, 'Item name is required');
        isValid = false;
    } else {
        clearFieldError(name);
    }
    
    // Validate category
    if (!category.value) {
        showFieldError(category, 'Please select a category');
        isValid = false;
    } else {
        clearFieldError(category);
    }
    
    // Validate stock
    if (!stock.value || stock.value < 0) {
        showFieldError(stock, 'Stock must be a positive number');
        isValid = false;
    } else {
        clearFieldError(stock);
    }
    
    // Validate minimum stock
    if (!minStock.value || minStock.value < 0) {
        showFieldError(minStock, 'Minimum stock must be a positive number');
        isValid = false;
    } else {
        clearFieldError(minStock);
    }
    
    // Validate expiry date
    if (!expiryDate.value) {
        showFieldError(expiryDate, 'Expiry date is required');
        isValid = false;
    } else {
        const expiry = new Date(expiryDate.value);
        const today = new Date();
        if (expiry <= today) {
            showFieldError(expiryDate, 'Expiry date must be in the future');
            isValid = false;
        } else {
            clearFieldError(expiryDate);
        }
    }
    
    // Validate price
    if (!price.value || price.value < 0) {
        showFieldError(price, 'Price must be a positive number');
        isValid = false;
    } else {
        clearFieldError(price);
    }
    
    return isValid;
}

// Handle form submissions
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.querySelector('#addItemModal form');
    const editForm = document.querySelector('#editItemModal form');
    const adjustmentForm = document.querySelector('#stockAdjustmentModal form');
    
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateItemForm(this)) {
                showLoading(this.querySelector('button[type="submit"]'));
                
                // Simulate API call
                setTimeout(() => {
                    hideLoading(this.querySelector('button[type="submit"]'));
                    this.submit();
                }, 1000);
            }
        });
    }
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateItemForm(this)) {
                showLoading(this.querySelector('button[type="submit"]'));
                
                // Simulate API call
                setTimeout(() => {
                    hideLoading(this.querySelector('button[type="submit"]'));
                    this.submit();
                }, 1000);
            }
        });
    }
    
    if (adjustmentForm) {
        adjustmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const adjustmentType = this.querySelector('select[name="adjustment_type"]');
            const amount = this.querySelector('input[name="amount"]');
            const reason = this.querySelector('textarea[name="reason"]');
            
            let isValid = true;
            
            if (!adjustmentType.value) {
                showFieldError(adjustmentType, 'Please select adjustment type');
                isValid = false;
            } else {
                clearFieldError(adjustmentType);
            }
            
            if (!amount.value || amount.value < 0) {
                showFieldError(amount, 'Amount must be a positive number');
                isValid = false;
            } else {
                clearFieldError(amount);
            }
            
            if (!reason.value.trim()) {
                showFieldError(reason, 'Reason is required');
                isValid = false;
            } else {
                clearFieldError(reason);
            }
            
            if (isValid) {
                showLoading(this.querySelector('button[type="submit"]'));
                
                // Simulate API call
                setTimeout(() => {
                    hideLoading(this.querySelector('button[type="submit"]'));
                    this.submit();
                }, 1000);
            }
        });
    }
});

// Stock adjustment calculations
function calculateNewStock(adjustmentType, currentStock, amount) {
    switch (adjustmentType) {
        case 'add':
            return parseInt(currentStock) + parseInt(amount);
        case 'subtract':
            return Math.max(0, parseInt(currentStock) - parseInt(amount));
        case 'set':
            return parseInt(amount);
        default:
            return parseInt(currentStock);
    }
}

// Update stock preview
document.addEventListener('DOMContentLoaded', function() {
    const adjustmentType = document.getElementById('adjustment_type');
    const amountInput = document.getElementById('adjustment_amount');
    const currentStockSpan = document.getElementById('current_stock_amount');
    
    if (adjustmentType && amountInput && currentStockSpan) {
        function updateStockPreview() {
            const currentStock = parseInt(currentStockSpan.textContent);
            const amount = parseInt(amountInput.value) || 0;
            const type = adjustmentType.value;
            
            if (type && amount > 0) {
                const newStock = calculateNewStock(type, currentStock, amount);
                const preview = document.getElementById('stock_preview');
                
                if (!preview) {
                    const previewDiv = document.createElement('div');
                    previewDiv.id = 'stock_preview';
                    previewDiv.className = 'stock-preview';
                    previewDiv.innerHTML = `
                        <strong>New Stock:</strong> <span class="new-stock">${newStock}</span>
                    `;
                    amountInput.parentNode.appendChild(previewDiv);
                } else {
                    preview.querySelector('.new-stock').textContent = newStock;
                }
            } else {
                const preview = document.getElementById('stock_preview');
                if (preview) {
                    preview.remove();
                }
            }
        }
        
        adjustmentType.addEventListener('change', updateStockPreview);
        amountInput.addEventListener('input', updateStockPreview);
    }
});

// Inventory search and filtering
function filterInventory() {
    const searchTerm = document.getElementById('inventorySearch')?.value.toLowerCase() || '';
    const categoryFilter = document.getElementById('categoryFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    
    const rows = document.querySelectorAll('.data-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const category = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        
        let showRow = true;
        
        if (searchTerm && !text.includes(searchTerm)) {
            showRow = false;
        }
        
        if (categoryFilter && category !== categoryFilter.toLowerCase()) {
            showRow = false;
        }
        
        if (statusFilter) {
            switch (statusFilter) {
                case 'low_stock':
                    if (!row.classList.contains('low-stock-row')) {
                        showRow = false;
                    }
                    break;
                case 'expiring_soon':
                    // This would need to be implemented based on your data structure
                    break;
                case 'controlled':
                    if (!row.querySelector('.controlled-badge')) {
                        showRow = false;
                    }
                    break;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
    
    updateInventoryCount();
}

function updateInventoryCount() {
    const visibleRows = document.querySelectorAll('.data-table tbody tr[style=""]').length;
    const totalRows = document.querySelectorAll('.data-table tbody tr').length;
    
    const countElement = document.querySelector('.inventory-count');
    if (countElement) {
        countElement.textContent = `Showing ${visibleRows} of ${totalRows} items`;
    }
}

// Add search and filter listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('inventorySearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterInventory, 300));
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterInventory);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterInventory);
    }
});

// Bulk operations for inventory
function selectAllItems() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="item_ids[]"]');
    const selectAllCheckbox = document.getElementById('selectAllItems');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"][name="item_ids[]"]:checked');
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
            bulkActions.querySelector('.selected-count').textContent = checkedBoxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

function bulkUpdateStock() {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"][name="item_ids[]"]:checked');
    
    if (checkedBoxes.length === 0) {
        showAlert('Please select items to update', 'warning');
        return;
    }
    
    const adjustment = prompt(`Enter stock adjustment (positive to add, negative to subtract):`);
    if (adjustment === null || adjustment === '') return;
    
    const amount = parseInt(adjustment);
    if (isNaN(amount)) {
        showAlert('Please enter a valid number', 'error');
        return;
    }
    
    confirmAction(`Are you sure you want to ${amount >= 0 ? 'add' : 'subtract'} ${Math.abs(amount)} to/from ${checkedBoxes.length} item(s)?`, function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="bulk_stock_update">
            <input type="hidden" name="amount" value="${amount}">
        `;
        
        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'item_ids[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
        
        document.body.appendChild(form);
        form.submit();
    });
}

// Export inventory functionality
function exportInventory() {
    const table = document.querySelector('.data-table');
    if (table) {
        exportToCSV(table, 'inventory.csv');
    }
}

// Low stock alerts
function checkLowStockAlerts() {
    const lowStockRows = document.querySelectorAll('.low-stock-row');
    
    if (lowStockRows.length > 0) {
        const message = `${lowStockRows.length} item(s) are running low on stock.`;
        showAlert(message, 'warning', 0);
    }
}

// Initialize low stock alerts on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(checkLowStockAlerts, 1000);
});

// Utility functions for inventory
function isExpiringSoon(item) {
    const expiry = new Date(item.expiry_date);
    const today = new Date();
    const diffTime = expiry - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 30;
}

// Add CSS for additional inventory styles
const inventoryStyles = `
.stock-preview {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 4px;
    padding: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

.new-stock {
    font-weight: bold;
    color: #1976d2;
}

.item-details .detail-section {
    margin-bottom: 1.5rem;
}

.item-details .detail-section h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.item-details .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.item-details .detail-row:last-child {
    border-bottom: none;
}

.item-details .detail-row label {
    font-weight: 500;
    color: #666;
}

.item-details .detail-row span {
    color: #333;
}
`;

// Add styles to document
const inventoryStyleSheet = document.createElement('style');
inventoryStyleSheet.textContent = inventoryStyles;
document.head.appendChild(inventoryStyleSheet);
