<?php
/**
 * Database Setup Script
 * Run this file once to create the database and tables
 * Usage: Open in browser or run: php setup_database.php
 */

// Database configuration (should match Database.php)
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'meditrack';

echo "=== MediTrack Database Setup ===\n\n";

// Step 1: Connect to MySQL server (without selecting a database)
echo "Step 1: Connecting to MySQL server...\n";
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "✓ Connected to MySQL server\n\n";

// Step 2: Create database if it doesn't exist
echo "Step 2: Creating database '$database'...\n";
$sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql) === TRUE) {
    echo "✓ Database '$database' created or already exists\n\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

// Step 3: Select the database
echo "Step 3: Selecting database '$database'...\n";
if ($conn->select_db($database)) {
    echo "✓ Database selected\n\n";
} else {
    die("Error selecting database: " . $conn->error . "\n");
}

// Step 4: Read and execute schema.sql
echo "Step 4: Reading schema file...\n";
$schemaFile = __DIR__ . '/database/schema.sql';

if (!file_exists($schemaFile)) {
    die("Error: Schema file not found at: $schemaFile\n");
}

$schema = file_get_contents($schemaFile);
if ($schema === false) {
    die("Error: Could not read schema file\n");
}
echo "✓ Schema file loaded\n\n";

// Step 5: Execute SQL statements
echo "Step 5: Executing SQL schema...\n";

// Temporarily disable foreign key checks to avoid constraint issues during setup
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Split the SQL file into individual statements
// Remove comments and split by semicolons
$schema = preg_replace('/--.*$/m', '', $schema); // Remove single-line comments
$statements = array_filter(
    array_map('trim', explode(';', $schema)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^\s*$/s', $stmt);
    }
);

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    if (empty(trim($statement))) {
        continue;
    }
    
    if ($conn->query($statement) === TRUE) {
        $successCount++;
    } else {
        // Some errors are expected (like "table already exists", "duplicate entry")
        if (strpos($conn->error, 'already exists') === false && 
            strpos($conn->error, 'Duplicate entry') === false &&
            strpos($conn->error, 'Duplicate key') === false) {
            echo "Warning: " . $conn->error . "\n";
            $errorCount++;
        } else {
            $successCount++;
        }
    }
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "✓ Executed $successCount SQL statements";
if ($errorCount > 0) {
    echo " (with $errorCount warnings)";
}
echo "\n\n";

// Step 6: Verify tables were created
echo "Step 6: Verifying tables...\n";
$tables = ['users', 'inventory', 'activity_logs', 'requests', 'notifications'];
$allTablesExist = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' NOT found\n";
        $allTablesExist = false;
    }
}

echo "\n";

// Step 7: Check sample data
if ($allTablesExist) {
    echo "Step 7: Checking sample data...\n";
    
    $inventoryCount = $conn->query("SELECT COUNT(*) as count FROM inventory")->fetch_assoc()['count'];
    $notificationsCount = $conn->query("SELECT COUNT(*) as count FROM notifications")->fetch_assoc()['count'];
    
    echo "✓ Inventory items: $inventoryCount\n";
    echo "✓ Notifications: $notificationsCount\n";
    echo "\n";
}

$conn->close();

echo "=== Setup Complete! ===\n";
echo "You can now access your application.\n";
echo "If you see this in a browser, you can close this window.\n";
?>

