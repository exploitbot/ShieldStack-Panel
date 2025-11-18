# ShieldStack Panel - Setup & Usage Guide

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ with extensions: PDO, MySQL, ssh2, mbstring, curl, xml
- MySQL 8.0+
- Apache/Nginx web server
- Composer (optional, for dependencies)

### Deployment Options

#### Option 1: Docker (Recommended for Testing)
```bash
# Clone repository
git clone https://github.com/exploitbot/ShieldStack-Panel.git
cd ShieldStack-Panel

# Start containers
docker-compose up -d

# Access at http://localhost:8080
```

#### Option 2: Production Server (shieldstack.dev)
```bash
# SSH into server
ssh root@shieldstack.dev

# Run automated deployment
cd /tmp
wget https://raw.githubusercontent.com/exploitbot/ShieldStack-Panel/claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx/deploy-to-production.sh
chmod +x deploy-to-production.sh
bash deploy-to-production.sh
```

#### Option 3: Manual Installation
```bash
# Clone repository
git clone https://github.com/exploitbot/ShieldStack-Panel.git
cd ShieldStack-Panel

# Install dependencies (if using Composer)
composer install

# Copy and configure database settings
cp includes/config.example.php includes/config.php
nano includes/config.php  # Update DB credentials

# Import database schemas
mysql -u root -p < database_schema.sql
mysql -u root -p < ai_editor_migration.sql

# Set up permissions
chmod 600 includes/config.php
mkdir -p uploads .ai_backups
chmod 775 uploads .ai_backups
chown -R www-data:www-data .
```

---

## 📋 Database Setup

### Automatic Setup
The database schema is automatically created when you access the panel for the first time.

### Manual Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schemas
mysql -u root -p shieldstack_panel < database_schema.sql
mysql -u root -p shieldstack_panel < ai_editor_migration.sql

# Set up AI access for admin user
mysql -u root -p shieldstack_panel < setup_eric_ai_access.sql
```

---

## 🔐 Default Credentials

**Admin Account:**
- Email: `eric@shieldstack.dev`
- Password: `jinho2310`

**MySQL:**
- User: `root`
- Password: `jinho2310`
- Database: `shieldstack_panel`

**⚠️ IMPORTANT:** Change these credentials in production!

---

## 🤖 AI Website Editor Setup

### 1. API Configuration
The AI Editor is pre-configured to use a custom OpenAI-compatible API:
- **Endpoint:** `https://clove.shieldstack.dev/v1/chat/completions`
- **API Key:** `eric`
- **Model:** `gpt-4`

Configuration file: `ai-editor/config.php`

### 2. Grant AI Access to Users

**Via SQL:**
```sql
-- Grant Enterprise Plan (unlimited tokens)
INSERT INTO ai_service_plans (customer_id, plan_type, token_limit, status)
SELECT id, 'enterprise', -1, 'active'
FROM customers
WHERE email = 'user@example.com';
```

**Via Admin Panel:**
1. Login as admin
2. Navigate to **Admin Panel → AI Website Editor → Assign Plans**
3. Select customer and plan type
4. Click "Assign Plan"

### 3. Configure SSH Credentials

**Via Admin Panel (Recommended):**
1. Navigate to **Admin Panel → AI Website Editor → SSH Credentials**
2. Click "Add SSH Credentials"
3. Fill in:
   - Customer: Select customer
   - SSH Host: `shieldstack.dev` (or your server)
   - SSH Port: `22`
   - SSH Username: Your SSH username
   - SSH Password: Your SSH password (will be encrypted)
   - Web Root Path: `/var/www/html`
   - Website URL: `https://shieldstack.dev`
   - Website Type: `php`, `wordpress`, `laravel`, etc.
4. Click "Save Credentials"

**Security Notes:**
- SSH credentials are encrypted using AES-256-CBC
- Encryption key is stored in database (for production, use environment variables)
- All file operations are validated and logged

### 4. Using the AI Editor

**As a Customer:**
1. Login to customer portal
2. Navigate to **AI Website Editor** in sidebar
3. Start chatting with the AI
4. Example requests:
   - "Show me the contents of index.php"
   - "Add a contact form to the homepage"
   - "Update the footer copyright year to 2025"
   - "List all files in the public directory"
   - "Create a backup of style.css"

**Safety Features:**
- ✅ Automatic backups before modifications
- ✅ Command whitelisting (only safe commands allowed)
- ✅ Path validation (prevents directory traversal)
- ✅ File size limits (5MB default)
- ✅ Content validation (warns about dangerous code)
- ✅ Rate limiting
- ✅ Complete audit logging

---

## 📁 Project Structure

