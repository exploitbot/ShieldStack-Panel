# ShieldStack Panel - Deployment Guide

## ⚠️ Important: Vercel Limitations

**Vercel is NOT recommended for this application** because:
- Vercel primarily supports Node.js/serverless functions
- Limited PHP support (experimental)
- No native MySQL database support
- No persistent file storage
- SSH2 extension not available

## ✅ Recommended Hosting Platforms

### 1. **Traditional PHP Hosting** (Best Option)
- **Namecheap Shared Hosting** - $2-5/month
- **Hostinger** - $2-4/month
- **SiteGround** - $4-7/month
- **DigitalOcean** - $6/month (requires setup)

### 2. **Cloud Platforms**
- **AWS Lightsail** - $5/month (PHP + MySQL)
- **Google Cloud Run** - Pay per use
- **Azure App Service** - $13/month

### 3. **Docker Deployment** (Any Platform)
- **Railway.app** - Free tier available
- **Render.com** - Free tier available
- **Fly.io** - Free tier available

---

## 🚀 Quick Deployment Options

### Option 1: Local Testing (Fastest)

```bash
# 1. Run setup script
./setup.sh

# 2. Edit configuration
nano .env
nano includes/config.php

# 3. Start server
php -S localhost:8000

# 4. Open browser
http://localhost:8000
```

---

### Option 2: Docker Deployment (Recommended for Testing)

**Step 1: Create Dockerfile**
```dockerfile
FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install SSH2 extension
RUN apt-get update && apt-get install -y libssh2-1-dev \
    && pecl install ssh2-1.3.1 \
    && docker-php-ext-enable ssh2

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache modules
RUN a2enmod rewrite

EXPOSE 80
```

**Step 2: Create docker-compose.yml**
```yaml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "8080:80"
    environment:
      - DB_HOST=db
      - DB_NAME=shieldstack_panel
      - DB_USER=root
      - DB_PASS=rootpassword
    depends_on:
      - db
    volumes:
      - .:/var/www/html

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: shieldstack_panel
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
      - ./database_schema.sql:/docker-entrypoint-initdb.d/1-schema.sql
      - ./ai_editor_migration.sql:/docker-entrypoint-initdb.d/2-ai-editor.sql

volumes:
  db_data:
```

**Step 3: Deploy**
```bash
docker-compose up -d
```

Access at: `http://localhost:8080`

---

### Option 3: Railway.app (Free + Easy)

**Step 1: Install Railway CLI**
```bash
npm install -g @railway/cli
railway login
```

**Step 2: Initialize Project**
```bash
railway init
railway link
```

**Step 3: Add MySQL Database**
```bash
railway add --database mysql
```

**Step 4: Set Environment Variables**
```bash
railway variables set DB_HOST=${{MYSQL_HOST}}
railway variables set DB_NAME=${{MYSQL_DATABASE}}
railway variables set DB_USER=${{MYSQL_USER}}
railway variables set DB_PASS=${{MYSQL_PASSWORD}}
railway variables set AI_OPENAI_KEY=your-key-here
railway variables set AI_SSH_ENCRYPTION_KEY=$(php -r "echo bin2hex(random_bytes(32));")
```

**Step 5: Deploy**
```bash
railway up
```

---

### Option 4: Render.com (Free Tier)

**Step 1: Create render.yaml**
```yaml
services:
  - type: web
    name: shieldstack-panel
    env: php
    buildCommand: composer install --no-dev
    startCommand: php -S 0.0.0.0:$PORT
    envVars:
      - key: DB_HOST
        fromDatabase:
          name: shieldstack-db
          property: host
      - key: DB_NAME
        fromDatabase:
          name: shieldstack-db
          property: database
      - key: DB_USER
        fromDatabase:
          name: shieldstack-db
          property: user
      - key: DB_PASS
        fromDatabase:
          name: shieldstack-db
          property: password

databases:
  - name: shieldstack-db
    databaseName: shieldstack_panel
    user: shieldstack
```

**Step 2: Connect to Render**
```bash
# Push to GitHub
git push origin main

# Connect GitHub repo in Render dashboard
# Deploy automatically
```

---

### Option 5: Vercel (Limited Support)

⚠️ **Only for testing frontend, NOT production**

**Step 1: Install Vercel CLI**
```bash
npm install -g vercel
```

**Step 2: Configure Environment Variables**
```bash
vercel env add DB_HOST
vercel env add DB_NAME
vercel env add DB_USER
vercel env add DB_PASS
vercel env add AI_OPENAI_KEY
vercel env add AI_SSH_ENCRYPTION_KEY
```

**Step 3: Deploy**
```bash
vercel --prod
```

**Limitations:**
- PHP runtime is experimental
- No MySQL (must use external database)
- No SSH2 extension
- No persistent storage
- Cold starts

