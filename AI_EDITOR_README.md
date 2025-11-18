# 🤖 AI Website Editor for ShieldStack Panel

An intelligent AI-powered website editing system that allows customers to modify their websites through natural language conversations. The AI connects via SSH to safely edit files, create pages, and make changes to websites.

## ✨ Features

### For Customers
- **💬 Natural Language Interface**: Describe changes in plain English
- **🔄 Real-time Chat**: Interactive AI assistant that understands context
- **📊 Usage Tracking**: Monitor token consumption and view statistics
- **📝 Change History**: Complete log of all modifications made
- **🔒 Safe Operations**: Automatic backups before every change
- **⚡ Fast Response**: Powered by GPT-4 or compatible models

### For Administrators
- **👥 Plan Management**: Assign Basic/Pro/Enterprise plans with token limits
- **🔐 SSH Credentials**: Securely manage customer server access (AES-256 encrypted)
- **📈 Analytics Dashboard**: Monitor usage across all customers
- **📋 Activity Logs**: View all AI operations and changes
- **⚙️ Flexible Configuration**: Customize safety rules and rate limits
- **🛡️ Security Controls**: Command whitelisting, path validation, rate limiting

## 🏗️ Architecture

```
┌─────────────────────────────────────────────┐
│          Customer Chat Interface            │
│  (Browser-based chat with AI assistant)     │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│              API Layer                       │
│  - Message Processing                        │
│  - Session Management                        │
│  - Token Counting                            │
└──────────────────┬──────────────────────────┘
                   │
         ┌─────────┴─────────┐
         ▼                   ▼
┌──────────────────┐  ┌──────────────────┐
│   AI Client      │  │  Safety Validator │
│  (OpenAI API)    │  │  - Path checks    │
│  - GPT-4         │  │  - Command filter │
│  - Function calls│  │  - Rate limiting  │
└────────┬─────────┘  └──────────────────┘
         │
         ▼
┌─────────────────────────────────────────────┐
│           SSH Manager                        │
│  - Secure connections (SSH2)                 │
│  - File operations (read/write)              │
│  - Command execution                         │
│  - Backup management                         │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│        Customer's Web Server                 │
│  - Website files (HTML/CSS/JS/PHP)          │
│  - Automatic backups                         │
│  - Change logs                               │
└─────────────────────────────────────────────┘
```

## 📦 What's Included

### Database Tables
- `ai_service_plans` - Customer plan assignments and token limits
- `customer_ssh_credentials` - Encrypted SSH access credentials
- `ai_change_logs` - Complete audit trail of all changes
- `ai_chat_sessions` - Conversation history and context
- `ai_rate_limits` - Rate limiting and usage tracking
- `ai_file_backups` - Backup metadata and expiration

### Core Components

#### Backend Classes (`ai-editor/includes/`)
- **encryption.php** - AES-256 encryption for sensitive data
- **ssh-manager.php** - SSH2-based file operations
- **ai-client.php** - OpenAI API integration with function calling
- **safety-validator.php** - Security validation and command filtering
- **backup-manager.php** - Automated backup creation and cleanup

#### API Endpoints (`ai-editor/api/`)
- **chat.php** - Main chat processing and SSH execution
- **get-session.php** - Load conversation history

#### Admin Interfaces (`ai-editor/admin/`)
- **index.php** - Dashboard with statistics and overview
- **assign-plan.php** - Assign and manage customer plans
- **manage-ssh.php** - Configure SSH credentials
- **view-logs.php** - View all customer activity

#### Customer Interfaces (`ai-editor/`)
- **index.php** - Main chat interface
- **usage.php** - Token usage statistics
- **history.php** - Change history and logs

## 🚀 Quick Start

See [AI_EDITOR_INSTALLATION.md](AI_EDITOR_INSTALLATION.md) for detailed setup instructions.

### Quick Installation

1. **Run database migration:**
   ```bash
   mysql -u root -p shieldstack_panel < ai_editor_migration.sql
   ```

2. **Install SSH2 extension:**
   ```bash
   sudo pecl install ssh2
   echo "extension=ssh2.so" >> /etc/php/7.4/apache2/php.ini
   sudo service apache2 restart
   ```

3. **Configure API:**
   ```sql
   UPDATE system_settings SET `value` = 'https://api.openai.com/v1/chat/completions' WHERE `key` = 'ai_openai_endpoint';
   UPDATE system_settings SET `value` = 'your-api-key-here' WHERE `key` = 'ai_openai_key';
   ```

4. **Assign a plan** via Admin Panel → AI Website Editor → Assign Plans

5. **Configure SSH credentials** via Admin Panel → AI Website Editor → SSH Credentials

6. **Start using!** Customers can now access via AI Tools → AI Website Editor

## 💡 Usage Examples

### Customer Requests

