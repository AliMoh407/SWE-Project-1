// Main JavaScript file for common functionality

// Global variables
let searchTimeout;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializeModals();
    initializeSearch();
    initializeAlerts();
});

// Tooltip initialization
function initializeTooltips() {
    // Add tooltips to buttons with title attributes
    const tooltipElements = document.querySelectorAll('[title]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = element.getAttribute('title');
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    // Remove title to prevent default tooltip
    element.setAttribute('data-title', element.getAttribute('title'));
    element.removeAttribute('title');
}

function hideTooltip(event) {
    const element = event.target;
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
    
    // Restore title
    const dataTitle = element.getAttribute('data-title');
    if (dataTitle) {
        element.setAttribute('title', dataTitle);
        element.removeAttribute('data-title');
    }
}

// Modal functionality
function initializeModals() {
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first input in modal
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Clear form if it's a form modal
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this);
            }, 300);
        });
    });
}

function performSearch(input) {
    const searchTerm = input.value.toLowerCase();
    const table = input.closest('.main-content').querySelector('.data-table');
    
    if (table) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

// Alert management
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-hide success alerts after 5 seconds
        if (alert.classList.contains('alert-success')) {
            setTimeout(() => {
                hideAlert(alert);
            }, 5000);
        }
        
        // Add close button to alerts
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => hideAlert(alert);
            alert.appendChild(closeBtn);
        }
    });
}

function hideAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateX(100%)';
    setTimeout(() => {
        alert.remove();
    }, 300);
}

function showAlert(message, type = 'info', duration = 5000) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)}"></i>
        ${message}
        <button class="alert-close" onclick="hideAlert(this.parentElement)">&times;</button>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alert, container.firstChild);
    
    if (duration > 0) {
        setTimeout(() => hideAlert(alert), duration);
    }
    
    return alert;
}

function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });
    
    // Email validation
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            showFieldError(input, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Password validation
    const passwordInputs = form.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.value && input.value.length < 6) {
            showFieldError(input, 'Password must be at least 6 characters');
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(input, message) {
    clearFieldError(input);
    
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    
    input.classList.add('error');
    input.parentNode.appendChild(error);
}

function clearFieldError(input) {
    input.classList.remove('error');
    const existingError = input.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Print functionality
function printReport() {
    window.print();
}

// Export functionality
function exportToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId) || document.querySelector('.data-table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(cell => {
            let text = cell.textContent.trim();
            // Escape quotes and wrap in quotes if contains comma
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                text = '"' + text.replace(/"/g, '""') + '"';
            }
            return text;
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    
    window.URL.revokeObjectURL(url);
}

// Loading states
function showLoading(element) {
    element.classList.add('loading');
    element.disabled = true;
    
    const originalText = element.textContent;
    element.setAttribute('data-original-text', originalText);
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
}

function hideLoading(element) {
    element.classList.remove('loading');
    element.disabled = false;
    
    const originalText = element.getAttribute('data-original-text');
    if (originalText) {
        element.textContent = originalText;
        element.removeAttribute('data-original-text');
    }
}

// Confirmation dialogs
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Add CSS for additional styles
const additionalStyles = `
.tooltip {
    position: absolute;
    background: #333;
    color: white;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    z-index: 1000;
    pointer-events: none;
}

.tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
}

.field-error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
    border-color: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
    margin-left: auto;
}

.alert-close:hover {
    opacity: 1;
}

.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
`;

// Add styles to document
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);
