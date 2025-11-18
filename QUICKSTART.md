# ShieldStack Panel - Quick Start Guide

## 🚀 Fastest Way to Test (Choose One)

### Option 1: Docker (Recommended - Everything Included)
```bash
# Start everything (web server + MySQL + phpMyAdmin)
docker-compose up -d

# Access the application
Web App: http://localhost:8080
phpMyAdmin: http://localhost:8081 (user: root, pass: rootpassword)

# Stop when done
docker-compose down
```

### Option 2: Local PHP Server
```bash
# Run setup script
chmod +x setup.sh
./setup.sh

# Edit configuration
nano .env
nano includes/config.php

# Start server
php -S localhost:8000

# Access at http://localhost:8000
```

### Option 3: One-Line Test Server
```bash
# Quick test without database (will show errors but pages load)
php -S localhost:8000
```

---

## 🔑 Default Login

After setup, login with:
```
Email: eric@shieldstack.dev
Password: jinho2310
```

⚠️ **Change this immediately in production!**

---

## ⚙️ Environment Setup

### Required Environment Variables

Create `.env` file (copy from `.env.example`):

```bash
# Database
DB_HOST=localhost
DB_NAME=shieldstack_panel
DB_USER=your_user
DB_PASS=your_password

# OpenAI API
AI_OPENAI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_OPENAI_KEY=sk-your-key-here

# Encryption (generate with: php -r "echo bin2hex(random_bytes(32));")
AI_SSH_ENCRYPTION_KEY=your-64-char-hex-key
```

### Generate Encryption Key
```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

## 📦 Installation Steps

### Step 1: Clone Repository
```bash
git clone <repository-url>
cd ShieldStack-Panel
```

### Step 2: Configure Environment
```bash
cp .env.example .env
cp includes/config.example.php includes/config.php

# Edit with your credentials
nano .env
nano includes/config.php
```

### Step 3: Setup Database
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schemas
mysql -u root -p shieldstack_panel < database_schema.sql
mysql -u root -p shieldstack_panel < ai_editor_migration.sql
```

### Step 4: Install PHP Extensions
```bash
# Required for AI Editor
sudo pecl install ssh2
echo "extension=ssh2.so" | sudo tee -a /etc/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/cli/php.ini
```

### Step 5: Start Server
```bash
php -S localhost:8000
```

---

## 🐳 Docker Deployment

### Quick Start
```bash
docker-compose up -d
```

### What This Includes:
- ✅ PHP 8.2 with Apache
- ✅ MySQL 8.0 database
- ✅ phpMyAdmin interface
- ✅ All extensions installed
- ✅ Database auto-imported

### Access URLs:
- Application: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- Database: localhost:3306

### Useful Commands:
```bash
# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Stop services
docker-compose down

# Rebuild after changes
docker-compose up -d --build

# Access MySQL directly
docker-compose exec db mysql -u root -p
```

---

## ☁️ Cloud Deployment

### Vercel (Frontend Testing Only)
```bash
vercel --prod
```
⚠️ Note: Vercel has limited PHP support. Use for frontend testing only.

### Railway.app (Full Stack)
```bash
railway init
railway add mysql
railway up
```

### Render.com (Full Stack)
- Connect GitHub repository
- Auto-deploys from main branch
- Add MySQL database
- Configure environment variables

---

## 📊 File Structure

```
ShieldStack-Panel/
├── ai-editor/              # AI Website Editor
│   ├── admin/             # Admin interfaces
│   ├── api/               # API endpoints
│   ├── assets/            # CSS/JS files
│   ├── includes/          # Core classes
│   ├── index.php          # Chat interface
│   ├── usage.php          # Token usage
│   └── history.php        # Change history
├── admin/                  # Main admin panel
├── includes/               # Core includes
├── assets/                 # Main assets
├── .env.example           # Environment template
├── setup.sh               # Quick setup script
├── docker-compose.yml     # Docker configuration
├── Dockerfile             # Docker image
└── vercel.json            # Vercel config
```

---

## 🧪 Testing

### Automated Testing
```bash
# Check PHP syntax
find . -name "*.php" -exec php -l {} \;

# Run setup script
./setup.sh
```

### Manual Testing
See `AI_EDITOR_TESTING_CHECKLIST.md` for complete testing procedure.

---

## 🔒 Security Notes

Before production:
1. Change default admin password
2. Set strong database password
3. Generate unique encryption key
4. Enable HTTPS
5. Review `AI_EDITOR_AUDIT_RESULTS.md`
6. Fix all Critical and High severity issues

---

## 📚 Documentation

- `AI_EDITOR_INSTALLATION.md` - Complete setup guide
- `AI_EDITOR_TESTING_CHECKLIST.md` - Testing procedures
- `AI_EDITOR_AUDIT_RESULTS.md` - Security audit
- `AI_EDITOR_README.md` - Feature overview
- `DEPLOYMENT.md` - Deployment options

---

## 🐛 Troubleshooting

### Database Connection Failed
```bash
# Check MySQL is running
sudo systemctl status mysql

# Check credentials in .env and includes/config.php
```

### SSH2 Extension Not Loaded
```bash
# Install SSH2
sudo pecl install ssh2

# Add to php.ini
echo "extension=ssh2.so" >> /etc/php/8.2/cli/php.ini
sudo service apache2 restart
```

### Permission Errors
```bash
chmod 755 ai-editor
chmod 644 includes/config.php
chmod 600 .env
```

---

## 💡 Quick Tips

### Test Without Database
```bash
# Just to see if pages load
php -S localhost:8000
```

### Reset Database
```bash
mysql -u root -p -e "DROP DATABASE shieldstack_panel; CREATE DATABASE shieldstack_panel;"
mysql -u root -p shieldstack_panel < database_schema.sql
mysql -u root -p shieldstack_panel < ai_editor_migration.sql
```

### View Logs
```bash
# Docker logs
docker-compose logs -f web

# PHP built-in server
# Logs appear in terminal

# Apache logs
tail -f /var/log/apache2/error.log
```

---

## 🎯 Next Steps

1. ✅ Start server (Docker or PHP)
2. ✅ Login with default credentials
3. ✅ Change admin password
4. ✅ Configure OpenAI API key
5. ✅ Test basic functionality
6. ✅ Review security audit
7. ✅ Run testing checklist
8. ✅ Deploy to production

---

**For detailed deployment options, see DEPLOYMENT.md**
**For security audit, see AI_EDITOR_AUDIT_RESULTS.md**
**For testing procedures, see AI_EDITOR_TESTING_CHECKLIST.md**
