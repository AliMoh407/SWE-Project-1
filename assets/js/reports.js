// Reports JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeReports();
});

function initializeReports() {
    console.log('Reports initialized');
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Initialize print and export functionality
    initializeExportFunctions();
}

function initializeCharts() {
    // Role Distribution Chart
    const roleCtx = document.getElementById('roleChart');
    if (roleCtx) {
        const roleData = {
            labels: ['Admin', 'Doctor', 'Pharmacist', 'Patient'],
            datasets: [{
                data: [1, 1, 1, 1], // These would come from your PHP data
                backgroundColor: [
                    '#dc3545',
                    '#007bff',
                    '#28a745',
                    '#6c757d'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };
        
        new Chart(roleCtx, {
            type: 'doughnut',
            data: roleData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Inventory Categories Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryData = {
            labels: ['Pain Relief', 'Medical Equipment', 'Diabetes Care', 'Medical Supplies'],
            datasets: [{
                label: 'Items',
                data: [3, 1, 1, 1], // These would come from your PHP data
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        };
        
        new Chart(categoryCtx, {
            type: 'bar',
            data: categoryData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}

function initializeExportFunctions() {
    // Print functionality
    window.printReport = function() {
        window.print();
    };
    
    // Export functionality
    window.exportReport = function() {
        const reportData = collectReportData();
        exportReportToCSV(reportData);
    };
}

function collectReportData() {
    const data = {
        timestamp: new Date().toLocaleString(),
        overview: {
            totalUsers: document.querySelector('.stat-card:nth-child(1) h3')?.textContent || '0',
            totalInventory: document.querySelector('.stat-card:nth-child(2) h3')?.textContent || '0',
            lowStockItems: document.querySelector('.stat-card:nth-child(3) h3')?.textContent || '0',
            expiringSoon: document.querySelector('.stat-card:nth-child(4) h3')?.textContent || '0'
        },
        lowStockItems: [],
        expiringItems: []
    };
    
    // Collect low stock items
    const lowStockTable = document.querySelector('.report-section:nth-child(4) .data-table');
    if (lowStockTable) {
        const rows = lowStockTable.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 5) {
                data.lowStockItems.push({
                    name: cells[0].textContent.trim(),
                    category: cells[1].textContent.trim(),
                    currentStock: cells[2].textContent.trim(),
                    minimumStock: cells[3].textContent.trim(),
                    status: cells[4].textContent.trim()
                });
            }
        });
    }
    
    // Collect expiring items
    const expiringTable = document.querySelector('.report-section:nth-child(5) .data-table');
    if (expiringTable) {
        const rows = expiringTable.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 5) {
                data.expiringItems.push({
                    name: cells[0].textContent.trim(),
                    category: cells[1].textContent.trim(),
                    expiryDate: cells[2].textContent.trim(),
                    daysRemaining: cells[3].textContent.trim(),
                    status: cells[4].textContent.trim()
                });
            }
        });
    }
    
    return data;
}

function exportReportToCSV(reportData) {
    let csvContent = 'Medical Inventory System Report\n';
    csvContent += `Generated: ${reportData.timestamp}\n\n`;
    
    // Overview section
    csvContent += 'OVERVIEW STATISTICS\n';
    csvContent += `Total Users,${reportData.overview.totalUsers}\n`;
    csvContent += `Total Inventory Items,${reportData.overview.totalInventory}\n`;
    csvContent += `Low Stock Items,${reportData.overview.lowStockItems}\n`;
    csvContent += `Items Expiring Soon,${reportData.overview.expiringSoon}\n\n`;
    
    // Low stock items section
    if (reportData.lowStockItems.length > 0) {
        csvContent += 'LOW STOCK ITEMS\n';
        csvContent += 'Item Name,Category,Current Stock,Minimum Stock,Status\n';
        reportData.lowStockItems.forEach(item => {
            csvContent += `"${item.name}","${item.category}",${item.currentStock},${item.minimumStock},"${item.status}"\n`;
        });
        csvContent += '\n';
    }
    
    // Expiring items section
    if (reportData.expiringItems.length > 0) {
        csvContent += 'ITEMS EXPIRING SOON\n';
        csvContent += 'Item Name,Category,Expiry Date,Days Remaining,Status\n';
        reportData.expiringItems.forEach(item => {
            csvContent += `"${item.name}","${item.category}","${item.expiryDate}",${item.daysRemaining},"${item.status}"\n`;
        });
    }
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `inventory-report-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Report exported successfully', 'success');
}

function generateSummaryReport() {
    const reportData = collectReportData();
    
    let summaryContent = `
        MEDICAL INVENTORY SYSTEM - SUMMARY REPORT
        Generated: ${reportData.timestamp}
        
        ========================================
        
        OVERVIEW STATISTICS:
        - Total Users: ${reportData.overview.totalUsers}
        - Total Inventory Items: ${reportData.overview.totalInventory}
        - Low Stock Items: ${reportData.overview.lowStockItems}
        - Items Expiring Soon: ${reportData.overview.expiringSoon}
        
        ========================================
    `;
    
    if (reportData.lowStockItems.length > 0) {
        summaryContent += `
        
        LOW STOCK ITEMS (${reportData.lowStockItems.length}):
        `;
        reportData.lowStockItems.forEach(item => {
            summaryContent += `
        - ${item.name} (${item.category}): ${item.currentStock} units (Min: ${item.minimumStock})`;
        });
    }
    
    if (reportData.expiringItems.length > 0) {
        summaryContent += `
        
        ITEMS EXPIRING SOON (${reportData.expiringItems.length}):
        `;
        reportData.expiringItems.forEach(item => {
            summaryContent += `
        - ${item.name} (${item.category}): Expires ${item.expiryDate} (${item.daysRemaining} days)`;
        });
    }
    
    summaryContent += `
        
        ========================================
        
        RECOMMENDATIONS:
    `;
    
    if (reportData.overview.lowStockItems > 0) {
        summaryContent += `
        - Consider reordering ${reportData.overview.lowStockItems} low stock items
        - Review minimum stock levels for better inventory management
        `;
    }
    
    if (reportData.overview.expiringSoon > 0) {
        summaryContent += `
        - Monitor ${reportData.overview.expiringSoon} items expiring within 30 days
        - Consider using expiring items first or adjusting ordering quantities
        `;
    }
    
    if (reportData.overview.lowStockItems == 0 && reportData.overview.expiringSoon == 0) {
        summaryContent += `
        - Inventory levels are optimal
        - No immediate action required
        `;
    }
    
    // Display summary in modal
    showSummaryModal(summaryContent);
}

function showSummaryModal(content) {
    const modal = document.createElement('div');
    modal.className = 'modal show';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Summary Report</h2>
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
            <title>Inventory Summary Report</title>
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
    link.download = `inventory-summary-${new Date().toISOString().split('T')[0]}.txt`;
    link.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Summary report downloaded successfully', 'success');
}

function refreshReportData() {
    showAlert('Refreshing report data...', 'info');
    
    // Simulate API call to refresh data
    setTimeout(() => {
        showAlert('Report data refreshed', 'success');
        // In a real application, you would reload the page or update the data via AJAX
        location.reload();
    }, 1000);
}

function filterReportByDate() {
    const dateInput = document.getElementById('reportDate');
    if (!dateInput) return;
    
    const selectedDate = dateInput.value;
    if (!selectedDate) return;
    
    // Filter data based on selected date
    showAlert(`Filtering report for ${selectedDate}...`, 'info');
    
    // In a real application, you would filter the data and update the display
    setTimeout(() => {
        showAlert('Report filtered successfully', 'success');
    }, 1000);
}

function exportToPDF() {
    showAlert('PDF export functionality would be implemented here', 'info');
    
    // In a real application, you might use libraries like jsPDF or html2pdf
    // For now, we'll use the browser's print function
    setTimeout(() => {
        window.print();
    }, 1000);
}

function scheduleReport() {
    showAlert('Report scheduling functionality would be implemented here', 'info');
    
    // In a real application, this would open a modal to configure scheduled reports
    // For demo purposes, we'll just show a message
}

function customizeReport() {
    showAlert('Report customization functionality would be implemented here', 'info');
    
    // In a real application, this would open a modal to customize report parameters
    // For demo purposes, we'll just show a message
}

// Add CSS for reports
const reportsStyles = `
.chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 2rem;
}

.report-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.report-section {
    page-break-inside: avoid;
}

@media print {
    .report-actions,
    .page-actions,
    .navigation,
    .header {
        display: none !important;
    }
    
    .main-content {
        padding: 0;
        margin: 0;
    }
    
    .report-section {
        margin-bottom: 2rem;
        break-inside: avoid;
    }
    
    .stat-card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem;
    }
}

.role-stats,
.category-stats {
    margin-top: 1rem;
}

.role-stat,
.category-stat {
    margin-bottom: 0.75rem;
}

.controlled-summary {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 5px;
    padding: 1.5rem;
}

.controlled-summary .summary-stat {
    text-align: center;
    margin-bottom: 1rem;
}

.controlled-summary .summary-stat h3 {
    font-size: 2rem;
    color: #856404;
    margin-bottom: 0.5rem;
}

.controlled-summary .summary-info {
    color: #856404;
}

.controlled-summary .summary-info p {
    margin-bottom: 0.5rem;
}

.controlled-summary .summary-info p:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .report-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .chart-container {
        height: 250px;
    }
    
    .role-stats,
    .category-stats {
        font-size: 0.9rem;
    }
}
`;

// Add styles to document
const reportsStyleSheet = document.createElement('style');
reportsStyleSheet.textContent = reportsStyles;
document.head.appendChild(reportsStyleSheet);
