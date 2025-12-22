<?php
/**
 * Password Diagnostic Tool
 * Check password status in database
 */
require_once 'config.php';

// Get all users with password info
$users = [];
$result = $conn->query("SELECT id, username, password, role, name, email FROM users ORDER BY id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $password = $row['password'];
        $isHashed = preg_match('/^\$2[ayb]\$/', $password);
        $row['is_hashed'] = $isHashed;
        $row['password_length'] = strlen($password);
        $row['password_preview'] = $isHashed ? substr($password, 0, 20) . '...' : 'PLAIN TEXT';
        $users[] = $row;
    }
    $result->free();
}

// Test password verification
$testResults = [];
if (isset($_GET['test_user']) && isset($_GET['test_password'])) {
    $testUser = $_GET['test_user'];
    $testPassword = $_GET['test_password'];
    
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param('s', $testUser);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user) {
            $storedPassword = trim($user['password']);
            $inputPassword = trim($testPassword);
            $isHashed = preg_match('/^\$2[ayb]\$/', $storedPassword);
            
            $testResults['user_found'] = true;
            $testResults['username'] = $user['username'];
            $testResults['is_hashed'] = $isHashed;
            $testResults['verification'] = false;
            
            if ($isHashed) {
                $testResults['verification'] = password_verify($inputPassword, $storedPassword);
                $testResults['method'] = 'password_verify()';
            } else {
                $testResults['verification'] = ($storedPassword === $inputPassword);
                $testResults['method'] = 'direct comparison';
            }
        } else {
            $testResults['user_found'] = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Diagnostic - MediTrack</title>
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/css/style.css">
</head>
<body>
    <div class="container">
        <main class="main-content">
            <div class="page-header">
                <h1>🔍 Password Diagnostic Tool</h1>
            </div>
            
            <?php if (!empty($testResults)): ?>
            <div class="alert alert-<?php echo $testResults['verification'] ? 'success' : 'error'; ?>">
                <h3>Test Results:</h3>
                <?php if ($testResults['user_found']): ?>
                <p><strong>User:</strong> <?php echo htmlspecialchars($testResults['username']); ?></p>
                <p><strong>Password Type:</strong> <?php echo $testResults['is_hashed'] ? 'Hashed' : 'Plain Text'; ?></p>
                <p><strong>Verification Method:</strong> <?php echo $testResults['method']; ?></p>
                <p><strong>Result:</strong> 
                    <?php if ($testResults['verification']): ?>
                        <span class="status-badge status-success">✓ Password is CORRECT</span>
                    <?php else: ?>
                        <span class="status-badge status-error">✗ Password is INCORRECT</span>
                    <?php endif; ?>
                </p>
                <?php else: ?>
                <p>User not found!</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="section">
                <h2 class="section-title">Test Password Verification</h2>
                <form method="GET" style="max-width: 600px;">
                    <div class="form-group">
                        <label for="test_user">Username:</label>
                        <input type="text" id="test_user" name="test_user" required 
                               value="<?php echo htmlspecialchars($_GET['test_user'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="test_password">Password:</label>
                        <input type="password" id="test_password" name="test_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Test Password</button>
                </form>
            </div>
            
            <div class="section">
                <h2 class="section-title">All Users Password Status</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Password Type</th>
                                <th>Password Preview</th>
                                <th>Actions</th>
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
                                <td><code><?php echo htmlspecialchars($user['password_preview']); ?></code></td>
                                <td>
                                    <a href="?test_user=<?php echo urlencode($user['username']); ?>&test_password=password" 
                                       class="btn btn-sm btn-info">Test 'password'</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="section">
                <a href="<?php echo getBaseUrl(); ?>routes/admin_users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to User Management
                </a>
            </div>
        </main>
    </div>
</body>
</html>

