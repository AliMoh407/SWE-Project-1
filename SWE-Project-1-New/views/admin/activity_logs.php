<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Activity Logs</h1>
        <div class="page-actions">
            <div class="search-container">
                <input type="text" id="searchLogs" placeholder="Search activities..." class="search-input">
                <i class="fas fa-search"></i>
            </div>
            <select id="filterStatus" class="filter-select">
                <option value="">All Status</option>
                <option value="Completed">Completed</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
    </div>
    
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($activity_logs); ?></h3>
                <p>Total Activities</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($activity_logs, function($log) { return $log['status'] === 'Completed'; })); ?></h3>
                <p>Completed</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_filter($activity_logs, function($log) { return $log['status'] === 'Pending'; })); ?></h3>
                <p>Pending</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_unique(array_column($activity_logs, 'user'))); ?></h3>
                <p>Active Users</p>
            </div>
        </div>
    </div>
    
    <div class="table-container">
        <table class="data-table" id="activityTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activity_logs as $log): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td><?php echo htmlspecialchars($log['user']); ?></td>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($log['timestamp'])); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($log['status']); ?>">
                            <?php echo htmlspecialchars($log['status']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="showActivityDetails(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Activity Details Modal -->
<div id="activityDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Activity Details</h2>
            <span class="close" onclick="closeModal('activityDetailsModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="activity-details">
                <div class="detail-row">
                    <label>Activity ID:</label>
                    <span id="detail_id"></span>
                </div>
                <div class="detail-row">
                    <label>User:</label>
                    <span id="detail_user"></span>
                </div>
                <div class="detail-row">
                    <label>Action:</label>
                    <span id="detail_action"></span>
                </div>
                <div class="detail-row">
                    <label>Timestamp:</label>
                    <span id="detail_timestamp"></span>
                </div>
                <div class="detail-row">
                    <label>Status:</label>
                    <span id="detail_status"></span>
                </div>
                <div class="detail-row">
                    <label>Additional Info:</label>
                    <p id="detail_info">No additional information available.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('activityDetailsModal')">Close</button>
        </div>
    </div>
</div>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/activity-logs.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