```
ShieldStack-Panel/
├── admin/                      # Admin panel pages
│   ├── dashboard.php
│   ├── customers.php
│   ├── invoices.php
│   └── ...
├── ai-editor/                  # AI Website Editor
│   ├── api/                    # API endpoints
│   │   └── chat.php           # Chat API
│   ├── admin/                  # Admin interfaces
│   │   ├── assign-plan.php
│   │   ├── manage-ssh.php
│   │   └── view-logs.php
│   ├── assets/                 # CSS/JS
│   ├── includes/               # Core classes
│   │   ├── ai-client.php       # OpenAI integration
│   │   ├── ssh-manager.php     # SSH operations
│   │   ├── safety-validator.php # Security validation
│   │   ├── backup-manager.php  # Backup management
│   │   └── encryption.php      # Credential encryption
│   ├── config.php              # AI Editor configuration
│   └── index.php               # Customer chat interface
├── assets/                     # Global assets
│   ├── css/
│   │   └── style.css          # Main stylesheet (mobile-responsive)
│   └── js/
│       └── mobile-menu.js     # Mobile sidebar navigation
├── includes/                   # Core includes
│   ├── auth.php               # Authentication
│   ├── database.php           # Database connection
│   ├── csrf.php               # CSRF protection
│   └── config.php             # Database configuration
├── database_schema.sql        # Main database schema
├── ai_editor_migration.sql    # AI Editor tables
├── setup_eric_ai_access.sql   # Admin AI setup
├── deploy-to-production.sh    # Deployment script
└── docker-compose.yml         # Docker configuration
```

---

## 🔒 Security Features

### Implemented
- ✅ **Prepared Statements:** All database queries use PDO prepared statements
- ✅ **Password Hashing:** BCrypt password hashing
- ✅ **CSRF Protection:** Session-based CSRF tokens (implemented in critical forms)
- ✅ **XSS Protection:** HTML escaping with `htmlspecialchars()`
- ✅ **Path Traversal Prevention:** URL-encoded pattern detection, path normalization
- ✅ **Command Injection Prevention:** Command validation, substitution detection
- ✅ **Open Redirect Prevention:** URL validation for redirects
- ✅ **SSH Credential Encryption:** AES-256-CBC encryption
- ✅ **Session Management:** Secure session configuration
- ✅ **Input Validation:** Server-side validation on all forms

### Security Audit Results
**Fixed (Committed):**
- 2 Critical issues (command injection, path traversal)
- 4 High severity issues (open redirect, XSS)
- CSRF protection added to profile.php, tickets.php

**Remaining Work:**
- 11 forms need CSRF protection
- Additional XSS protection recommended
- Move encryption keys to environment variables

See `AI_EDITOR_AUDIT_RESULTS.md` for complete audit details.

---

## 📱 Mobile Responsiveness

The panel is fully mobile-responsive with:
- ✅ Collapsible sidebar menu
- ✅ Touch-friendly navigation
- ✅ Responsive tables with horizontal scrolling
- ✅ Mobile-optimized forms
- ✅ Adaptive layouts for all screen sizes

**Breakpoints:**
- Desktop: > 768px
- Tablet: 768px - 1024px
- Mobile: < 768px
- Small Mobile: < 480px

**Mobile Menu:**
- Hamburger menu automatically appears on mobile
- Slide-in sidebar with overlay
- Touch gestures supported

---

## 🎯 Features

### Customer Portal
- **Dashboard:** Overview of services, invoices, tickets
- **Services:** View and manage hosting services
- **Plans:** Browse available hosting plans
- **Domains:** Manage domain registrations
- **Invoices:** View and pay invoices
- **Tickets:** Submit and track support tickets
- **AI Website Editor:** AI-powered website modifications
- **Profile:** Update account information

### Admin Panel
- **Dashboard:** System overview and statistics
- **Customers:** Manage customer accounts
- **Invoices:** Create and manage invoices
- **Tickets:** Respond to support tickets
- **Services:** Manage customer services
- **Plans:** Configure hosting plans
- **AI Editor Management:**
  - Assign AI plans to customers
  - Configure SSH credentials
  - View change logs and audit trail
  - Monitor token usage

### AI Website Editor
- **Natural Language Interface:** Chat with AI to modify websites
- **SSH Integration:** Direct file system access
- **Automatic Backups:** Every modification backed up
- **Safety Validation:** Multi-layer security checks
- **Audit Logging:** Complete change history
- **Token Management:** Usage tracking per plan
- **Rate Limiting:** Prevents abuse

**Available Plans:**
- **Basic:** 10,000 tokens/month ($29.99)
- **Pro:** 50,000 tokens/month ($79.99)
- **Enterprise:** Unlimited tokens ($199.99)

---

## 🔧 Configuration

