# Deploy to shieldstack.dev

## 🚀 Quick Deployment (Copy & Paste)

SSH into your server and run these commands:

```bash
# 1. SSH into the server
ssh root@shieldstack.dev
# Password: jinho2310

# 2. Download and run the deployment script
cd /tmp
wget https://raw.githubusercontent.com/exploitbot/ShieldStack-Panel/claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx/deploy-to-production.sh
chmod +x deploy-to-production.sh
bash deploy-to-production.sh

# 3. Configure AI Editor (add your OpenAI API key)
cp /var/www/html/ai-editor/config.production.php /var/www/html/ai-editor/config.php
nano /var/www/html/ai-editor/config.php
# Find line: 'api_key' => '',
# Replace with: 'api_key' => 'sk-your-actual-api-key-here',
# Save: Ctrl+O, Enter, Ctrl+X

# 4. Set proper permissions
chown www-data:www-data /var/www/html/ai-editor/config.php
chmod 600 /var/www/html/ai-editor/config.php

# 5. Restart web server (if needed)
systemctl restart apache2 || systemctl restart nginx
```

---

## 📋 Manual Deployment (Step by Step)

If the automated script doesn't work, follow these manual steps:

### Step 1: SSH into Server
```bash
ssh root@shieldstack.dev
# Password: jinho2310
```

### Step 2: Backup Current Site
```bash
mkdir -p /root/backups
cd /var/www/html
tar -czf /root/backups/website_backup_$(date +%Y%m%d_%H%M%S).tar.gz .
mysqldump -uroot -pjinho2310 shieldstack_panel > /root/backups/database_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 3: Install Required Packages
```bash
apt-get update
apt-get install -y git php php-mysql php-mbstring php-curl php-xml php-zip php-gd php-ssh2
```

### Step 4: Clone/Update Repository
```bash
cd /var/www/html

# If git repository already exists:
git fetch origin
git checkout claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx
git pull origin claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx

# If starting fresh:
# rm -rf /var/www/html/*
# git clone -b claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx https://github.com/exploitbot/ShieldStack-Panel.git .
```

### Step 5: Configure Database
```bash
cat > /var/www/html/includes/config.php <<'EOF'
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shieldstack_panel');
define('DB_USER', 'root');
define('DB_PASS', 'jinho2310');
define('DB_CHARSET', 'utf8mb4');
define('APP_ENV', 'production');
define('APP_DEBUG', false);
?>
EOF

chmod 600 /var/www/html/includes/config.php
```

### Step 6: Import Database Schemas
```bash
# Create database
mysql -uroot -pjinho2310 -e "CREATE DATABASE IF NOT EXISTS shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import main schema
mysql -uroot -pjinho2310 shieldstack_panel < /var/www/html/database_schema.sql

# Import AI Editor schema
mysql -uroot -pjinho2310 shieldstack_panel < /var/www/html/ai_editor_migration.sql
```

### Step 7: Configure AI Editor
```bash
# Copy production config
cp /var/www/html/ai-editor/config.production.php /var/www/html/ai-editor/config.php

# Edit and add your OpenAI API key
nano /var/www/html/ai-editor/config.php
# Find: 'api_key' => '',
# Change to: 'api_key' => 'sk-your-key-here',
# Save and exit (Ctrl+O, Enter, Ctrl+X)

# Set permissions
chmod 600 /var/www/html/ai-editor/config.php
```

### Step 8: Set Permissions
```bash
cd /var/www/html
chown -R www-data:www-data .
chmod -R 755 .
chmod 600 includes/config.php
chmod 600 ai-editor/config.php

# Create required directories
mkdir -p uploads .ai_backups
chown www-data:www-data uploads .ai_backups
chmod 775 uploads .ai_backups
```

### Step 9: Restart Web Server
```bash
# For Apache
systemctl restart apache2

# For Nginx + PHP-FPM
systemctl restart nginx
systemctl restart php8.2-fpm  # or php7.4-fpm, check your version
```

---

## ✅ Verification

After deployment, verify everything works:

### 1. Check Website
```bash
curl -I https://shieldstack.dev
# Should return: HTTP/1.1 200 OK or 302 Found (redirect to login)
```

### 2. Check Database Connection
```bash
php -r "
\$conn = new PDO('mysql:host=localhost;dbname=shieldstack_panel', 'root', 'jinho2310');
echo 'Database connected successfully!\n';
"
```

### 3. Check File Permissions
```bash
ls -la /var/www/html/includes/config.php
# Should show: -rw------- (600) owned by www-data or root

