// Notifications JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();
});

function initializeNotifications() {
    // Initialize notification filtering
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.textContent.trim();
            filterNotifications(filter);
            
            // Update active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Auto-refresh notifications every 30 seconds
    setInterval(refreshNotifications, 30000);
}

function filterNotifications(filter) {
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
        let show = true;
        
        switch (filter) {
            case 'All Notifications':
                show = true;
                break;
            case 'Unread':
                show = notification.classList.contains('unread');
                break;
            case 'High Priority':
                show = notification.classList.contains('priority-high');
                break;
            case 'Low Stock':
                show = notification.getAttribute('data-type') === 'low_stock';
                break;
            case 'Expiry Warnings':
                show = notification.getAttribute('data-type') === 'expiry';
                break;
        }
        
        notification.style.display = show ? 'flex' : 'none';
    });
    
    updateNotificationCount();
}

function updateNotificationCount() {
    const visibleNotifications = document.querySelectorAll('.notification-item[style="flex"]').length;
    const totalNotifications = document.querySelectorAll('.notification-item').length;
    
    const countElement = document.querySelector('.notification-count');
    if (countElement) {
        countElement.textContent = `Showing ${visibleNotifications} of ${totalNotifications} notifications`;
    }
}

function markAsRead(notificationId) {
    // Simulate API call
    fetch('/api/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notificationId: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notification) {
                notification.classList.remove('unread');
                notification.classList.add('read');
                
                // Remove the mark as read button
                const markReadBtn = notification.querySelector('.notification-actions-right .btn-success');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }
            
            showAlert('Notification marked as read', 'success');
        } else {
            showAlert('Failed to mark notification as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error marking notification as read', 'error');
    });
}

function markAllAsRead() {
    const unreadNotifications = document.querySelectorAll('.notification-item.unread');
    
    if (unreadNotifications.length === 0) {
        showAlert('No unread notifications', 'info');
        return;
    }
    
    confirmAction(`Are you sure you want to mark all ${unreadNotifications.length} notifications as read?`, function() {
        // Simulate API call
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                unreadNotifications.forEach(notification => {
                    notification.classList.remove('unread');
                    notification.classList.add('read');
                    
                    // Remove mark as read buttons
                    const markReadBtn = notification.querySelector('.notification-actions-right .btn-success');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                });
                
                showAlert('All notifications marked as read', 'success');
                updateNotificationStats();
            } else {
                showAlert('Failed to mark notifications as read', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error marking notifications as read', 'error');
        });
    });
}

function deleteNotification(notificationId) {
    confirmAction('Are you sure you want to delete this notification?', function() {
        // Simulate API call
        fetch('/api/notifications/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notificationId: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from UI
                const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notification) {
                    notification.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        notification.remove();
                        updateNotificationStats();
                    }, 300);
                }
                
                showAlert('Notification deleted', 'success');
            } else {
                showAlert('Failed to delete notification', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting notification', 'error');
        });
    });
}

function showAlternatives(itemId) {
    // Get item information
    const item = getItemById(itemId);
    if (!item) {
        showAlert('Item not found', 'error');
        return;
    }
    
    // Find alternatives (items in same category with stock)
    const alternatives = getAlternatives(item.category, itemId);
    
    let content = `
        <h3>Alternatives for ${item.name}</h3>
        <p class="item-category">Category: ${item.category}</p>
    `;
    
    if (alternatives.length === 0) {
        content += '<p class="no-alternatives">No alternatives available in this category.</p>';
    } else {
        content += '<div class="alternatives-list">';
        alternatives.forEach(alt => {
            content += `
                <div class="alternative-item">
                    <div class="alt-info">
                        <h4>${alt.name}</h4>
                        <p>Stock: <span class="${alt.stock <= alt.min_stock ? 'low-stock' : 'normal-stock'}">${alt.stock}</span></p>
                        <p>Price: $${alt.price}</p>
                        ${alt.controlled ? '<span class="controlled-badge">Controlled</span>' : ''}
                    </div>
                    <div class="alt-actions">
                        <button class="btn btn-sm btn-primary" onclick="requestAlternative(${alt.id})">
                            Request This Item
                        </button>
                    </div>
                </div>
            `;
        });
        content += '</div>';
    }
    
    document.getElementById('alternativesContent').innerHTML = content;
    showModal('alternativesModal');
}

