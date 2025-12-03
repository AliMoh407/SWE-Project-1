<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>MediTrack</title>
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php if (isLoggedIn()): ?>
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-hospital"></i>
                    <h1>MediTrack</h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars(getCurrentUser()['name']); ?></span>
                    <span class="role-badge"><?php echo ucfirst(getCurrentUser()['role']); ?></span>
                    <a href="<?php echo getBaseUrl(); ?>logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </header>
        <?php endif; ?>