**Required External Services:**
- Database: PlanetScale, AWS RDS, or DigitalOcean Managed MySQL
- File Storage: AWS S3 or similar

---

## 🗄️ Database Setup

### Remote MySQL Database Options

**1. PlanetScale (Free tier)**
```bash
# Free tier includes:
# - 5GB storage
# - 1 billion row reads/month
# - 10 million row writes/month

# Get connection string from PlanetScale dashboard
# Add to .env or Vercel environment variables
```

**2. AWS RDS**
```bash
# Smallest instance: db.t3.micro ($15/month)
# 20GB storage included
```

**3. DigitalOcean Managed Database**
```bash
# Basic plan: $15/month
# 1GB RAM, 10GB storage
```

**4. Aiven MySQL (Free tier)**
```bash
# Free tier for development
# 1 CPU, 1GB RAM
```

### Import Database Schema

```bash
# After creating remote database
mysql -h your-host.com -u your-user -p your-database < database_schema.sql
mysql -h your-host.com -u your-user -p your-database < ai_editor_migration.sql
```

---

## 🔧 Configuration for Production

### 1. Update config.php

```php
<?php
// Use environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'shieldstack_panel');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
?>
```

### 2. Set Environment Variables

All platforms support environment variables. Set these:

```bash
DB_HOST=your-database-host
DB_NAME=shieldstack_panel
DB_USER=your-db-user
DB_PASS=your-db-password
AI_OPENAI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_OPENAI_KEY=sk-your-key
AI_SSH_ENCRYPTION_KEY=your-64-char-hex-key
APP_ENV=production
```

### 3. Security Checklist

- [ ] Change default admin password
- [ ] Set strong database password
- [ ] Enable HTTPS/SSL
- [ ] Set secure session cookies
- [ ] Configure firewall rules
- [ ] Enable error logging (not displaying)
- [ ] Restrict file permissions
- [ ] Enable CSRF protection
- [ ] Validate all inputs
- [ ] Use prepared statements

---

## 📊 Platform Comparison

| Platform | Cost | Setup Difficulty | PHP Support | MySQL Included | SSH2 Support | Recommended |
|----------|------|------------------|-------------|----------------|--------------|-------------|
| **Local/XAMPP** | Free | Easy | ✅ Full | ✅ Yes | ✅ Yes | ⭐⭐⭐⭐⭐ Testing |
| **Docker** | Free | Medium | ✅ Full | ✅ Yes | ✅ Yes | ⭐⭐⭐⭐⭐ All |
| **Railway.app** | Free tier | Easy | ✅ Good | ✅ Yes | ⚠️ Maybe | ⭐⭐⭐⭐ Testing |
| **Render.com** | Free tier | Easy | ✅ Good | ✅ Yes | ⚠️ Maybe | ⭐⭐⭐⭐ Testing |
| **DigitalOcean** | $6/mo | Hard | ✅ Full | ✅ Yes | ✅ Yes | ⭐⭐⭐⭐⭐ Production |
| **Shared Hosting** | $3/mo | Easy | ✅ Full | ✅ Yes | ⚠️ Sometimes | ⭐⭐⭐⭐ Production |
| **Vercel** | Free tier | Easy | ⚠️ Limited | ❌ No | ❌ No | ⭐⭐ Frontend Only |

---

## 🎯 Recommended Setup for Testing

### Quick Test (5 minutes):
```bash
./setup.sh
php -S localhost:8000
```

### Docker Test (10 minutes):
```bash
docker-compose up -d
# Access at http://localhost:8080
```

### Railway Deploy (15 minutes):
```bash
railway init
railway add mysql
railway up
```

---

## 🐛 Troubleshooting

### "Database connection failed"
- Check DB_HOST, DB_NAME, DB_USER, DB_PASS
- Ensure MySQL is running
- Check firewall allows connection

### "SSH2 extension not loaded"
```bash
pecl install ssh2
echo "extension=ssh2.so" >> php.ini
```

### "Permission denied"
```bash
chmod 755 ai-editor
chmod 644 includes/config.php
chmod 600 .env
```

### "CSRF token validation failed"
- Ensure sessions are enabled
- Check session.save_path is writable
- Clear browser cookies

---

## 📞 Support

If you encounter issues:

1. Check logs: `tail -f /var/log/apache2/error.log`
2. Enable PHP errors: `error_reporting(E_ALL);`
3. Review AI_EDITOR_AUDIT_RESULTS.md
4. Check AI_EDITOR_TESTING_CHECKLIST.md

---

**For Vercel Testing:** Use external MySQL database (PlanetScale recommended)
**For Production:** Use Docker or traditional PHP hosting
**For Development:** Use local PHP server or Docker
