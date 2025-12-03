// Doctor Requests JavaScript

let inventoryData = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeDoctorRequests();
});

function initializeDoctorRequests() {
    console.log('Doctor requests initialized');
    
    // Initialize search functionality
    const itemSearch = document.getElementById('item_search');
    if (itemSearch) {
        itemSearch.addEventListener('input', handleItemSearch);
    }
    
    // Initialize availability search
    const availabilitySearch = document.getElementById('availability_search');
    if (availabilitySearch) {
        availabilitySearch.addEventListener('input', handleAvailabilitySearch);
    }
    
    // Load inventory data if available
    if (typeof inventoryData !== 'undefined') {
        inventoryData = window.inventoryData || [];
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
        resultsContainer.innerHTML = '<div class="no-results">No items found</div>';
        resultsContainer.style.display = 'block';
        return;
    }
    
    const resultsHTML = filteredItems.map(item => `
        <div class="search-result-item" onclick="selectItem(${item.id})">
            <div class="item-name">${item.name}</div>
            <div class="item-details">
                <span class="item-category">${item.category}</span>
                <span class="item-stock ${item.stock <= item.min_stock ? 'low-stock' : 'normal-stock'}">Stock: ${item.stock}</span>
                ${item.controlled ? '<span class="controlled-badge">Controlled</span>' : ''}
            </div>
        </div>
    `).join('');
    
    resultsContainer.innerHTML = resultsHTML;
    resultsContainer.style.display = 'block';
}

function selectItem(itemId) {
    const item = inventoryData.find(i => i.id == itemId);
    if (!item) return;
    
    // Update form fields
    document.getElementById('selected_item').value = item.name;
    document.getElementById('item_id').value = item.id;
    
    // Hide search results
    document.getElementById('search_results').style.display = 'none';
    document.getElementById('item_search').value = '';
    
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
    
    // Validate item selection
    if (!itemId.value) {
        showAlert('Please select an item from the search results', 'error');
        isValid = false;
    }
    
    // Validate quantity
    if (!quantity.value || quantity.value <= 0) {
        showFieldError(quantity, 'Please enter a valid quantity');
        isValid = false;
    } else {
        clearFieldError(quantity);
    }
    
    // Validate patient ID
    if (!patientId.value.trim()) {
        showFieldError(patientId, 'Patient ID is required');
        isValid = false;
    } else {
        clearFieldError(patientId);
    }
    
    // Validate patient name
    if (!patientName.value.trim()) {
        showFieldError(patientName, 'Patient name is required');
        isValid = false;
    } else {
        clearFieldError(patientName);
    }
    
    return isValid;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const requestForm = document.getElementById('requestForm');
    
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateRequestForm(this)) {
                const submitBtn = this.querySelector('button[type="submit"]');
                showLoading(submitBtn);
                
                // Simulate API call
                setTimeout(() => {
                    hideLoading(submitBtn);
                    this.submit();
                }, 1000);
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
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 5px 5px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-result-item {
    padding: 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.item-name {
    font-weight: 500;
    color: #333;
    margin-bottom: 0.25rem;
}

.item-details {
    display: flex;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #666;
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

.no-results {
    padding: 1rem;
    text-align: center;
    color: #666;
    font-style: italic;
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
