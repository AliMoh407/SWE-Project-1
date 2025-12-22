// Doctor Requests JavaScript

let inventoryData = [];

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    initializeDoctorRequests();
});

function initializeDoctorRequests() {
    // Load inventory data if available
    if (typeof window.inventoryData !== 'undefined') {
        inventoryData = window.inventoryData;
    } else {
        inventoryData = [];
    }
    
    // Initialize search functionality
    const itemSearch = document.getElementById('item_search');
    if (itemSearch) {
        itemSearch.addEventListener('input', handleItemSearch);
        itemSearch.addEventListener('focus', function() {
            if (this.value.length >= 2) {
                handleItemSearch({ target: this });
            }
        });
    }
    
    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        const searchContainer = document.querySelector('.form-group');
        const searchResults = document.getElementById('search_results');
        if (searchContainer && searchResults && !searchContainer.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Initialize availability search
    const availabilitySearch = document.getElementById('availability_search');
    if (availabilitySearch) {
        availabilitySearch.addEventListener('input', handleAvailabilitySearch);
    }
}

function handleItemSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const resultsContainer = document.getElementById('search_results');
    
    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        return;
    }
    
    const filteredItems = inventoryData.filter(item => 
        item.name.toLowerCase().includes(searchTerm) ||
        item.category.toLowerCase().includes(searchTerm)
    );
    
    if (filteredItems.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results"><i class="fas fa-search"></i><p>No items found matching your search</p></div>';
        resultsContainer.style.display = 'block';
        return;
    }
    
    const resultsHTML = filteredItems.map(item => `
        <div class="search-result-item" onclick="selectItem(${item.id})">
            <div class="item-name">${escapeHtml(item.name)}</div>
            <div class="item-details">
                <span class="item-category">${escapeHtml(item.category)}</span>
                <span class="item-stock ${item.stock <= item.min_stock ? 'low-stock' : 'normal-stock'}">
                    <i class="fas fa-box"></i> Stock: ${item.stock}
                </span>
                ${item.controlled ? '<span class="controlled-badge"><i class="fas fa-shield-alt"></i> Controlled</span>' : ''}
            </div>
        </div>
    `).join('');
    
    resultsContainer.innerHTML = resultsHTML;
    resultsContainer.style.display = 'block';
}

function selectItem(itemId) {
    const item = inventoryData.find(i => i.id == itemId);
    if (!item) {
        console.error('Item not found:', itemId);
        showAlert('Item not found. Please try again.', 'error');
        return;
    }
    
    // Update form fields
    const selectedItemInput = document.getElementById('selected_item');
    const itemIdInput = document.getElementById('item_id');
    
    if (selectedItemInput) {
        selectedItemInput.value = item.name;
    }
    if (itemIdInput) {
        itemIdInput.value = item.id;
    }
    
    // Hide search results
    const searchResults = document.getElementById('search_results');
    if (searchResults) {
        searchResults.style.display = 'none';
    }
    const itemSearch = document.getElementById('item_search');
    if (itemSearch) {
        itemSearch.value = '';
    }
    
    // Check if item is controlled
    if (item.controlled) {
        showControlledWarning(item);
    }
}

function showControlledWarning(item) {
    document.getElementById('controlled_item_name').textContent = item.name;
    showModal('controlledWarningModal');
}

function proceedWithControlledRequest() {
    closeModal('controlledWarningModal');
    // Continue with form submission
}

function handleAvailabilitySearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const resultsContainer = document.getElementById('availability_results');
    
    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        return;
    }
    
    const filteredItems = inventoryData.filter(item => 
        item.name.toLowerCase().includes(searchTerm) ||
        item.category.toLowerCase().includes(searchTerm)
    );
    
    if (filteredItems.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results">No items found</div>';
        return;
    }
    
    const resultsHTML = filteredItems.map(item => `
        <div class="availability-item">
            <div class="item-info">
                <h4>${item.name}</h4>
                <p class="item-category">${item.category}</p>
            </div>
            <div class="availability-status">
                <span class="stock-amount ${item.stock <= item.min_stock ? 'low-stock' : 'normal-stock'}">
                    ${item.stock} units
                </span>
                <span class="min-stock">Min: ${item.min_stock}</span>
                ${item.controlled ? '<span class="controlled-badge">Controlled</span>' : ''}
            </div>
            <div class="availability-actions">
                ${item.stock > 0 ? 
                    '<button class="btn btn-sm btn-primary" onclick="quickRequest(' + item.id + ')">Request</button>' :
                    '<span class="unavailable">Out of Stock</span>'
                }
            </div>
        </div>
    `).join('');
    
    resultsContainer.innerHTML = resultsHTML;
}

