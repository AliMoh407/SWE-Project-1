// User Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeUserManagement();
});

function initializeUserManagement() {
    // Initialize user management functionality
}

function showCreateUserModal() {
    showModal('createUserModal');
}

function showEditUserModal(user) {
    // Populate form with user data
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    
    showModal('editUserModal');
}

function showDeleteUserModal(userId) {
    document.getElementById('delete_user_id').value = userId;
    showModal('deleteUserModal');
}

// Form validation for user management
function validateUserForm(form) {
    const username = form.querySelector('input[name="username"]');
    const password = form.querySelector('input[name="password"]');
    const name = form.querySelector('input[name="name"]');
    const email = form.querySelector('input[name="email"]');
    const role = form.querySelector('select[name="role"]');
    
    let isValid = true;
    
    // Validate username
    if (!username.value.trim()) {
        showFieldError(username, 'Username is required');
        isValid = false;
    } else if (username.value.length < 3) {
        showFieldError(username, 'Username must be at least 3 characters');
        isValid = false;
    } else {
        clearFieldError(username);
    }
    
    // Validate password (only for create form)
    if (password && password.name === 'password' && form.querySelector('input[name="action"]').value === 'create') {
        if (!password.value.trim()) {
            showFieldError(password, 'Password is required');
            isValid = false;
        } else if (password.value.length < 6) {
            showFieldError(password, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            clearFieldError(password);
        }
    }
    
    // Validate name
    if (!name.value.trim()) {
        showFieldError(name, 'Full name is required');
        isValid = false;
    } else {
        clearFieldError(name);
    }
    
    // Validate email
    if (!email.value.trim()) {
        showFieldError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    } else {
        clearFieldError(email);
    }
    
    // Validate role
    if (!role.value) {
        showFieldError(role, 'Please select a role');
        isValid = false;
    } else {
        clearFieldError(role);
    }
    
    return isValid;
}

// Handle form submissions
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.querySelector('#createUserModal form');
    const editForm = document.querySelector('#editUserModal form');
    const deleteForm = document.querySelector('#deleteUserModal form');
    
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            // Basic validation - let browser handle required fields
            const username = this.querySelector('input[name="username"]').value.trim();
            const password = this.querySelector('input[name="password"]').value.trim();
            const role = this.querySelector('select[name="role"]').value;
            
            if (!username || !password || !role) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }
            
            // Show loading state but don't prevent submission
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                
                // Re-enable after 5 seconds in case of error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }
            
            // Let form submit normally
            return true;
        });
    }
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }
            
            return true;
        });
    }
    
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }
            
            return true;
        });
    }
});

// Username availability check
function checkUsernameAvailability(username, currentUsername = null) {
    if (!username || username === currentUsername) return;
    
    // Simulate username check
    const usernames = ['admin', 'doctor1', 'pharmacist1', 'patient1'];
    
    if (usernames.includes(username.toLowerCase())) {
        const usernameInput = document.querySelector('input[name="username"]');
        showFieldError(usernameInput, 'Username already exists');
        return false;
    }
    
    return true;
}

// Add real-time username validation
document.addEventListener('DOMContentLoaded', function() {
    const usernameInputs = document.querySelectorAll('input[name="username"]');
    
    usernameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                checkUsernameAvailability(this.value.trim());
            }
        });
    });
});

// Role-based form adjustments
function adjustFormForRole(roleSelect) {
    const form = roleSelect.closest('form');
    const passwordField = form.querySelector('input[name="password"]');
    const passwordLabel = form.querySelector('label[for*="password"]');
    
    if (roleSelect.value === 'admin') {
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password <span class="required">*</span>';
        }
        if (passwordField) {
            passwordField.required = true;
        }
    } else {
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password';
        }
        if (passwordField) {
            passwordField.required = false;
        }
    }
}

// Add role change listener
document.addEventListener('DOMContentLoaded', function() {
    const roleSelects = document.querySelectorAll('select[name="role"]');
    
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            adjustFormForRole(this);
        });
    });
});

// Bulk operations
function selectAllUsers() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="user_ids[]"]');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"][name="user_ids[]"]:checked');
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
            bulkActions.querySelector('.selected-count').textContent = checkedBoxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

function bulkDeleteUsers() {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"][name="user_ids[]"]:checked');
    
    if (checkedBoxes.length === 0) {
        showAlert('Please select users to delete', 'warning');
        return;
    }
    
    confirmAction(`Are you sure you want to delete ${checkedBoxes.length} user(s)? This action cannot be undone.`, function() {
        // Submit bulk delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="bulk_delete">';
        
        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'user_ids[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
        
        document.body.appendChild(form);
        form.submit();
    });
}

// Add event listeners for bulk operations
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('input[type="checkbox"][name="user_ids[]"]');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', selectAllUsers);
    }
    
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', bulkDeleteUsers);
    }
});

// Export users functionality
function exportUsers() {
    const table = document.querySelector('.data-table');
    if (table) {
        exportToCSV(table, 'users.csv');
    }
}

// Search and filter functionality
function filterUsers() {
    const searchTerm = document.getElementById('userSearch')?.value.toLowerCase() || '';
    const roleFilter = document.getElementById('roleFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    
    const rows = document.querySelectorAll('.data-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const role = row.querySelector('.role-badge')?.textContent.toLowerCase() || '';
        
        let showRow = true;
        
        if (searchTerm && !text.includes(searchTerm)) {
            showRow = false;
        }
        
        if (roleFilter && role !== roleFilter.toLowerCase()) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
    
    updateUserCount();
}

function updateUserCount() {
    const visibleRows = document.querySelectorAll('.data-table tbody tr[style=""]').length;
    const totalRows = document.querySelectorAll('.data-table tbody tr').length;
    
    const countElement = document.querySelector('.user-count');
    if (countElement) {
        countElement.textContent = `Showing ${visibleRows} of ${totalRows} users`;
    }
}

// Add search and filter listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterUsers, 300));
    }
    
    if (roleFilter) {
        roleFilter.addEventListener('change', filterUsers);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterUsers);
    }
});

// User status management
function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    confirmAction(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this user?`, function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="user_id" value="${userId}">
            <input type="hidden" name="new_status" value="${newStatus}">
        `;
        
        document.body.appendChild(form);
        form.submit();
    });
}

// Password reset functionality
function resetUserPassword(userId, username) {
    confirmAction(`Are you sure you want to reset the password for ${username}?`, function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        
        document.body.appendChild(form);
        form.submit();
    });
}
