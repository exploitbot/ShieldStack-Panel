# Vercel Deployment Guide for ShieldStack Panel

## ⚠️ IMPORTANT WARNINGS

**Vercel is NOT recommended for production deployment** of this application because:

1. ❌ **No MySQL Database** - Vercel doesn't provide MySQL
2. ❌ **Limited PHP Support** - PHP runtime is experimental
3. ❌ **No SSH2 Extension** - AI Editor won't work
4. ❌ **No Persistent Storage** - File uploads/backups won't persist
5. ❌ **Cold Starts** - App sleeps when not in use
6. ⚠️ **Limited Execution Time** - 10 second timeout on free tier

**Use Vercel ONLY for:**
- Frontend testing
- UI/UX review
- Demo purposes
- With external database

---

## 🚀 Deploy to Vercel (Frontend Testing Only)

### Step 1: Install Vercel CLI

```bash
npm install -g vercel
vercel login
```

### Step 2: Set Up External Database (Required)

You **MUST** use an external MySQL database. Options:

#### Option A: PlanetScale (Recommended - Free Tier)
```bash
# Sign up at https://planetscale.com
# Create database: shieldstack_panel
# Get connection string
# Format: mysql://user:pass@host/database
```

#### Option B: AWS RDS
```bash
# Create MySQL 8.0 instance
# Security group: Allow your IP
# Note down endpoint, username, password
```

#### Option C: DigitalOcean Managed Database
```bash
# Create MySQL database cluster
# Download CA certificate
# Get connection details
```

### Step 3: Import Database Schema

```bash
# Connect to your remote database
mysql -h your-host.com -u your-user -p your-database < database_schema.sql
mysql -h your-host.com -u your-user -p your-database < ai_editor_migration.sql

# Or use phpMyAdmin/MySQL Workbench to import
```

### Step 4: Configure Environment Variables

```bash
# Set environment variables in Vercel
vercel env add DB_HOST production
# Enter your database host (e.g., mysql.planetscale.com)

vercel env add DB_NAME production
# Enter: shieldstack_panel

vercel env add DB_USER production
# Enter your database username

vercel env add DB_PASS production
# Enter your database password

vercel env add AI_OPENAI_KEY production
# Enter: sk-your-openai-api-key

vercel env add AI_SSH_ENCRYPTION_KEY production
# Generate with: php -r "echo bin2hex(random_bytes(32));"
```

### Step 5: Deploy

```bash
# Deploy to production
vercel --prod

# Your app will be at: https://your-app.vercel.app
```

---

## ⚙️ What Works on Vercel

✅ **Working Features:**
- User authentication
- Dashboard viewing
- Customer management
- Invoice viewing
- Ticket system (basic)
- Plans browsing
- Profile management

❌ **NOT Working on Vercel:**
- AI Website Editor (no SSH2 extension)
- File uploads
- Persistent sessions
- Background jobs
- SSH connections
- Automated backups

---

## 🔧 Configuration for Vercel

The `vercel.json` file is already configured:

```json
{
  "version": 2,
  "functions": {
    "api/**/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  }
}
```

### Update includes/config.php

Make it read from environment variables:

```php
<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'shieldstack_panel');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
?>
```

---

## 📊 Vercel Limitations

| Feature | Status | Workaround |
|---------|--------|------------|
| MySQL | ❌ Not included | Use PlanetScale/AWS RDS |
| PHP Runtime | ⚠️ Experimental | Limited to serverless functions |
| SSH2 Extension | ❌ Not available | AI Editor won't work |
| File Storage | ❌ Not persistent | Use S3/Cloudinary |
| Execution Time | ⚠️ 10s timeout | May cause issues |
| Memory | ⚠️ 1GB limit | Should be enough |
| HTTPS | ✅ Automatic | Works great |
| Custom Domain | ✅ Supported | Works great |

---

## 🎯 Better Alternatives for Full Stack

### 1. Docker (Best for Testing)
```bash
docker-compose up -d
# Everything works, no limitations
# Access at http://localhost:8080
```

### 2. Railway.app (Best for Deployment)
```bash
railway init
railway add mysql
railway up
# Full PHP + MySQL support
# AI Editor works
# $5/month after free tier
```

### 3. Render.com
```bash
# Connect GitHub repo
# Add MySQL database
# Auto-deploy on push
# Free tier available
```

### 4. Traditional PHP Hosting
- Namecheap: $2/month
- Hostinger: $2/month
- SiteGround: $4/month
- **Full support for everything**

---

## 🐛 Common Vercel Issues

### "Database connection failed"
**Problem:** Can't connect to database
**Solution:**
- Check DB_HOST includes port if needed
- Ensure database allows connections from Vercel IPs
- For PlanetScale, use provided connection string

### "SSH2 functions undefined"
**Problem:** AI Editor trying to use SSH2
**Solution:**
- SSH2 won't work on Vercel
- Disable AI Editor features
- Use different platform for AI Editor

### "Session data lost on refresh"
**Problem:** Serverless = no persistent storage
**Solution:**
- Use database-backed sessions
- Or accept this limitation for demos

### "Timeout error"
**Problem:** Operation takes >10 seconds
**Solution:**
- Optimize database queries
- Use indexes
- Or upgrade to Pro plan (60s timeout)

---

## 📝 Deployment Checklist

Before deploying to Vercel:

- [ ] Set up external MySQL database
- [ ] Import database schemas
- [ ] Configure environment variables
- [ ] Update config.php to use env vars
- [ ] Test database connection locally
- [ ] Deploy to Vercel
- [ ] Test basic features
- [ ] Disable AI Editor (won't work)
- [ ] Set up custom domain (optional)
- [ ] Configure DNS (if using custom domain)

---

## 🚀 Quick Deploy Commands

```bash
# Login to Vercel
vercel login

# Link project (first time)
vercel link

# Set environment variables
vercel env add DB_HOST production
vercel env add DB_NAME production
vercel env add DB_USER production
vercel env add DB_PASS production
vercel env add AI_OPENAI_KEY production
vercel env add AI_SSH_ENCRYPTION_KEY production

# Deploy
vercel --prod

# View logs
vercel logs

# Open in browser
vercel open
```

---

## 💡 Recommended Workflow

### For Testing UI/Frontend:
1. ✅ Deploy to Vercel
2. ✅ Use PlanetScale for database
3. ✅ Test basic features
4. ✅ Get feedback on design

### For Full Testing (Including AI Editor):
1. ✅ Use Docker locally
2. ✅ Or deploy to Railway.app
3. ✅ Test all features
4. ✅ Verify SSH connections work

### For Production:
1. ✅ Use Railway.app, Render.com, or traditional hosting
2. ✅ NOT Vercel (too many limitations)
3. ✅ Ensure all features work
4. ✅ Set up monitoring

---

## 📞 Getting Help

If deployment fails:

1. Check Vercel logs: `vercel logs`
2. Verify environment variables: `vercel env ls`
3. Test database connection from local machine
4. Review error messages in Vercel dashboard
5. Consider using Docker instead

---

## 🎯 Summary

| Aspect | Status |
|--------|--------|
| **Vercel for Production** | ❌ NOT Recommended |
| **Vercel for Demo** | ✅ Acceptable |
| **AI Editor on Vercel** | ❌ Won't Work |
| **Basic Features on Vercel** | ⚠️ Mostly Works |
| **Docker for Everything** | ✅ Recommended |
| **Railway for Production** | ✅ Recommended |

---

**TL;DR:**
- Vercel works for basic demo/testing only
- AI Editor won't work (no SSH2 extension)
- Need external MySQL database (PlanetScale recommended)
- For production, use Docker, Railway, or traditional PHP hosting

**Want everything to work?** Use:
```bash
docker-compose up -d
```

See `DEPLOYMENT.md` for all deployment options.
See `QUICKSTART.md` for fastest setup.
