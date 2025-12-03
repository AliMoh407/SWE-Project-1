# Database Setup Instructions

## Step 1: Create the Database

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click on "New" in the left sidebar
3. Enter database name: `meditrack`
4. Choose collation: `utf8mb4_general_ci`
5. Click "Create"

## Step 2: Import the Schema

1. In phpMyAdmin, select the `meditrack` database
2. Click on the "SQL" tab
3. Open the file `database/schema.sql` from your project
4. Copy all the SQL content
5. Paste it into the SQL tab in phpMyAdmin
6. Click "Go" to execute

This will create all necessary tables:
- `users` - User accounts
- `inventory` - Inventory items
- `activity_logs` - System activity logs
- `requests` - Doctor requests for items
- `notifications` - System notifications

## Step 3: Verify Sample Data

After running the schema, you should have:
- 6 inventory items (Paracetamol, Morphine, etc.)
- 3 activity logs
- 4 sample notifications

## Step 4: Update Database Credentials (if needed)

If your database credentials are different, edit `config.php`:

```php
$DB_HOST = 'localhost';      // Your database host
$DB_USER = 'root';           // Your database username
$DB_PASS = '';               // Your database password
$DB_NAME = 'meditrack';      // Your database name
```

## Troubleshooting

### Error: Table already exists
If you get this error, you can either:
1. Drop the existing tables and re-run the schema
2. Or modify the schema to use `CREATE TABLE IF NOT EXISTS` (already included)

### Error: Foreign key constraint fails
Make sure you run the schema in order - the `users` table must be created before other tables that reference it.

### No data showing up
- Check that the INSERT statements in `schema.sql` ran successfully
- Verify your database connection in `config.php`
- Check that the tables exist in phpMyAdmin

