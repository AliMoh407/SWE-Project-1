<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Request Items</h1>
        <button class="btn btn-primary" onclick="showRequestForm()">
            <i class="fas fa-plus"></i> New Request
        </button>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <!-- Request Form -->
    <div class="request-form-container">
        <div class="form-header">
            <h2>New Item Request</h2>
            <p>Fill out the form below to request medicines or equipment from inventory.</p>
        </div>
        
        <form method="POST" class="request-form" id="requestForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="item_search">Search Item</label>
                    <div class="search-container">
                        <input type="text" id="item_search" placeholder="Type to search items..." autocomplete="off">
                        <i class="fas fa-search"></i>
                    </div>
                    <div id="search_results" class="search-results"></div>
                </div>
                
                <div class="form-group">
                    <label for="selected_item">Selected Item</label>
                    <input type="text" id="selected_item" readonly placeholder="No item selected">
                    <input type="hidden" id="item_id" name="item_id">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required min="1" placeholder="Enter quantity">
                </div>
                
                <div class="form-group">
                    <label for="patient_id">Patient ID</label>
                    <input type="text" id="patient_id" name="patient_id" required placeholder="e.g., P001">
                </div>
            </div>
            
            <div class="form-group">
                <label for="patient_name">Patient Name</label>
                <input type="text" id="patient_name" name="patient_name" required placeholder="Enter patient's full name">
            </div>
            
            <div class="form-group">
                <label for="notes">Notes / Reason for Request</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Enter reason for requesting this item..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear Form</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
    
    <!-- Recent Requests -->
    <div class="recent-requests">
        <h2>Recent Requests</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Patient</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($request_history as $request): ?>
                    <tr>
                        <td>#<?php echo $request['id']; ?></td>
                        <td><?php echo htmlspecialchars($request['item_name']); ?></td>
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
                        <td><?php echo date('M j, Y', strtotime($request['requested_date'])); ?></td>
                        <td><?php echo htmlspecialchars($request['notes']); ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-secondary" onclick="viewRequestDetails(<?php echo $request['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($request['status'] === 'Pending'): ?>
                            <button class="btn btn-sm btn-danger" onclick="cancelRequest(<?php echo $request['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Item Availability Check -->
    <div class="availability-check">
        <h2>Quick Availability Check</h2>
        <div class="availability-form">
            <div class="form-group">
                <label for="availability_search">Check Item Availability</label>
                <div class="search-container">
                    <input type="text" id="availability_search" placeholder="Search for items...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" onclick="checkAvailability()">
                <i class="fas fa-search"></i> Check Availability
            </button>
        </div>
        <div id="availability_results" class="availability-results"></div>
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
                <p>Loading request details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('requestDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Controlled Medicine Warning Modal -->
<div id="controlledWarningModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Controlled Medicine Request</h2>
            <span class="close" onclick="closeModal('controlledWarningModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="warning-content">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <h3>Authorization Required</h3>
                <p>You are requesting a controlled medicine. This request will require additional authorization and may take longer to process.</p>
                <div class="warning-details">
                    <p><strong>Item:</strong> <span id="controlled_item_name"></span></p>
                    <p><strong>Category:</strong> Controlled Substance</p>
                    <p><strong>Requirements:</strong></p>
                    <ul>
                        <li>Valid patient prescription</li>
                        <li>Medical justification</li>
                        <li>Pharmacist approval</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('controlledWarningModal')">Cancel</button>
            <button type="button" class="btn btn-warning" onclick="proceedWithControlledRequest()">
                <i class="fas fa-shield-alt"></i> Proceed with Request
            </button>
        </div>
    </div>
</div>

<script>
// Make inventory data available to JavaScript
window.inventoryData = <?php echo json_encode($inventory ?? []); ?>;
</script>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/doctor-requests.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