ls -la /var/www/html/ai-editor/config.php
# Should show: -rw------- (600) owned by www-data
```

### 4. Check PHP Extensions
```bash
php -m | grep -E "(ssh2|mysql|mbstring|curl)"
# Should show all required extensions
```

### 5. Test Login
Open browser and navigate to:
- **URL:** https://shieldstack.dev
- **Admin Login:** eric@shieldstack.dev
- **Password:** jinho2310

### 6. Test AI Editor
- Navigate to: https://shieldstack.dev/ai-editor
- Should show AI Website Editor interface
- If customer doesn't have plan, will show upgrade message

---

## 🔧 Troubleshooting

### "Database connection failed"
```bash
# Check MySQL is running
systemctl status mysql

# Test connection manually
mysql -uroot -pjinho2310 -e "SHOW DATABASES;"

# Check config.php exists and has correct credentials
cat /var/www/html/includes/config.php
```

### "500 Internal Server Error"
```bash
# Check PHP error logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### "Permission denied" errors
```bash
# Reset all permissions
cd /var/www/html
chown -R www-data:www-data .
chmod -R 755 .
chmod 600 includes/config.php ai-editor/config.php
chmod 775 uploads .ai_backups
```

### AI Editor not showing
```bash
# Check config exists
ls -la /var/www/html/ai-editor/config.php

# Check database tables exist
mysql -uroot -pjinho2310 shieldstack_panel -e "SHOW TABLES LIKE 'ai_%';"
# Should show: ai_service_plans, customer_ssh_credentials, ai_change_logs, etc.
```

### SSH2 extension not found
```bash
# Install SSH2 extension
apt-get install -y php-ssh2

# Restart web server
systemctl restart apache2 || systemctl restart php8.2-fpm
```

---

## 📊 Post-Deployment Tasks

### 1. Assign AI Plan to Test Customer
```bash
mysql -uroot -pjinho2310 shieldstack_panel <<EOF
-- Find a customer ID
SELECT id, email FROM customers LIMIT 5;

-- Assign AI Pro plan (replace CUSTOMER_ID with actual ID)
INSERT INTO ai_service_plans (customer_id, plan_type, token_limit, status)
VALUES (1, 'pro', 50000, 'active');
EOF
```

### 2. Add SSH Credentials for Test Customer
Login as admin and navigate to:
- **Admin Panel** → **AI Website Editor** → **SSH Credentials**
- Add SSH credentials for the test customer

### 3. Set Up Automated Backups
```bash
# Add to crontab
crontab -e

# Add these lines (daily backups at 2 AM):
0 2 * * * tar -czf /root/backups/website_$(date +\%Y\%m\%d).tar.gz /var/www/html
0 2 * * * mysqldump -uroot -pjinho2310 shieldstack_panel > /root/backups/database_$(date +\%Y\%m\%d).sql
0 2 * * * find /root/backups -name "*.tar.gz" -mtime +30 -delete
0 2 * * * find /root/backups -name "*.sql" -mtime +30 -delete
```

---

## 🎯 Testing Checklist

- [ ] Website loads: https://shieldstack.dev
- [ ] Admin login works: eric@shieldstack.dev / jinho2310
- [ ] Dashboard displays correctly
- [ ] Customers list shows data
- [ ] Invoices page works
- [ ] Tickets system functions
- [ ] AI Editor page loads: https://shieldstack.dev/ai-editor
- [ ] Admin can assign AI plans
- [ ] Admin can add SSH credentials
- [ ] Customer can access AI chat (if plan assigned)
- [ ] No PHP errors in logs
- [ ] Database queries working
- [ ] File permissions correct

---

## 📞 Support

**If you encounter any issues:**

1. Check error logs:
   ```bash
   tail -100 /var/log/apache2/error.log
   tail -100 /var/log/nginx/error.log
   ```

2. Check PHP errors:
   ```bash
   php -l /var/www/html/index.php
   php -l /var/www/html/ai-editor/index.php
   ```

3. Verify database:
   ```bash
   mysql -uroot -pjinho2310 shieldstack_panel -e "SHOW TABLES;"
   ```

4. Review security audit:
   ```bash
   cat /var/www/html/AI_EDITOR_AUDIT_RESULTS.md
   ```

---

## 🔒 Security Notes

**IMPORTANT:** After deployment, review and fix security issues documented in:
- `AI_EDITOR_AUDIT_RESULTS.md` (42 security issues identified)
- Priority: Fix 2 Critical and 5 High severity issues first

**Production Security Checklist:**
- [ ] Change default admin password
- [ ] Implement CSRF protection (class exists, needs integration)
- [ ] Fix path traversal vulnerability in safety-validator.php
- [ ] Fix command injection in ssh-manager.php
- [ ] Add XSS protection to all outputs
- [ ] Move encryption key to environment variable
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up fail2ban for brute force protection
- [ ] Regular security updates

---

**Deployment Script:** `/deploy-to-production.sh`
**Repository:** https://github.com/exploitbot/ShieldStack-Panel
**Branch:** claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx
