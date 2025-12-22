// Activity Logs JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeActivityLogs();
});

function initializeActivityLogs() {
    // Initialize search functionality
    const searchInput = document.getElementById('searchLogs');
    if (searchInput) {
        searchInput.addEventListener('input', handleActivitySearch);
    }
    
    // Initialize status filter
    const statusFilter = document.getElementById('filterStatus');
    if (statusFilter) {
        statusFilter.addEventListener('change', handleStatusFilter);
    }
    
    // Initialize date range picker if available
    initializeDateRangePicker();
}

function handleActivitySearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const table = document.getElementById('activityTable');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    updateActivityCount();
}

function handleStatusFilter(event) {
    const statusFilter = event.target.value.toLowerCase();
    const table = document.getElementById('activityTable');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase();
            if (statusFilter === '' || status.includes(statusFilter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    updateActivityCount();
}

function updateActivityCount() {
    const visibleRows = document.querySelectorAll('#activityTable tbody tr[style=""]').length;
    const totalRows = document.querySelectorAll('#activityTable tbody tr').length;
    
    const countElement = document.querySelector('.activity-count');
    if (countElement) {
        countElement.textContent = `Showing ${visibleRows} of ${totalRows} activities`;
    }
}

function showActivityDetails(log) {
    // Populate the details modal
    document.getElementById('detail_id').textContent = log.id;
    document.getElementById('detail_user').textContent = log.user;
    document.getElementById('detail_action').textContent = log.action;
    document.getElementById('detail_timestamp').textContent = formatDateTime(log.timestamp);
    document.getElementById('detail_status').innerHTML = `<span class="status-badge status-${log.status.toLowerCase()}">${log.status}</span>`;
    
    // Add additional information based on log type
    let additionalInfo = 'No additional information available.';
    
    if (log.action.includes('Requested')) {
        additionalInfo = `
            <p><strong>Request Details:</strong></p>
            <ul>
                <li>Item: ${extractItemFromAction(log.action)}</li>
                <li>Requested by: ${log.user}</li>
                <li>Timestamp: ${formatDateTime(log.timestamp)}</li>
                <li>Status: ${log.status}</li>
            </ul>
        `;
    } else if (log.action.includes('Updated')) {
        additionalInfo = `
            <p><strong>Update Details:</strong></p>
            <ul>
                <li>Item: ${extractItemFromAction(log.action)}</li>
                <li>Updated by: ${log.user}</li>
                <li>Action: ${log.action}</li>
                <li>Timestamp: ${formatDateTime(log.timestamp)}</li>
            </ul>
        `;
    } else if (log.action.includes('Created')) {
        additionalInfo = `
            <p><strong>Creation Details:</strong></p>
            <ul>
                <li>Created by: ${log.user}</li>
                <li>Action: ${log.action}</li>
                <li>Timestamp: ${formatDateTime(log.timestamp)}</li>
                <li>Status: ${log.status}</li>
            </ul>
        `;
    }
    
    document.getElementById('detail_info').innerHTML = additionalInfo;
    
    showModal('activityDetailsModal');
}

function extractItemFromAction(action) {
    // Extract item name from action string
    // This is a simple implementation - you might want to make it more robust
    const match = action.match(/(?:Requested|Updated)\s+(.+?)(?:\s+for|$)/);
    return match ? match[1] : 'Unknown Item';
}

function initializeDateRangePicker() {
    // If you have a date range picker, initialize it here
    const dateRangeInput = document.getElementById('dateRange');
    if (dateRangeInput) {
        dateRangeInput.addEventListener('change', handleDateRangeFilter);
    }
}

function handleDateRangeFilter(event) {
    const dateRange = event.target.value;
    const table = document.getElementById('activityTable');
    
    if (!table || !dateRange) return;
    
    const [startDate, endDate] = dateRange.split(' - ');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const timestampCell = row.querySelector('td:nth-child(4)');
        if (timestampCell) {
            const activityDate = new Date(timestampCell.textContent);
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (activityDate >= start && activityDate <= end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    updateActivityCount();
}

function exportActivityLogs() {
    const table = document.getElementById('activityTable');
    if (table) {
        exportToCSV(table, 'activity-logs.csv');
    }
}

function printActivityReport() {
    window.print();
}

function filterByUser() {
    const userFilter = document.getElementById('userFilter');
    if (!userFilter) return;
    
    const selectedUser = userFilter.value;
    const table = document.getElementById('activityTable');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const userCell = row.querySelector('td:nth-child(2)');
        if (userCell) {
            const userName = userCell.textContent.trim();
            if (selectedUser === '' || userName === selectedUser) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    updateActivityCount();
}

function filterByAction() {
    const actionFilter = document.getElementById('actionFilter');
    if (!actionFilter) return;
    
    const selectedAction = actionFilter.value;
    const table = document.getElementById('activityTable');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const actionCell = row.querySelector('td:nth-child(3)');
        if (actionCell) {
            const action = actionCell.textContent.trim();
            if (selectedAction === '' || action.includes(selectedAction)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    updateActivityCount();
}

function clearAllFilters() {
    // Clear search input
    const searchInput = document.getElementById('searchLogs');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Clear status filter
    const statusFilter = document.getElementById('filterStatus');
    if (statusFilter) {
        statusFilter.value = '';
    }
    
    // Clear date range filter
    const dateRangeInput = document.getElementById('dateRange');
    if (dateRangeInput) {
        dateRangeInput.value = '';
    }
    
    // Clear user filter
    const userFilter = document.getElementById('userFilter');
    if (userFilter) {
        userFilter.value = '';
    }
    
    // Clear action filter
    const actionFilter = document.getElementById('actionFilter');
    if (actionFilter) {
        actionFilter.value = '';
    }
    
    // Show all rows
    const table = document.getElementById('activityTable');
    if (table) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
    
    updateActivityCount();
}

function generateActivityReport() {
    const table = document.getElementById('activityTable');
    if (!table) return;
    
    // Collect visible data
    const visibleRows = table.querySelectorAll('tbody tr[style=""]');
    const reportData = [];
    
    visibleRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            reportData.push({
                id: cells[0].textContent.trim(),
                user: cells[1].textContent.trim(),
                action: cells[2].textContent.trim(),
                timestamp: cells[3].textContent.trim(),
                status: cells[4].textContent.trim()
            });
        }
    });
    
    // Generate report content
    let reportContent = `
        Activity Log Report
        Generated: ${new Date().toLocaleString()}
        Total Activities: ${reportData.length}
        
        ========================================
        
    `;
    
    reportData.forEach(activity => {
        reportContent += `
        ID: ${activity.id}
        User: ${activity.user}
        Action: ${activity.action}
        Timestamp: ${activity.timestamp}
        Status: ${activity.status}
        
        ----------------------------------------
        `;
    });
    
    // Create and download report
    const blob = new Blob([reportContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `activity-report-${new Date().toISOString().split('T')[0]}.txt`;
    link.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Activity report generated successfully', 'success');
}

function refreshActivityLogs() {
    showAlert('Refreshing activity logs...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        showAlert('Activity logs refreshed', 'success');
        // In a real application, you would reload the data from the server
    }, 1000);
}

function getActivityStats() {
    const table = document.getElementById('activityTable');
    if (!table) return {};
    
    const rows = table.querySelectorAll('tbody tr');
    const stats = {
        total: rows.length,
        completed: 0,
        pending: 0,
        approved: 0,
        rejected: 0
    };
    
    rows.forEach(row => {
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase();
            if (status.includes('completed')) stats.completed++;
            else if (status.includes('pending')) stats.pending++;
            else if (status.includes('approved')) stats.approved++;
            else if (status.includes('rejected')) stats.rejected++;
        }
    });
    
    return stats;
}

function updateActivityStats() {
    const stats = getActivityStats();
    
    // Update stat cards
    const totalCard = document.querySelector('.stat-card:nth-child(1) h3');
    const completedCard = document.querySelector('.stat-card:nth-child(2) h3');
    const pendingCard = document.querySelector('.stat-card:nth-child(3) h3');
    const activeUsersCard = document.querySelector('.stat-card:nth-child(4) h3');
    
    if (totalCard) totalCard.textContent = stats.total;
    if (completedCard) completedCard.textContent = stats.completed;
    if (pendingCard) pendingCard.textContent = stats.pending;
    
    // Calculate unique users
    const table = document.getElementById('activityTable');
    if (table && activeUsersCard) {
        const userCells = table.querySelectorAll('tbody tr td:nth-child(2)');
        const uniqueUsers = new Set();
        userCells.forEach(cell => {
            uniqueUsers.add(cell.textContent.trim());
        });
        activeUsersCard.textContent = uniqueUsers.size;
    }
}

// Initialize stats update
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(updateActivityStats, 1000);
});

// Add CSS for activity logs
const activityLogsStyles = `
.activity-count {
    color: #666;
    font-size: 0.9rem;
    text-align: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.activity-details .detail-section {
    margin-bottom: 1.5rem;
}

.activity-details .detail-section h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.activity-details .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-details .detail-row:last-child {
    border-bottom: none;
}

.activity-details .detail-row label {
    font-weight: 500;
    color: #666;
}

.activity-details .detail-row span {
    color: #333;
}

.filter-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.filter-controls .form-group {
    margin-bottom: 0;
}

.filter-controls label {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.filter-controls select,
.filter-controls input {
    min-width: 150px;
}

.activity-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-controls select,
    .filter-controls input {
        min-width: auto;
    }
    
    .activity-actions {
        flex-direction: column;
    }
}
`;

// Add styles to document
const activityLogsStyleSheet = document.createElement('style');
activityLogsStyleSheet.textContent = activityLogsStyles;
document.head.appendChild(activityLogsStyleSheet);
