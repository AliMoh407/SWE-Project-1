<?php
include __DIR__ . '/../../includes/header.php';
?>

<main class="login-container">
    <div class="login-form-container">
        <div class="login-header">
            <i class="fas fa-hospital"></i>
            <h2>MediTrack</h2>
            <p>Please login to continue</p>
        </div>
        
        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                    Username
                </label>         
                <input type="text" id="username" name="username" required    
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">      
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <div class="demo-accounts">
            <h3>Demo Accounts:</h3>
            <div class="demo-account">
                <strong>Admin:</strong> admin / password
            </div>
            <div class="demo-account">
                <strong>Doctor:</strong> doctor1 / password
            </div>
            <div class="demo-account">
                <strong>Pharmacist:</strong> pharmacist1 / password
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