function quickRequest(itemId) {
    const item = inventoryData.find(i => i.id == itemId);
    if (!item) return;
    
    // Pre-fill the request form
    document.getElementById('selected_item').value = item.name;
    document.getElementById('item_id').value = item.id;
    
    // Focus on quantity field
    document.getElementById('quantity').focus();
    
    // Scroll to form
    document.querySelector('.request-form-container').scrollIntoView({ behavior: 'smooth' });
}

function checkAvailability() {
    const searchInput = document.getElementById('availability_search');
    if (searchInput.value.trim()) {
        handleAvailabilitySearch({ target: searchInput });
    }
}

// Form validation for requests
function validateRequestForm(form) {
    const itemId = form.querySelector('input[name="item_id"]');
    const quantity = form.querySelector('input[name="quantity"]');
    const patientId = form.querySelector('input[name="patient_id"]');
    const patientName = form.querySelector('input[name="patient_name"]');
    
    let isValid = true;
    const errors = [];
    
    // Validate item selection
    if (!itemId || !itemId.value || itemId.value.trim() === '') {
        errors.push('Please select an item from the search results');
        isValid = false;
        if (itemId) {
            showFieldError(itemId, 'Please select an item');
        }
    } else {
        if (itemId) clearFieldError(itemId);
    }
    
    // Validate quantity
    if (!quantity || !quantity.value || parseInt(quantity.value) <= 0) {
        errors.push('Please enter a valid quantity');
        isValid = false;
        if (quantity) {
            showFieldError(quantity, 'Please enter a valid quantity (greater than 0)');
        }
    } else {
        if (quantity) clearFieldError(quantity);
    }
    
    // Validate patient ID
    if (!patientId || !patientId.value.trim()) {
        errors.push('Patient ID is required');
        isValid = false;
        if (patientId) {
            showFieldError(patientId, 'Patient ID is required');
        }
    } else {
        if (patientId) clearFieldError(patientId);
    }
    
    // Validate patient name
    if (!patientName || !patientName.value.trim()) {
        errors.push('Patient name is required');
        isValid = false;
        if (patientName) {
            showFieldError(patientName, 'Patient name is required');
        }
    } else {
        if (patientName) clearFieldError(patientName);
    }
    
    if (!isValid && errors.length > 0) {
        showAlert(errors.join('. '), 'error');
    }
    
    return isValid;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const requestForm = document.getElementById('requestForm');
    
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            // Only prevent default if validation fails
            if (!validateRequestForm(this)) {
                e.preventDefault();
                return false;
            }
            
            // If validation passes, allow form to submit normally
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                showLoading(submitBtn);
            }
        });
    }
});

function clearForm() {
    document.getElementById('requestForm').reset();
    document.getElementById('selected_item').value = '';
    document.getElementById('item_id').value = '';
    document.getElementById('search_results').style.display = 'none';
    document.getElementById('availability_results').innerHTML = '';
}

function showRequestForm() {
    document.querySelector('.request-form-container').scrollIntoView({ behavior: 'smooth' });
    document.getElementById('item_search').focus();
}

