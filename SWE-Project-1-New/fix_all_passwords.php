<?php
/**
 * Fix All Passwords Script
 * This will update all user passwords to be properly hashed
 * Default password for all users will be set to 'password'
 */

require_once 'config.php';

$message = '';
$message_type = '';
$updated_count = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Get all users
    $result = $conn->query("SELECT id, username FROM users");
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
    }
    
    // Default password to set
    $defaultPassword = $_POST['default_password'] ?? 'password';
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    if (!$hashedPassword) {
        $message = 'Error: Failed to generate password hash.';
        $message_type = 'error';
    } else {
        // Update all users
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            foreach ($users as $user) {
                $stmt->bind_param('si', $hashedPassword, $user['id']);
                if ($stmt->execute()) {
                    $updated_count++;
                }
            }
            $stmt->close();
            
            $message = "Successfully updated passwords for {$updated_count} user(s). All passwords are now set to: '{$defaultPassword}'";
            $message_type = 'success';
        } else {
            $message = 'Database error: ' . $conn->error;
            $message_type = 'error';
        }
    }
}

// Get all users
$users = [];
$result = $conn->query("SELECT id, username, role, name, email, password FROM users ORDER BY id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['is_hashed'] = preg_match('/^\$2[axyb]\$/', $row['password']);
        $users[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix All Passwords - MediTrack</title>
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/css/style.css">
</head>
<body>
    <div class="container">
        <main class="main-content">
            <div class="page-header">
                <h1>🔧 Fix All User Passwords</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="section">
                <h2 class="section-title">⚠️ Reset All Passwords</h2>
                <p class="text-muted">This will reset ALL user passwords to the same value. Use this if passwords are not working.</p>
                
                <form method="POST" style="max-width: 600px; margin-top: 2rem;">
                    <div class="form-group">
                        <label for="default_password">New Password for All Users:</label>
                        <input type="password" id="default_password" name="default_password" 
                               value="password" required>
                        <small class="text-muted">All users will have this password after reset.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="confirm" value="yes" required>
                            I understand this will reset ALL user passwords
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Reset All Passwords
                    </button>
                </form>
            </div>
            
            <div class="section">
                <h2 class="section-title">Current Users</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Password Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td>
                                    <span class="status-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_hashed']): ?>
                                        <span class="status-badge status-success">Hashed</span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">Plain Text</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="section">
                <a href="<?php echo getBaseUrl(); ?>diagnose_passwords.php" class="btn btn-info">
                    <i class="fas fa-search"></i> Diagnose Passwords
                </a>
                <a href="<?php echo getBaseUrl(); ?>routes/admin_users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to User Management
                </a>
            </div>
        </main>
    </div>
</body>
</html>