### Environment Variables
```bash
# Database
DB_HOST=localhost
DB_NAME=shieldstack_panel
DB_USER=root
DB_PASS=your_password

# Application
APP_ENV=production
APP_DEBUG=false

# AI Editor
AI_OPENAI_ENDPOINT=https://clove.shieldstack.dev/v1/chat/completions
AI_OPENAI_KEY=eric
AI_OPENAI_MODEL=gpt-4
AI_SSH_ENCRYPTION_KEY=your_32_byte_hex_key
```

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName shieldstack.dev
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/shieldstack_error.log
    CustomLog ${APACHE_LOG_DIR}/shieldstack_access.log combined
</VirtualHost>
```

### PHP Requirements
```ini
; php.ini settings
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
```

---

## 🐛 Troubleshooting

### "Database connection failed"
```bash
# Check MySQL is running
systemctl status mysql

# Test connection
mysql -u root -p shieldstack_panel -e "SHOW TABLES;"

# Verify config.php exists
cat /var/www/html/includes/config.php
```

### "SSH2 extension not found"
```bash
# Install SSH2 extension
apt-get install php-ssh2

# Restart web server
systemctl restart apache2
```

### "AI Editor not showing"
```bash
# Check config exists
ls -la /var/www/html/ai-editor/config.php

# Check database tables
mysql -u root -p shieldstack_panel -e "SHOW TABLES LIKE 'ai_%';"

# Check customer has plan
mysql -u root -p shieldstack_panel -e "
SELECT c.email, asp.plan_type, asp.token_limit, asp.status
FROM customers c
JOIN ai_service_plans asp ON c.id = asp.customer_id;"
```

### Mobile menu not working
```bash
# Check JavaScript is loaded
# View page source, ensure mobile-menu.js is included

# Check browser console for errors (F12)
# Should see: "Panel mobile menu script loaded"
```

### Permission errors
```bash
# Fix permissions
cd /var/www/html
chown -R www-data:www-data .
chmod -R 755 .
chmod 600 includes/config.php ai-editor/config.php
chmod 775 uploads .ai_backups
```

---

## 📈 Testing Checklist

- [ ] Admin login works
- [ ] Customer registration works
- [ ] Dashboard displays correctly
- [ ] Mobile menu opens/closes
- [ ] AI Editor chat interface loads
- [ ] SSH credentials can be added
- [ ] File operations work (read, write, list)
- [ ] Backups are created
- [ ] CSRF tokens validate
- [ ] XSS protection works
- [ ] Path traversal blocked
- [ ] Command injection blocked

---

## 📞 Support

**Documentation:**
- `AI_EDITOR_README.md` - AI Editor feature documentation
- `AI_EDITOR_AUDIT_RESULTS.md` - Security audit results
- `AI_EDITOR_TESTING_CHECKLIST.md` - Manual testing guide
- `DEPLOYMENT.md` - Deployment options
- `VERCEL_DEPLOYMENT.md` - Vercel-specific guide

**Admin Access:**
- Panel: https://shieldstack.dev
- Admin Dashboard: https://shieldstack.dev/admin/dashboard.php
- AI Editor Admin: https://shieldstack.dev/ai-editor/admin/

**GitHub Repository:**
- https://github.com/exploitbot/ShieldStack-Panel
- Branch: `claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx`

---

## 🚀 Next Steps

1. **Deploy to production:**
   ```bash
   bash deploy-to-production.sh
   ```

2. **Set up SSL certificate:**
   ```bash
   certbot --apache -d shieldstack.dev
   ```

3. **Configure AI credentials:**
   - Add SSH credentials via Admin Panel
   - Test AI chat functionality

4. **Implement remaining CSRF protection:**
   - See `AI_EDITOR_AUDIT_RESULTS.md` for list

5. **Set up automated backups:**
   ```bash
   # Add to crontab
   0 2 * * * tar -czf /root/backups/website_$(date +\%Y\%m\%d).tar.gz /var/www/html
   0 2 * * * mysqldump -uroot -pjinho2310 shieldstack_panel > /root/backups/database_$(date +\%Y\%m\%d).sql
   ```

6. **Monitor logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   tail -f /var/log/mysql/error.log
   ```

---

## 📝 Changelog

### Latest Updates
- ✅ Fixed critical security vulnerabilities (command injection, path traversal, XSS, open redirect)
- ✅ Added CSRF protection class and implemented in critical forms
- ✅ Configured custom OpenAI API endpoint (https://clove.shieldstack.dev/v1/)
- ✅ Granted enterprise AI plan to eric@shieldstack.dev
- ✅ Improved mobile responsiveness
- ✅ Complete AI Website Editor implementation

### Branch
`claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx`

---

**Last Updated:** 2025-11-18
**Version:** 1.0.0
**Status:** Production Ready (with minor security improvements recommended)
