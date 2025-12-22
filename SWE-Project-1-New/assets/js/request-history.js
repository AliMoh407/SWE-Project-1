// Request History JavaScript

let requestHistory = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeRequestHistory();
});

function initializeRequestHistory() {
    // Load request history data if available
    if (typeof window.requestHistory !== 'undefined') {
        requestHistory = window.requestHistory;
    }
    
    // Initialize search and filters
    initializeSearchAndFilters();
}

function initializeSearchAndFilters() {
    const searchInput = document.getElementById('requestSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleRequestSearch);
    }
    
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', handleStatusFilter);
    }
    
    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', handleDateFilter);
    }
}

function handleRequestSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const table = document.querySelector('.data-table');
    
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
    
    updateRequestCount();
}

function handleStatusFilter(event) {
    const statusFilter = event.target.value.toLowerCase();
    const table = document.querySelector('.data-table');
    
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
    
    updateRequestCount();
}

function handleDateFilter(event) {
    const dateFilter = event.target.value;
    const table = document.querySelector('.data-table');
    
    if (!table || !dateFilter) return;
    
    const rows = table.querySelectorAll('tbody tr');
    const today = new Date();
    
    rows.forEach(row => {
        const dateCell = row.querySelector('td:nth-child(7)'); // Requested date column
        if (dateCell) {
            const requestDate = new Date(dateCell.textContent);
            let showRow = true;
            
            switch (dateFilter) {
                case 'today':
                    showRow = requestDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 7);
                    showRow = requestDate >= weekAgo;
                    break;
                case 'month':
                    const monthAgo = new Date(today);
                    monthAgo.setMonth(today.getMonth() - 1);
                    showRow = requestDate >= monthAgo;
                    break;
            }
            
            row.style.display = showRow ? '' : 'none';
        }
    });
    
    updateRequestCount();
}

function updateRequestCount() {
    const visibleRows = document.querySelectorAll('.data-table tbody tr[style=""]').length;
    const totalRows = document.querySelectorAll('.data-table tbody tr').length;
    
    const countElement = document.querySelector('.request-count');
    if (countElement) {
        countElement.textContent = `Showing ${visibleRows} of ${totalRows} requests`;
    }
}

function viewRequestDetails(requestId) {
    const request = requestHistory.find(r => r.id == requestId);
    
    if (!request) {
        showAlert('Request details not found', 'error');
        return;
    }
    
    // Populate the details modal
    document.getElementById('detail_request_id').textContent = request.id;
    document.getElementById('detail_item_name').textContent = request.item_name;
    document.getElementById('detail_quantity').textContent = request.quantity;
    document.getElementById('detail_status').innerHTML = `<span class="status-badge status-${request.status.toLowerCase()}">${request.status}</span>`;
    document.getElementById('detail_priority').innerHTML = `<span class="priority-indicator priority-${request.priority}">${request.priority}</span>`;
    document.getElementById('detail_patient_id').textContent = request.patient_id;
    document.getElementById('detail_patient_name').textContent = request.patient_name;
    document.getElementById('detail_requested_date').textContent = formatDate(request.requested_date);
    document.getElementById('detail_approved_date').textContent = request.approved_date ? formatDate(request.approved_date) : 'Pending';
    document.getElementById('detail_approved_by').textContent = request.approved_by || 'Pending';
    document.getElementById('detail_notes').textContent = request.notes || 'No additional notes';
    
    showModal('requestDetailsModal');
}

function editRequest(requestId) {
    const request = requestHistory.find(r => r.id == requestId);
    
    if (!request) {
        showAlert('Request not found', 'error');
        return;
    }
    
    if (request.status !== 'Pending') {
        showAlert('Only pending requests can be edited', 'warning');
        return;
    }
    
    // In a real application, this would open an edit form
    // For demo purposes, we'll show an alert
    showAlert('Edit request functionality would open here', 'info');
}

function cancelRequest(requestId) {
    const request = requestHistory.find(r => r.id == requestId);
    
    if (!request) {
        showAlert('Request not found', 'error');
        return;
    }
    
    if (request.status !== 'Pending') {
        showAlert('Only pending requests can be cancelled', 'warning');
        return;
    }
    
    confirmAction('Are you sure you want to cancel this request?', function() {
        // Simulate API call
        fetch('/api/requests/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ requestId: requestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the request status in the UI
                updateRequestStatus(requestId, 'Cancelled');
                showAlert('Request cancelled successfully', 'success');
            } else {
                showAlert('Failed to cancel request', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error cancelling request', 'error');
        });
    });
}

function updateRequestStatus(requestId, newStatus) {
    // Update the request in the data
    const request = requestHistory.find(r => r.id == requestId);
    if (request) {
        request.status = newStatus;
    }
    
    // Update the UI
    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (row) {
        const statusCell = row.querySelector('.status-badge');
        if (statusCell) {
            statusCell.textContent = newStatus;
            statusCell.className = `status-badge status-${newStatus.toLowerCase()}`;
        }
        
        // Remove action buttons for non-pending requests
        if (newStatus !== 'Pending') {
            const actionButtons = row.querySelectorAll('.actions .btn-warning, .actions .btn-danger');
            actionButtons.forEach(btn => btn.remove());
        }
    }
}

function exportToCSV() {
    const table = document.querySelector('.data-table');
    if (table) {
        exportToCSV(table, 'request-history.csv');
    }
}

function printReport() {
    window.print();
}

function generateSummaryReport() {
    const stats = getRequestStats();
    
    let summaryContent = `
        REQUEST HISTORY SUMMARY REPORT
        Generated: ${new Date().toLocaleString()}
        
        ========================================
        
        OVERVIEW STATISTICS:
        - Total Requests: ${stats.total}
        - Approved: ${stats.approved}
        - Pending: ${stats.pending}
        - Rejected: ${stats.rejected}
        - Cancelled: ${stats.cancelled}
        
        ========================================
    `;
    
    if (stats.pending > 0) {
        summaryContent += `
        
        PENDING REQUESTS (${stats.pending}):
        `;
        
        const pendingRequests = requestHistory.filter(r => r.status === 'Pending');
        pendingRequests.forEach(request => {
            summaryContent += `
        - Request #${request.id}: ${request.item_name} for ${request.patient_name} (${request.patient_id})`;
        });
    }
    
    if (stats.approved > 0) {
        summaryContent += `
        
        RECENTLY APPROVED REQUESTS:
        `;
        
        const approvedRequests = requestHistory
            .filter(r => r.status === 'Approved')
            .slice(0, 5); // Show last 5 approved
        
        approvedRequests.forEach(request => {
            summaryContent += `
        - Request #${request.id}: ${request.item_name} - Approved on ${formatDate(request.approved_date)}`;
        });
    }
    
    summaryContent += `
        
        ========================================
        
        RECOMMENDATIONS:
    `;
    
    if (stats.pending > 0) {
        summaryContent += `
        - ${stats.pending} requests are pending approval
        - Review pending requests for timely processing
        `;
    }
    
    if (stats.rejected > 0) {
        summaryContent += `
        - ${stats.rejected} requests were rejected
        - Review rejection reasons for process improvement
        `;
    }
    
    if (stats.pending === 0) {
        summaryContent += `
        - All requests have been processed
        - No pending actions required
        `;
    }
    
    // Display summary in modal
    showSummaryModal(summaryContent);
}

function getRequestStats() {
    const stats = {
        total: requestHistory.length,
        approved: 0,
        pending: 0,
        rejected: 0,
        cancelled: 0
    };
    
    requestHistory.forEach(request => {
        switch (request.status.toLowerCase()) {
            case 'approved':
                stats.approved++;
                break;
            case 'pending':
                stats.pending++;
                break;
            case 'rejected':
                stats.rejected++;
                break;
            case 'cancelled':
                stats.cancelled++;
                break;
        }
    });
    
    return stats;
}

function showSummaryModal(content) {
    const modal = document.createElement('div');
    modal.className = 'modal show';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Request History Summary</h2>
                <span class="close" onclick="closeModal('summaryModal')">&times;</span>
            </div>
            <div class="modal-body">
                <pre style="white-space: pre-wrap; font-family: monospace; font-size: 0.9rem; line-height: 1.4;">${content}</pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('summaryModal')">Close</button>
                <button type="button" class="btn btn-primary" onclick="printSummaryReport('${content.replace(/'/g, "\\'")}')">Print</button>
                <button type="button" class="btn btn-success" onclick="downloadSummaryReport('${content.replace(/'/g, "\\'")}')">Download</button>
            </div>
        </div>
    `;
    modal.id = 'summaryModal';
    document.body.appendChild(modal);
}

function printSummaryReport(content) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Request History Summary</title>
            <style>
                body { font-family: monospace; font-size: 12px; line-height: 1.4; margin: 20px; }
                pre { white-space: pre-wrap; }
            </style>
        </head>
        <body>
            <pre>${content}</pre>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function downloadSummaryReport(content) {
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `request-history-summary-${new Date().toISOString().split('T')[0]}.txt`;
    link.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Summary report downloaded successfully', 'success');
}

function clearAllFilters() {
    // Clear search input
    const searchInput = document.getElementById('requestSearch');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Clear status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.value = '';
    }
    
    // Clear date filter
    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.value = '';
    }
    
    // Show all rows
    const table = document.querySelector('.data-table');
    if (table) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
    
    updateRequestCount();
}

function refreshRequestHistory() {
    showAlert('Refreshing request history...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        showAlert('Request history refreshed', 'success');
        // In a real application, you would reload the data
        location.reload();
    }, 1000);
}

// Add CSS for request history
const requestHistoryStyles = `
.request-count {
    color: #666;
    font-size: 0.9rem;
    text-align: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.request-details .detail-section {
    margin-bottom: 1.5rem;
}

.request-details .detail-section h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.request-details .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.request-details .detail-row:last-child {
    border-bottom: none;
}

.request-details .detail-row label {
    font-weight: 500;
    color: #666;
}

.request-details .detail-row span {
    color: #333;
}

.export-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.export-section h3 {
    color: #333;
    margin-bottom: 1rem;
}

.export-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.priority-high {
    color: #dc3545;
}

.priority-normal {
    color: #28a745;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

@media print {
    .page-actions,
    .navigation,
    .header,
    .export-section {
        display: none !important;
    }
    
    .main-content {
        padding: 0;
        margin: 0;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem;
    }
}

@media (max-width: 768px) {
    .export-buttons {
        flex-direction: column;
    }
    
    .filters-form {
        flex-direction: column;
    }
}
`;

// Add styles to document
const requestHistoryStyleSheet = document.createElement('style');
requestHistoryStyleSheet.textContent = requestHistoryStyles;
document.head.appendChild(requestHistoryStyleSheet);
