<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Manage Doctor Requests</h1>
        <p class="subtitle">Review and approve/reject controlled medicine requests from doctors</p>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Filter by Status:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="">All Requests</option>
                    <option value="Pending" <?php echo ($status_filter ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo ($status_filter ?? '') === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo ($status_filter ?? '') === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" placeholder="Search by patient name, ID, or item name..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Pending Requests (Priority) -->
    <?php if (!empty($pending_requests)): ?>
    <div class="section">
        <h2 class="section-title">
            <i class="fas fa-exclamation-triangle"></i> Pending Requests (<?php echo count($pending_requests); ?>)
        </h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Doctor</th>
                        <th>Patient</th>
                        <th>Requested Date</th>
                        <th>Priority</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_requests as $request): ?>
                    <?php 
                    $item = $inventoryModel->findById($request['item_id']);
                    $available_stock = $item ? $item['stock'] : 0;
                    $can_approve = $available_stock >= $request['quantity'];
                    ?>
                    <tr class="<?php echo !$can_approve ? 'low-stock-row' : ''; ?>">
                        <td>#<?php echo $request['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($request['item_name']); ?></strong>
                            <?php if ($item): ?>
                            <br><small class="text-muted">Stock: <?php echo $item['stock']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $request['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($request['doctor_name'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="patient-info">
                                <strong><?php echo htmlspecialchars($request['patient_id']); ?></strong><br>
                                <small><?php echo htmlspecialchars($request['patient_name']); ?></small>
                            </div>
                        </td>
                        <td><?php echo date('M j, Y H:i', strtotime($request['requested_date'])); ?></td>
                        <td>
                            <span class="priority-badge priority-<?php echo strtolower($request['priority']); ?>">
                                <?php echo htmlspecialchars($request['priority']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($request['notes'] ?? 'N/A'); ?></td>
                        <td class="actions">
                            <?php if ($can_approve): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this request? Stock will be reduced.');">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success" title="Approve Request">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-danger" title="Insufficient stock">
                                <i class="fas fa-exclamation-circle"></i> Low Stock
                            </span>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Reject Request">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Requests -->
    <div class="section">
        <h2 class="section-title">All Requests</h2>
        <div class="table-container">
            <?php if (empty($all_requests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No requests found.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Doctor</th>
                        <th>Patient</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th>Approved Date</th>
                        <th>Priority</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_requests as $request): ?>
                    <tr>
                        <td>#<?php echo $request['id']; ?></td>
                        <td><?php echo htmlspecialchars($request['item_name']); ?></td>
                        <td><?php echo $request['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($request['doctor_name'] ?? 'N/A'); ?></td>
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
                        <td><?php echo date('M j, Y H:i', strtotime($request['requested_date'])); ?></td>
                        <td>
                            <?php if ($request['approved_date']): ?>
                                <?php echo date('M j, Y H:i', strtotime($request['approved_date'])); ?>
                                <?php if ($request['approved_by']): ?>
                                    <br><small class="text-muted">by <?php echo htmlspecialchars($request['approved_by']); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="priority-badge priority-<?php echo strtolower($request['priority']); ?>">
                                <?php echo htmlspecialchars($request['priority']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($request['notes'] ?? 'N/A'); ?></td>
                        <td class="actions">
                            <?php if ($request['status'] === 'Pending'): ?>
                            <?php 
                            $item = $inventoryModel->findById($request['item_id']);
                            $available_stock = $item ? $item['stock'] : 0;
                            $can_approve = $available_stock >= $request['quantity'];
                            ?>
                            <?php if ($can_approve): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this request? Stock will be reduced.');">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success" title="Approve Request">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-danger" title="Insufficient stock">
                                <i class="fas fa-exclamation-circle"></i>
                            </span>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Reject Request">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

