# ShieldStack Client Management Panel

A comprehensive web hosting client management system with support ticketing, invoicing, service management, and automated email notifications.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🌟 Features

### Client Portal
- **User Authentication**: Secure login/registration with password hashing
- **Support Tickets**: Multi-department ticket system with priority levels
- **Invoice Management**: View invoices, payment status, and due dates
- **Service Overview**: View active services and subscriptions
- **Profile Management**: Update account information

### Admin Dashboard
- **User Management**: Create, edit, and manage customer accounts
- **Ticket System**: 
  - View all tickets with statistics dashboard
  - Filter by status (Open, Awaiting Admin, Awaiting Client, Resolved, Closed)
  - Priority management (Low, Medium, High)
  - Department routing
- **Invoice Management**: Create and track invoices with automated reminders
- **Service Management**: Manage plans/products and assign to users
- **Email Configuration**: Complete SMTP setup with template customization

### Email Notifications
- Automated notifications for:
  - New ticket creation
  - Ticket replies (admin and client)
  - Invoice creation and payment
  - Overdue invoice reminders
- Fully customizable email templates
- SMTP integration with any email provider

## 📋 Requirements

- **Web Server**: Nginx 1.20+ or Apache 2.4+
- **PHP**: 8.0 or higher
  - Required extensions: `php-fpm`, `php-mysqlnd`, `php-mbstring`, `php-json`
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **SSL Certificate**: Required for production (Let's Encrypt recommended)
- **Optional**: PHPMailer for email functionality

## 🚀 Installation

### 1. Clone Repository

```bash
git clone https://github.com/exploitbot/ShieldStack-Panel.git
cd ShieldStack-Panel
```

### 2. Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shieldstack'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON shieldstack_panel.* TO 'shieldstack'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u shieldstack -p shieldstack_panel < database_schema.sql
```

### 3. Configure Application

Edit `includes/config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shieldstack_panel');
define('DB_USER', 'shieldstack');
define('DB_PASS', 'your_secure_password');
define('DB_CHARSET', 'utf8mb4');
?>
```

### 4. Web Server Configuration

#### Nginx Configuration

Create `/etc/nginx/conf.d/shieldstack.conf`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/html/ShieldStack-Panel;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.ht {
        deny all;
    }
    
    location ~ /includes/ {
        deny all;
    }
}
```

### 5. Set File Permissions

```bash
# Set ownership
sudo chown -R nginx:nginx /var/www/html/ShieldStack-Panel

# Set permissions
sudo chmod 755 /var/www/html/ShieldStack-Panel
sudo find /var/www/html/ShieldStack-Panel -type f -exec chmod 644 {} \;
sudo find /var/www/html/ShieldStack-Panel -type d -exec chmod 755 {} \;

# For SELinux systems
sudo chcon -R -t httpd_sys_script_exec_t /var/www/html/ShieldStack-Panel
```

### 6. Install PHPMailer (Optional - for email functionality)

```bash
cd vendor
mkdir -p phpmailer
# Download PHPMailer from https://github.com/PHPMailer/PHPMailer
# Extract to vendor/phpmailer/
```

Or use Composer:
```bash
composer require phpmailer/phpmailer
```

### 7. Create Admin Account

```bash
# Generate password hash
php -r "echo password_hash('your_admin_password', PASSWORD_DEFAULT);"

# Insert admin user
mysql -u shieldstack -p shieldstack_panel
```

```sql
INSERT INTO users (email, password, full_name, role, created_at)
VALUES ('admin@yourdomain.com', '$2y$10$your_generated_hash_here', 'Admin User', 'admin', NOW());
```

### 8. Restart Services

```bash
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

## 📁 Directory Structure

```
ShieldStack-Panel/
├── admin/                      # Admin panel pages
│   ├── dashboard.php          # Admin dashboard
│   ├── tickets.php            # Ticket management
│   ├── ticket-view.php        # Individual ticket view
│   ├── invoices.php           # Invoice management
│   ├── create-invoice.php     # Invoice creation
│   ├── manage-users.php       # User management
│   ├── email-settings.php     # Email configuration
│   └── includes/              # Admin sidebar/header
├── includes/                   # Core classes
│   ├── config.php             # Database configuration
│   ├── database.php           # PDO database wrapper
│   ├── auth.php               # Authentication class
│   └── email.php              # Email service class
├── assets/                     # Static resources
│   ├── css/
│   └── js/
├── vendor/                     # Third-party libraries
│   ├── autoload.php           # PSR-4 autoloader
│   └── phpmailer/             # PHPMailer library
├── index.php                   # Client dashboard
├── login.php                   # Login page
├── signup.php                  # Registration page
├── tickets.php                 # Client ticket view
├── invoices.php                # Client invoice view
└── database_schema.sql         # Database schema
```

## 🗄️ Database Schema

### Core Tables

- **users**: User accounts (admin/customer roles)
- **tickets**: Support tickets with status and priority
- **ticket_replies**: Ticket conversation threads
- **ticket_departments**: Ticket categories
- **invoices**: Billing and payments
- **services**: Products/services offered
- **user_services**: User-service assignments
- **system_settings**: Application configuration

For complete schema, see `database_schema.sql`.

## 🔧 Configuration

### Email Settings

Access admin panel → Email Settings to configure:

1. **SMTP Configuration**
   - Host, Port, Encryption
   - Username and Password

2. **Email Identity**
   - From Email/Name
   - Reply-to Address
   - Admin Notification Email

3. **Notification Preferences**
   - Enable/disable specific notification types
   - Ticket notifications
   - Invoice notifications

4. **Template Customization**
   - Header and button colors
   - Logo URL
   - Footer text

5. **Testing**
   - Send test emails to verify configuration

## 🔐 Security Features

- **Password Hashing**: Bcrypt algorithm via `password_hash()`
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: All output escaped with `htmlspecialchars()`
- **Session Security**: HttpOnly, Secure, SameSite cookies
- **Role-Based Access Control**: Admin/Customer separation
- **CSRF Protection**: Recommended implementation (see docs)

## 🎨 Customization

### Branding
- Edit `/assets/css/style.css` for styling
- Update email templates in `/includes/email.php`
- Modify sidebar in `/includes/sidebar.php`

### Adding Features
- Follow existing patterns in `/includes/` for new classes
- Use prepared statements for all database queries
- Implement proper authentication checks

## 📊 Default Ticket Statuses

- **Open**: Initial state
- **Awaiting Admin**: Waiting for admin response
- **Awaiting Client**: Waiting for client response
- **Resolved**: Issue resolved but not closed
- **Closed**: Ticket permanently closed

## 📊 Default Ticket Priorities

- **Low**: Non-urgent issues
- **Medium**: Standard priority (default)
- **High**: Urgent issues requiring immediate attention

## 🐛 Troubleshooting

### Common Issues

**Issue: "Access Denied" after login**
```bash
# Fix session permissions
sudo chown -R nginx:nginx /var/lib/php/session
sudo systemctl restart php-fpm
```

**Issue: White page / 500 error**
```bash
# Check error logs
sudo tail -50 /var/log/nginx/error.log
sudo tail -50 /var/log/php-fpm/www-error.log
```

**Issue: Emails not sending**
- Verify SMTP credentials in Email Settings
- Check firewall allows SMTP ports (587, 465)
- Test with admin panel → Email Settings → Test & Verify

**Issue: SQL syntax errors**
- Ensure MySQL reserved keywords are escaped or aliased
- Check all queries use prepared statements

**Issue: Changes not appearing**
```bash
# Clear PHP OpCache
sudo systemctl restart php-fpm
```

## 📝 Development Notes

### Code Standards
- Use prepared statements for ALL database queries
- Escape all user output with `htmlspecialchars()`
- Follow PSR-4 autoloading for classes
- Keep business logic in `/includes/` classes
- Maintain separation between admin and client interfaces

### Adding New Email Notifications

1. Add notification toggle to `system_settings`
2. Create method in `/includes/email.php`
3. Add checkbox to Email Settings page
4. Call method from appropriate trigger point

Example:
```php
// In EmailService class
public function sendCustomNotification($userId) {
    if (!$this->isNotificationEnabled('notify_custom_event')) {
        return false;
    }
    // Email logic here
}
```

## 🔄 Updates & Maintenance

### Regular Tasks
- **Daily**: Monitor error logs
- **Weekly**: Check database size, update packages
- **Monthly**: Database backup, review security

### Backup Command
```bash
mysqldump -u shieldstack -p shieldstack_panel > backup_$(date +%F).sql
```

## 📄 License

This project is licensed under the MIT License - see LICENSE file for details.

## 🤝 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📧 Support

For issues or questions:
- Open an issue on GitHub
- Check existing documentation
- Review troubleshooting section

## 🙏 Acknowledgments

- PHPMailer for email functionality
- Bootstrap-inspired styling
- Community contributors

---

**Version**: 1.0.0  
**Last Updated**: November 2025  
**Maintained By**: ShieldStack Development Team
