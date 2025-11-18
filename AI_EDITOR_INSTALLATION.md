# AI Website Editor - Installation & Setup Guide

Complete guide for installing and configuring the AI Website Editor feature for ShieldStack Panel.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Database Setup](#database-setup)
3. [PHP Extensions](#php-extensions)
4. [Configuration](#configuration)
5. [System Settings](#system-settings)
6. [Getting Started](#getting-started)
7. [Admin Guide](#admin-guide)
8. [Customer Guide](#customer-guide)
9. [Troubleshooting](#troubleshooting)
10. [Security Considerations](#security-considerations)

---

## Prerequisites

Before installing the AI Website Editor, ensure you have:

- **PHP 7.4+** with PDO and OpenSSL extensions
- **MySQL 5.7+** or **MariaDB 10.2+**
- **SSH2 PHP Extension** (for SSH connectivity)
- **OpenAI-compatible API endpoint** (OpenAI, Azure OpenAI, or self-hosted)
- **Root/Admin access** to the server

---

## Database Setup

### Step 1: Run the Migration Script

Execute the database migration script to create all necessary tables:

```bash
mysql -u your_db_user -p shieldstack_panel < ai_editor_migration.sql
```

Or use phpMyAdmin to import the `ai_editor_migration.sql` file.

### Step 2: Verify Tables

Run this query to verify all tables were created:

```sql
SHOW TABLES LIKE 'ai_%';
SHOW TABLES LIKE '%ssh%';
```

You should see:
- `ai_service_plans`
- `customer_ssh_credentials`
- `ai_change_logs`
- `ai_chat_sessions`
- `ai_rate_limits`
- `ai_file_backups`

### Step 3: Check System Settings

Verify AI settings were inserted:

```sql
SELECT * FROM system_settings WHERE `key` LIKE 'ai_%';
```

---

## PHP Extensions

### Install SSH2 Extension

The SSH2 extension is **required** for SSH connectivity.

#### On Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install libssh2-1-dev libssh2-1
sudo pecl install ssh2
```

Add to your `php.ini`:
```ini
extension=ssh2.so
```

#### On CentOS/RHEL:

```bash
sudo yum install libssh2 libssh2-devel
sudo pecl install ssh2
```

Add to your `php.ini`:
```ini
extension=ssh2.so
```

#### Verify Installation:

```bash
php -m | grep ssh2
```

You should see `ssh2` in the output.

### Alternative: Use phpseclib (If SSH2 extension not available)

If you cannot install SSH2 extension, you can modify `ssh-manager.php` to use [phpseclib](https://github.com/phpseclib/phpseclib):

```bash
composer require phpseclib/phpseclib:~3.0
```

---

## Configuration

### Step 1: Configure API Credentials

Update the system settings in your database with your OpenAI API credentials:

```sql
UPDATE system_settings SET `value` = 'https://api.openai.com/v1/chat/completions' WHERE `key` = 'ai_openai_endpoint';
UPDATE system_settings SET `value` = 'your-api-key-here' WHERE `key` = 'ai_openai_key';
UPDATE system_settings SET `value` = 'gpt-4' WHERE `key` = 'ai_model_name';
```

**For Azure OpenAI:**
```sql
UPDATE system_settings SET `value` = 'https://your-resource.openai.azure.com/openai/deployments/your-deployment/chat/completions?api-version=2023-05-15' WHERE `key` = 'ai_openai_endpoint';
```

**For Self-Hosted (e.g., LocalAI, Ollama):**
```sql
UPDATE system_settings SET `value` = 'http://localhost:8080/v1/chat/completions' WHERE `key` = 'ai_openai_endpoint';
```

### Step 2: Configure Safety Settings

Set allowed and blocked commands:

```sql
UPDATE system_settings SET `value` = '["ls","cat","grep","find","head","tail","wc","pwd","whoami","stat","file","du","df","test","echo","cp","mkdir","touch"]' WHERE `key` = 'ai_allowed_commands';

UPDATE system_settings SET `value` = '["rm","rmdir","dd","mkfs","fdisk","chmod 777","kill","shutdown","reboot","mysql","DROP","DELETE","wget","curl"]' WHERE `key` = 'ai_blocked_commands';
```

### Step 3: Set Rate Limits

Configure rate limiting:

```sql
INSERT INTO system_settings (`key`, `value`) VALUES
('ai_max_requests_per_minute', '10'),
('ai_max_tokens_per_day', '50000'),
('ai_backup_retention_days', '30'),
('ai_max_file_size_mb', '5')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
```

### Step 4: Generate Encryption Key

A secure encryption key is needed for storing SSH credentials:

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Save this key in system settings:

```sql
INSERT INTO system_settings (`key`, `value`) VALUES ('ai_ssh_encryption_key', 'your-generated-key-here');
```

**IMPORTANT:** Keep this key secure and never commit it to version control!

---

## System Settings

### Environment Variables (Optional)

You can use environment variables instead of database settings:

Add to your `.env` or server environment:

```env
AI_SSH_ENCRYPTION_KEY=your-secure-encryption-key
AI_OPENAI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_OPENAI_KEY=sk-your-api-key
AI_MODEL_NAME=gpt-4
```

### File Permissions

Ensure proper permissions:

```bash
chmod 755 /var/www/html/ai-editor
chmod 644 /var/www/html/ai-editor/includes/*.php
chmod 644 /var/www/html/ai-editor/api/*.php
```

---

## Getting Started

### For Administrators

#### 1. Access Admin Panel

Navigate to: **Admin Panel → AI Website Editor → Dashboard**

#### 2. Assign an AI Plan to a Customer

1. Go to **AI Website Editor → Assign Plans**
2. Select a customer
3. Choose plan type (Basic/Pro/Enterprise)
4. Set token limit:
   - **Basic:** 10,000 tokens
   - **Pro:** 50,000 tokens
   - **Enterprise:** -1 (unlimited)
5. Click "Assign Plan"

#### 3. Configure SSH Credentials

1. Go to **AI Website Editor → SSH Credentials**
2. Fill in the form:
   - **Customer:** Select customer
   - **Website Name:** Display name
   - **SSH Host:** server.example.com
   - **SSH Port:** 22 (default)
   - **SSH Username:** username
   - **SSH Password:** (will be encrypted)
   - **Web Root Path:** /var/www/html
   - **Website Type:** WordPress, HTML, PHP, etc.
3. Click "Add SSH Credentials"

**Security Note:** Passwords are encrypted using AES-256-CBC before storage.

#### 4. Test SSH Connection

After adding credentials, the system will attempt to connect. Check the "Status" column to verify connectivity.

### For Customers

#### 1. Access AI Website Editor

Navigate to: **AI Tools → AI Website Editor**

If you see an error:
- **"AI Website Editor Not Activated"** → Contact support to get a plan assigned
- **"SSH Credentials Not Configured"** → Administrator needs to set up your SSH access

#### 2. Start Chatting

Once configured, you can start requesting changes:

**Example Requests:**
- "Change the main heading on my homepage to 'Welcome to Our Store'"
- "Add a contact form to my contact.html page"
- "Fix the broken CSS on the about page"
- "Create a new page called services.html with a list of our services"
- "Update the copyright year in the footer to 2025"

#### 3. Monitor Usage

- Go to **Usage** tab to see your token consumption
- Go to **History** tab to see all changes made

---

## Admin Guide

### Managing Plans

**View All Plans:**
- **AI Website Editor → Dashboard**
- Shows all active plans, token usage, and statistics

**Add Tokens to a Plan:**
1. Go to **Assign Plans**
2. Find the customer's plan
3. Click "Add Tokens"
4. Enter number of tokens to add
5. Click "Add Tokens"

**Suspend/Activate a Plan:**
1. Go to **Assign Plans**
2. Find the customer's plan
3. Click "Suspend" or "Activate"

**Reset Token Usage:**
1. Go to **Assign Plans**
2. Click "Reset" to set tokens_used back to 0

### Managing SSH Credentials

**Add New Credentials:**
- **AI Website Editor → SSH Credentials**
- Fill in the form and submit

**Edit Credentials:**
- Currently requires re-adding (update feature can be added)

**Delete Credentials:**
- Click "Delete" button (will ask for confirmation)

**Enable/Disable:**
- Click "Enable" or "Disable" to toggle `is_active` status

### Viewing Logs

**All Customer Activity:**
- **AI Website Editor → View Logs**
- Filter by customer or success status
- View all AI requests and responses

**Export Logs:**
- Use SQL query to export:

```sql
SELECT * FROM ai_change_logs WHERE executed_at >= '2025-01-01' INTO OUTFILE '/tmp/ai_logs.csv';
```

### Monitoring Security

**Check for Suspicious Activity:**

```sql
SELECT customer_id, COUNT(*) as failed_attempts
FROM ai_change_logs
WHERE success = 0 AND executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY customer_id
HAVING failed_attempts > 10;
```

**View Blocked Commands:**

```sql
SELECT * FROM ai_change_logs
WHERE error_message LIKE '%blocked%' OR error_message LIKE '%not allowed%';
```

---

## Customer Guide

### Understanding the AI Editor

The AI Website Editor gives you access to an AI assistant that can modify your website files through SSH. It can:

- Edit HTML, CSS, JavaScript, and PHP files
- Create new files and pages
- Find and replace content
- Fix bugs and errors
- Optimize code

### Best Practices

1. **Be Specific:** Clearly describe what you want changed
2. **One Change at a Time:** Make incremental changes for easier tracking
3. **Review Changes:** Check your website after each change
4. **Monitor Tokens:** Keep an eye on your token usage

### Example Conversations

**Changing Text:**
```
You: "Change the homepage heading to 'Welcome to Our New Site'"
AI: "I'll help you change the homepage heading. Let me first check your homepage file..."
```

**Creating a Page:**
```
You: "Create a new about page with information about our company"
AI: "I'll create a new about.html page for you. What information would you like to include?"
```

**Fixing Issues:**
```
You: "The contact form isn't working, can you fix it?"
AI: "Let me check your contact form code. Can you tell me what error you're seeing?"
```

### Token Usage Tips

- Simple text changes: ~100-500 tokens
- File creation: ~500-1,000 tokens
- Complex modifications: ~1,000-3,000 tokens
- Multiple file changes: ~2,000-5,000 tokens

---

## Troubleshooting

### Common Issues

#### 1. "SSH connection failed"

**Causes:**
- Wrong SSH credentials
- Firewall blocking connection
- SSH service not running
- Wrong port

**Solutions:**
- Verify credentials are correct
- Check firewall rules: `sudo ufw status`
- Ensure SSH is running: `sudo systemctl status ssh`
- Verify port (default: 22)

#### 2. "SSH2 extension not installed"

**Solution:**
```bash
sudo pecl install ssh2
echo "extension=ssh2.so" | sudo tee -a /etc/php/7.4/cli/php.ini
sudo service apache2 restart  # or nginx/php-fpm
```

#### 3. "Token limit exceeded"

**For Customers:**
- Contact support to upgrade plan or add tokens

**For Admins:**
- Go to **Assign Plans** → **Add Tokens**

#### 4. "Command not allowed"

**Explanation:**
The AI tried to execute a command that's blocked for security.

**Solution:**
- If the command is safe, admin can add it to `ai_allowed_commands`
- Otherwise, describe the task differently

#### 5. "Path is outside web root"

**Explanation:**
The AI tried to access files outside the configured web root.

**Solution:**
- Ensure your request only targets files within your website
- Admin: Verify `web_root_path` is correct in SSH credentials

### Debug Mode

Enable debug logging:

```php
// Add to ai-editor/api/chat.php
error_log("AI Request: " . json_encode($message));
error_log("AI Response: " . json_encode($aiResponse));
```

View logs:
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

---

## Security Considerations

### Best Practices

1. **Use SSH Keys Instead of Passwords**
   - Generate SSH key pairs
   - Store private key encrypted in database
   - More secure than passwords

2. **Restrict SSH User Permissions**
   - Create dedicated SSH user with limited permissions
   - Use `chroot` jail to restrict file access
   - Only grant access to web root directory

3. **Enable Rate Limiting**
   - Prevent abuse by limiting requests per minute
   - Set daily token limits

4. **Monitor Logs Regularly**
   - Check for suspicious activity
   - Review failed requests
   - Monitor token usage patterns

5. **Keep Backups**
   - Backups are created automatically
   - Verify backup retention settings
   - Test restore procedure

6. **Update Regularly**
   - Keep PHP and extensions updated
   - Update OpenAI API integration
   - Monitor security advisories

### Security Checklist

- [ ] SSH2 extension installed
- [ ] Encryption key generated and secured
- [ ] API credentials configured
- [ ] Allowed/blocked commands set
- [ ] Rate limiting enabled
- [ ] Backup retention configured
- [ ] SSH users have minimal permissions
- [ ] Logs are being monitored
- [ ] Test restore procedure works
- [ ] HTTPS enabled for panel

---

## Advanced Configuration

### Custom AI Models

You can use any OpenAI-compatible model:

```sql
-- Use GPT-3.5 Turbo (cheaper, faster)
UPDATE system_settings SET `value` = 'gpt-3.5-turbo' WHERE `key` = 'ai_model_name';

-- Use GPT-4 Turbo (more capable)
UPDATE system_settings SET `value` = 'gpt-4-turbo-preview' WHERE `key` = 'ai_model_name';

-- Use custom model
UPDATE system_settings SET `value` = 'your-custom-model' WHERE `key` = 'ai_model_name';
```

### Webhook Notifications

Add webhook notifications for events (requires custom development):

1. Create webhook endpoint
2. Trigger on important events:
   - Low token warning
   - Failed SSH connection
   - Successful file modification
   - Error occurred

### Automated Cleanup

Add to crontab for automated backup cleanup:

```bash
# Run daily at 2 AM
0 2 * * * php /var/www/html/ai-editor/includes/cleanup-backups.php
```

Create `cleanup-backups.php`:

```php
<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/backup-manager.php';

$backupManager = new BackupManager();
$result = $backupManager->cleanupExpiredBackups();

echo "Deleted {$result['deleted_count']} expired backups\n";
if (!empty($result['errors'])) {
    echo "Errors: " . implode("\n", $result['errors']);
}
?>
```

---

## Support

For issues or questions:

1. Check this documentation
2. Review error logs
3. Check GitHub issues: https://github.com/yourusername/shieldstack-panel/issues
4. Contact support: support@shieldstack.dev

---

## Changelog

### Version 1.0.0 (2025-11-18)
- Initial release
- Core AI chat functionality
- SSH integration
- Admin panel for plan management
- Customer usage tracking
- Comprehensive security features

---

**Installation complete! Your AI Website Editor is ready to use.**
