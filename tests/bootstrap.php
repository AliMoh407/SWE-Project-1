<?php

// Flag test environment to avoid real DB connections
define('APP_ENV', 'test');

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Provide minimal server values used by helpers such as getBaseUrl()
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/SWE-Project-1/routes/dashboard.php';

require_once __DIR__ . '/../config.php';

