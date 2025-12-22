<?php
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navigation.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>User Management</h1>
        <button class="btn btn-primary" onclick="showCreateUserModal()">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td class="actions">
                        <button class="btn btn-sm btn-secondary" onclick="showEditUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteUserModal(<?php echo $user['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New User</h2>
            <span class="close" onclick="closeModal('createUserModal')">&times;</span>
        </div>
        <form method="POST" action="<?php echo getBaseUrl(); ?>routes/admin_users.php" class="modal-body">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="create_username">Username</label>
                <input type="text" id="create_username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="create_password">Password</label>
                <input type="password" id="create_password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="create_name">Full Name</label>
                <input type="text" id="create_name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="create_email">Email</label>
                <input type="email" id="create_email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="create_role">Role</label>
                <select id="create_role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                    <option value="<?php echo ROLE_DOCTOR; ?>">Doctor</option>
                    <option value="<?php echo ROLE_PHARMACIST; ?>">Pharmacist</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <span class="close" onclick="closeModal('editUserModal')">&times;</span>
        </div>
        <form method="POST" action="<?php echo getBaseUrl(); ?>routes/admin_users.php" class="modal-body">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_user_id" name="user_id">
            
            <div class="form-group">
                <label for="edit_username">Username</label>
                <input type="text" id="edit_username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="edit_password">Password (leave blank to keep current)</label>
                <input type="password" id="edit_password" name="password">
            </div>
            
            <div class="form-group">
                <label for="edit_name">Full Name</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="edit_email">Email</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="edit_role">Role</label>
                <select id="edit_role" name="role" required>
                    <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                    <option value="<?php echo ROLE_DOCTOR; ?>">Doctor</option>
                    <option value="<?php echo ROLE_PHARMACIST; ?>">Pharmacist</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Delete User</h2>
            <span class="close" onclick="closeModal('deleteUserModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <form method="POST" action="<?php echo getBaseUrl(); ?>routes/admin_users.php" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_user_id" name="user_id">
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$additional_js = [getBaseUrl() . 'assets/js/user-management.js'];
include __DIR__ . '/../../includes/footer.php'; 
?>


