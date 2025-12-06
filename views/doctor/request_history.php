<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Request History</h1>
        <div class="page-actions">
            <a href="<?php echo getBaseUrl(); ?>doctor_requests.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Request
            </a>
        </div>
    </div>
    
    <!-- Statistics Overview -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_requests; ?></h3>
                <p>Total Requests</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $approved_requests; ?></h3>
                <p>Approved</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $pending_requests; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $rejected_requests; ?></h3>
                <p>Rejected</p>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-container">
        <form method="GET" class="filters-form">
            <div class="search-container">
                <input type="text" name="search" placeholder="Search requests..." 
                       value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <i class="fas fa-search"></i>
            </div>
            
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            
            <select name="date_filter" class="filter-select">
                <option value="">All Time</option>
                <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>This Month</option>
            </select>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-filter"></i> Filter
            </button>
            
            <a href="<?php echo getBaseUrl(); ?>doctor_request_history.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>
    
    <!-- Requests Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Patient</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Requested</th>
                    <th>Approved</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filtered_requests as $request): ?>
                <tr class="priority-<?php echo $request['priority']; ?>">
                    <td>#<?php echo $request['id']; ?></td>
                    <td>
                        <div class="item-info">
                            <strong><?php echo htmlspecialchars($request['item_name']); ?></strong>
                            <?php if ($request['priority'] === 'high'): ?>
                            <span class="priority-badge high">High Priority</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo $request['quantity']; ?></td>
                    <td>
                        <div class="patient-info">
                            <strong><?php echo htmlspecialchars($request['patient_id']); ?></strong><br>
                            <small><?php echo htmlspecialchars($request['patient_name']); ?></small>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                            <?php echo htmlspecialchars($request['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="priority-indicator priority-<?php echo $request['priority']; ?>">
                            <?php echo ucfirst($request['priority']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($request['requested_date'])); ?></td>
                    <td>
                        <?php if ($request['approved_date']): ?>
                            <?php echo date('M j, Y', strtotime($request['approved_date'])); ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <button class="btn btn-sm btn-secondary" onclick="viewRequestDetails(<?php echo $request['id']; ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($request['status'] === 'Pending'): ?>
                        <button class="btn btn-sm btn-warning" onclick="editRequest(<?php echo $request['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="cancelRequest(<?php echo $request['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($filtered_requests)): ?>
                <tr>
                    <td colspan="9" class="no-data">
                        <i class="fas fa-search"></i>
                        <p>No requests found matching your criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Export Options -->
    <div class="export-section">
        <h3>Export Options</h3>
        <div class="export-buttons">
            <button class="btn btn-secondary" onclick="exportToCSV()">
                <i class="fas fa-file-csv"></i> Export to CSV
            </button>
            <button class="btn btn-secondary" onclick="printReport()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button class="btn btn-secondary" onclick="generateSummaryReport()">
                <i class="fas fa-chart-bar"></i> Summary Report
            </button>
        </div>
    </div>
</main>

<!-- Request Details Modal -->
<div id="requestDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Request Details</h2>
            <span class="close" onclick="closeModal('requestDetailsModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="requestDetailsContent">
                <div class="request-details">
                    <div class="detail-section">
                        <h3>Request Information</h3>
                        <div class="detail-row">
                            <label>Request ID:</label>
                            <span id="detail_request_id"></span>
                        </div>
                        <div class="detail-row">
                            <label>Item:</label>
                            <span id="detail_item_name"></span>
                        </div>
                        <div class="detail-row">
                            <label>Quantity:</label>
                            <span id="detail_quantity"></span>
                        </div>
                        <div class="detail-row">
                            <label>Status:</label>
                            <span id="detail_status"></span>
                        </div>
                        <div class="detail-row">
                            <label>Priority:</label>
                            <span id="detail_priority"></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Patient Information</h3>
                        <div class="detail-row">
                            <label>Patient ID:</label>
                            <span id="detail_patient_id"></span>
                        </div>
                        <div class="detail-row">
                            <label>Patient Name:</label>
                            <span id="detail_patient_name"></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Timeline</h3>
                        <div class="detail-row">
                            <label>Requested Date:</label>
                            <span id="detail_requested_date"></span>
                        </div>
                        <div class="detail-row">
                            <label>Approved Date:</label>
                            <span id="detail_approved_date"></span>
                        </div>
                        <div class="detail-row">
                            <label>Approved By:</label>
                            <span id="detail_approved_by"></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Notes</h3>
                        <p id="detail_notes"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('requestDetailsModal')">Close</button>
        </div>
    </div>
</div>

<script>
// Make request data available to JavaScript
const requestHistory = <?php echo json_encode($request_history); ?>;
</script>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/request-history.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