// Request history management
function viewRequestDetails(requestId) {
    const request = window.requestHistory ? 
        window.requestHistory.find(r => r.id == requestId) : null;
    
    if (!request) {
        showAlert('Request details not found', 'error');
        return;
    }
    
    const content = `
        <div class="request-details">
            <div class="detail-section">
                <h3>Request Information</h3>
                <div class="detail-row">
                    <label>Request ID:</label>
                    <span>#${request.id}</span>
                </div>
                <div class="detail-row">
                    <label>Item:</label>
                    <span>${request.item_name}</span>
                </div>
                <div class="detail-row">
                    <label>Quantity:</label>
                    <span>${request.quantity}</span>
                </div>
                <div class="detail-row">
                    <label>Status:</label>
                    <span class="status-badge status-${request.status.toLowerCase()}">${request.status}</span>
                </div>
                <div class="detail-row">
                    <label>Priority:</label>
                    <span class="priority-indicator priority-${request.priority}">${request.priority}</span>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>Patient Information</h3>
                <div class="detail-row">
                    <label>Patient ID:</label>
                    <span>${request.patient_id}</span>
                </div>
                <div class="detail-row">
                    <label>Patient Name:</label>
                    <span>${request.patient_name}</span>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>Timeline</h3>
                <div class="detail-row">
                    <label>Requested Date:</label>
                    <span>${formatDate(request.requested_date)}</span>
                </div>
                <div class="detail-row">
                    <label>Approved Date:</label>
                    <span>${request.approved_date ? formatDate(request.approved_date) : 'Pending'}</span>
                </div>
                <div class="detail-row">
                    <label>Approved By:</label>
                    <span>${request.approved_by || 'Pending'}</span>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>Notes</h3>
                <p>${request.notes || 'No additional notes'}</p>
            </div>
        </div>
    `;
    
    document.getElementById('requestDetailsContent').innerHTML = content;
    showModal('requestDetailsModal');
}

function editRequest(requestId) {
    // This would typically open an edit form
    showAlert('Edit functionality would be implemented here', 'info');
}

function cancelRequest(requestId) {
    confirmAction('Are you sure you want to cancel this request?', function() {
        // Simulate cancel request
        showAlert('Request cancelled successfully', 'success');
        
        // Update the table row
        const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
        if (row) {
            const statusCell = row.querySelector('.status-badge');
            statusCell.textContent = 'Cancelled';
            statusCell.className = 'status-badge status-cancelled';
        }
    });
}

// Add CSS for doctor requests
const doctorRequestsStyles = `
.form-group {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 5px 5px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-top: -1px;
}

.search-result-item {
    padding: 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.search-result-item:hover {
    background: #f8f9fa;
    border-left: 3px solid #667eea;
    padding-left: calc(1rem - 3px);
}

.search-result-item:last-child {
    border-bottom: none;
}

.item-name {
    font-weight: 600;
    color: #333;
    font-size: 1rem;
    margin: 0;
    line-height: 1.4;
}

.item-details {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    font-size: 0.875rem;
    color: #666;
}

.item-category {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.8rem;
    white-space: nowrap;
}

.item-stock {
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
}

.item-stock.low-stock {
    color: #721c24;
    background: #f8d7da;
}

.item-stock.normal-stock {
    color: #155724;
    background: #d4edda;
}

.item-stock i {
    font-size: 0.75rem;
}

.controlled-badge {
    background: #fff3cd;
    color: #856404;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
}

.controlled-badge i {
    font-size: 0.7rem;
}

.no-results {
    padding: 2rem 1rem;
    text-align: center;
    color: #666;
}

.no-results i {
    font-size: 2rem;
    color: #ccc;
    margin-bottom: 0.5rem;
    display: block;
}

.no-results p {
    margin: 0;
    font-style: italic;
}

.availability-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    margin-bottom: 0.5rem;
}

.availability-item .item-info {
    flex: 1;
}

.availability-item .item-info h4 {
    margin: 0 0 0.25rem 0;
    color: #333;
}

.availability-item .item-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.availability-status {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.availability-actions {
    display: flex;
    align-items: center;
}

.unavailable {
    color: #dc3545;
    font-weight: 500;
}

.request-form-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-header h2 {
    color: #333;
    margin-bottom: 0.5rem;
}

.form-header p {
    color: #666;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.recent-requests {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.availability-check {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.availability-form {
    display: flex;
    gap: 1rem;
    align-items: end;
    margin-bottom: 1rem;
}

.availability-results {
    max-height: 300px;
    overflow-y: auto;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}
`;

// Add styles to document
const doctorRequestsStyleSheet = document.createElement('style');
doctorRequestsStyleSheet.textContent = doctorRequestsStyles;
document.head.appendChild(doctorRequestsStyleSheet);
