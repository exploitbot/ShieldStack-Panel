# MySQL Database Setup for ShieldStack Panel

## Quick Setup Instructions

### Option 1: Automatic Setup (if you have MySQL root access)

Run this command on your server:
```bash
mysql -u root -p < /var/www/html/panel/setup_mysql.sql
```

This will create:
- Database: `shieldstack_panel`
- User: `shieldstack`
- Password: `ShieldStack2024!`

### Option 2: Use Existing Database

If you already have a MySQL database and user, update the credentials in:
```
/var/www/html/panel/includes/config.php
```

Change these values:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

### Option 3: Manual MySQL Setup

Connect to MySQL and run:
```sql
CREATE DATABASE shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shieldstack'@'localhost' IDENTIFIED BY 'ShieldStack2024!';
GRANT ALL PRIVILEGES ON shieldstack_panel.* TO 'shieldstack'@'localhost';
FLUSH PRIVILEGES;
```

### Testing the Setup

Visit: https://shieldstack.dev/panel/setup_database.php

This page will:
- Test your database connection
- Show you any errors
- Confirm when setup is complete

### After Setup

Once working, you can:
1. Delete the setup files:
   - `/var/www/html/panel/setup_database.php`
   - `/var/www/html/panel/setup_mysql.sql`

2. Access the panel:
   - Customer Portal: https://shieldstack.dev/panel/
   - Admin Panel: https://shieldstack.dev/panel/admin/

3. Login with default admin credentials:
   - Email: admin@shieldstack.dev
   - Password: Admin123!

## Troubleshooting

### "Access denied for user"
- Check your MySQL credentials in `/var/www/html/panel/includes/config.php`
- Make sure the MySQL user has been created
- Try connecting manually: `mysql -u shieldstack -p shieldstack_panel`

### "Unknown database"
- The database hasn't been created yet
- Run the setup SQL script or create it manually

### "Can't connect to MySQL server"
- Check if MySQL is running: `systemctl status mysqld`
- Check if MySQL is listening: `netstat -tlnp | grep 3306`