**Simple Text Change:**
```
User: "Change the main heading on index.html to 'Welcome to Our Store'"
AI: "I'll change that for you. Let me create a backup first..."
     [Creates backup]
     [Reads index.html]
     [Updates heading]
     "Done! I've updated the heading to 'Welcome to Our Store'. The old version is backed up."
```

**Creating New Content:**
```
User: "Create a new services page listing our three main services"
AI: "I'll create a new services.html page for you. What are your three main services?"
User: "Web Design, SEO, and Hosting"
AI: [Creates services.html with structured content]
    "I've created services.html with your three services listed. Would you like me to add any specific details?"
```

**Bug Fixing:**
```
User: "The contact form button isn't centered, can you fix it?"
AI: [Reads contact.html and style.css]
    "I found the issue. The button is missing text-align: center in its container. I'll fix that now."
    [Updates CSS]
    "Fixed! The button should now be centered."
```

## 🔒 Security Features

### Multi-Layer Protection

1. **Command Whitelisting**
   - Only approved shell commands can execute
   - Dangerous commands blocked (rm, dd, chmod 777, etc.)

2. **Path Validation**
   - All file paths validated against web root
   - Directory traversal prevention (..)
   - Null byte injection protection

3. **Encrypted Storage**
   - SSH credentials encrypted with AES-256-CBC
   - Unique IV per encryption
   - Secure key management

4. **Rate Limiting**
   - Requests per minute limits
   - Daily token limits
   - Per-customer tracking

5. **Automatic Backups**
   - Created before every file modification
   - Configurable retention period
   - Restore capability

6. **Audit Logging**
   - Complete log of all operations
   - Success/failure tracking
   - Token usage monitoring

## 📊 Plan Types

### Basic Plan
- **10,000 tokens/month**
- Basic website modifications
- Automatic backups
- Email support
- **Recommended for:** Small websites, occasional updates

### Pro Plan
- **50,000 tokens/month**
- Advanced modifications
- Priority AI processing
- Priority support
- **Recommended for:** Active websites, regular updates

### Enterprise Plan
- **Unlimited tokens**
- Full website control
- Real-time processing
- 24/7 premium support
- Dedicated account manager
- **Recommended for:** Large sites, development teams

## 🛠️ Technical Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- SSH2 PHP extension
- OpenSSL extension
- 256MB+ RAM recommended

### API Requirements
- OpenAI API key (or compatible endpoint)
- Supports: OpenAI, Azure OpenAI, LocalAI, Ollama, etc.
- Recommended: GPT-4 for best results

### Network Requirements
- HTTPS recommended (not required)
- SSH access to customer servers
- Outbound HTTPS to AI API endpoint

## 📈 Token Usage Guide

Approximate token usage for common operations:

| Operation | Tokens |
|-----------|--------|
| Simple text change | 100-500 |
| CSS modification | 300-800 |
| HTML structure change | 500-1,500 |
| New page creation | 800-2,000 |
| Multiple file changes | 1,500-4,000 |
| Complex debugging | 2,000-5,000 |

**Tip:** More specific requests use fewer tokens!

## 🔧 Configuration

### Environment Variables

```env
AI_SSH_ENCRYPTION_KEY=your-64-char-hex-key
AI_OPENAI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_OPENAI_KEY=sk-your-api-key-here
AI_MODEL_NAME=gpt-4
```

### System Settings

All settings can be configured via database `system_settings` table:

- `ai_openai_endpoint` - API endpoint URL
- `ai_openai_key` - API key
- `ai_model_name` - Model to use (gpt-4, gpt-3.5-turbo, etc.)
- `ai_allowed_commands` - JSON array of allowed commands
- `ai_blocked_commands` - JSON array of blocked commands
- `ai_max_requests_per_minute` - Rate limit
- `ai_max_tokens_per_day` - Daily token limit
- `ai_backup_retention_days` - How long to keep backups
- `ai_max_file_size_mb` - Maximum file size for editing

## 🐛 Troubleshooting

### Common Issues

**SSH Connection Failed**
- Verify credentials are correct
- Check SSH service is running
- Ensure firewall allows connection
- Verify SSH2 extension is installed

**API Errors**
- Check API key is valid
- Verify endpoint URL is correct
- Ensure outbound HTTPS is allowed
- Check API rate limits

**Permission Denied**
- Verify SSH user has proper permissions
- Check web root path is correct
- Ensure files are readable/writable

See [AI_EDITOR_INSTALLATION.md](AI_EDITOR_INSTALLATION.md) for detailed troubleshooting.

## 📝 License

This is part of ShieldStack Panel. All rights reserved.

## 🤝 Support

For issues or questions:
- 📧 Email: support@shieldstack.dev
- 🐛 Issues: GitHub repository
- 📖 Docs: AI_EDITOR_INSTALLATION.md

---

**Built with ❤️ for ShieldStack Panel**

*Powered by OpenAI GPT-4 | Secured with AES-256 | Protected by Multi-Layer Validation*