function getItemById(itemId) {
    // This would typically come from your inventory data
    // For demo purposes, we'll use a simple lookup
    const items = [
        { id: 1, name: 'Paracetamol 500mg', category: 'Pain Relief', stock: 150, min_stock: 50, price: 2.50, controlled: false },
        { id: 2, name: 'Morphine 10mg', category: 'Pain Relief', stock: 25, min_stock: 30, price: 15.00, controlled: true },
        { id: 3, name: 'Surgical Gloves', category: 'Medical Equipment', stock: 500, min_stock: 100, price: 0.50, controlled: false },
        { id: 4, name: 'Insulin Pen', category: 'Diabetes Care', stock: 75, min_stock: 50, price: 45.00, controlled: false },
        { id: 5, name: 'Oxycodone 5mg', category: 'Pain Relief', stock: 10, min_stock: 20, price: 8.00, controlled: true },
        { id: 6, name: 'Bandages', category: 'Medical Supplies', stock: 200, min_stock: 50, price: 1.25, controlled: false }
    ];
    
    return items.find(item => item.id == itemId);
}

function getAlternatives(category, excludeId) {
    // Get items in same category with stock, excluding the original item
    const items = [
        { id: 1, name: 'Paracetamol 500mg', category: 'Pain Relief', stock: 150, min_stock: 50, price: 2.50, controlled: false },
        { id: 2, name: 'Morphine 10mg', category: 'Pain Relief', stock: 25, min_stock: 30, price: 15.00, controlled: true },
        { id: 3, name: 'Surgical Gloves', category: 'Medical Equipment', stock: 500, min_stock: 100, price: 0.50, controlled: false },
        { id: 4, name: 'Insulin Pen', category: 'Diabetes Care', stock: 75, min_stock: 50, price: 45.00, controlled: false },
        { id: 5, name: 'Oxycodone 5mg', category: 'Pain Relief', stock: 10, min_stock: 20, price: 8.00, controlled: true },
        { id: 6, name: 'Bandages', category: 'Medical Supplies', stock: 200, min_stock: 50, price: 1.25, controlled: false }
    ];
    
    return items.filter(item => 
        item.category === category && 
        item.id != excludeId && 
        item.stock > 0
    );
}

function requestAlternative(itemId) {
    const item = getItemById(itemId);
    if (item) {
        closeModal('alternativesModal');
        showAlert(`Redirecting to request form for ${item.name}`, 'info');
        // In a real application, you would redirect to the request form with the item pre-selected
    }
}

function showReorderModal() {
    showModal('reorderModal');
}

function generateReorderReport() {
    const reorderItems = document.querySelectorAll('.reorder-item');
    const reorderData = [];
    
    reorderItems.forEach(item => {
        const name = item.querySelector('h4').textContent;
        const quantity = item.querySelector('input[type="number"]').value;
        reorderData.push({ name, quantity });
    });
    
    if (reorderData.length === 0) {
        showAlert('No items to reorder', 'warning');
        return;
    }
    
    // Generate CSV content
    let csvContent = 'Item Name,Reorder Quantity\n';
    reorderData.forEach(item => {
        csvContent += `"${item.name}",${item.quantity}\n`;
    });
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'reorder-list.csv';
    link.click();
    window.URL.revokeObjectURL(url);
    
    closeModal('reorderModal');
    showAlert('Reorder report generated successfully', 'success');
}

function refreshNotifications() {
    // Simulate refreshing notifications
    fetch('/api/notifications/refresh', {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.newNotifications) {
            // Add new notifications to the UI
            data.newNotifications.forEach(notification => {
                addNotificationToUI(notification);
            });
            
            updateNotificationStats();
        }
    })
    .catch(error => {
        console.error('Error refreshing notifications:', error);
    });
}

function addNotificationToUI(notification) {
    const container = document.querySelector('.notifications-container');
    const notificationElement = createNotificationElement(notification);
    container.insertBefore(notificationElement, container.firstChild);
}

function createNotificationElement(notification) {
    const div = document.createElement('div');
    div.className = `notification-item unread priority-${notification.priority}`;
    div.setAttribute('data-type', notification.type);
    div.setAttribute('data-notification-id', notification.id);
    
    const icon = getNotificationIcon(notification.type);
    
    div.innerHTML = `
        <div class="notification-icon">
            ${icon}
        </div>
        
        <div class="notification-content">
            <div class="notification-header">
                <h3>${notification.title}</h3>
                <span class="notification-time">
                    ${formatDateTime(notification.timestamp)}
                </span>
            </div>
            <p class="notification-message">${notification.message}</p>
            
            ${notification.type === 'low_stock' || notification.type === 'expiry' ? `
            <div class="notification-actions">
                <a href="inventory.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> View Item
                </a>
                <button class="btn btn-sm btn-secondary" onclick="showAlternatives(${notification.item_id})">
                    <i class="fas fa-exchange-alt"></i> Show Alternatives
                </button>
            </div>
            ` : ''}
        </div>
        
        <div class="notification-actions-right">
            <button class="btn btn-sm btn-success" onclick="markAsRead(${notification.id})">
                <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteNotification(${notification.id})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    return div;
}

function getNotificationIcon(type) {
    const icons = {
        'low_stock': '<i class="fas fa-exclamation-triangle"></i>',
        'expiry': '<i class="fas fa-clock"></i>',
        'request': '<i class="fas fa-clipboard-list"></i>',
        'system': '<i class="fas fa-info-circle"></i>'
    };
    return icons[type] || '<i class="fas fa-bell"></i>';
}

function updateNotificationStats() {
    const totalNotifications = document.querySelectorAll('.notification-item').length;
    const unreadNotifications = document.querySelectorAll('.notification-item.unread').length;
    const highPriorityNotifications = document.querySelectorAll('.notification-item.priority-high.unread').length;
    
    // Update stat cards
    const totalCard = document.querySelector('.stat-card:nth-child(1) h3');
    const unreadCard = document.querySelector('.stat-card:nth-child(2) h3');
    const highPriorityCard = document.querySelector('.stat-card:nth-child(3) h3');
    
    if (totalCard) totalCard.textContent = totalNotifications;
    if (unreadCard) unreadCard.textContent = unreadNotifications;
    if (highPriorityCard) highPriorityCard.textContent = highPriorityNotifications;
}

// Add CSS for notifications
const notificationsStyles = `
@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(-100%);
    }
}

.alternatives-list {
    max-height: 400px;
    overflow-y: auto;
}

.alternative-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    margin-bottom: 0.5rem;
}

.alt-info h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.alt-info p {
    margin: 0.25rem 0;
    color: #666;
    font-size: 0.9rem;
}

.no-alternatives {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 2rem;
}

.reorder-list {
    max-height: 400px;
    overflow-y: auto;
}

.reorder-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    margin-bottom: 0.5rem;
}

.reorder-item h4 {
    margin: 0 0 0.25rem 0;
    color: #333;
}

.reorder-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.reorder-quantity {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.reorder-quantity label {
    font-size: 0.9rem;
    color: #666;
}

.reorder-quantity input {
    width: 80px;
    text-align: center;
}

.notification-count {
    color: #666;
    font-size: 0.9rem;
    text-align: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
`;

// Add styles to document
const notificationsStyleSheet = document.createElement('style');
notificationsStyleSheet.textContent = notificationsStyles;
document.head.appendChild(notificationsStyleSheet);
