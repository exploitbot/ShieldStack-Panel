# Vercel Quick Start Guide

## ⚡ Deploy to Vercel in 5 Minutes

### Prerequisites
- Vercel account (https://vercel.com)
- External MySQL database (PlanetScale recommended: https://planetscale.com)
- OpenAI API key

---

## 🚀 Quick Deploy Steps

### 1. Install Vercel CLI
```bash
npm install -g vercel
vercel login
```

### 2. Set Up External Database

**Option A: PlanetScale (Recommended - Free Tier)**
1. Sign up at https://planetscale.com
2. Create new database: `shieldstack_panel`
3. Get connection details from dashboard
4. Import schemas:
   ```bash
   # Use PlanetScale CLI or web console to import
   pscale shell shieldstack_panel main < database_schema.sql
   pscale shell shieldstack_panel main < ai_editor_migration.sql
   ```

**Option B: Other MySQL Providers**
- AWS RDS: https://aws.amazon.com/rds/
- DigitalOcean: https://www.digitalocean.com/products/managed-databases
- Aiven: https://aiven.io/mysql (has free tier)

### 3. Configure Environment Variables

Run these commands one by one:

```bash
# Database Configuration
vercel env add DB_HOST production
# Enter: your-database-host.com (e.g., aws.connect.psdb.cloud)

vercel env add DB_NAME production
# Enter: shieldstack_panel

vercel env add DB_USER production
# Enter: your-database-username

vercel env add DB_PASS production
# Enter: your-database-password

# AI Configuration
vercel env add AI_OPENAI_ENDPOINT production
# Enter: https://api.openai.com/v1/chat/completions

vercel env add AI_OPENAI_KEY production
# Enter: sk-your-actual-openai-api-key

# Generate encryption key and add it
vercel env add AI_SSH_ENCRYPTION_KEY production
# Enter the output from: php -r "echo bin2hex(random_bytes(32));"

# Optional: Set environment
vercel env add APP_ENV production
# Enter: production
```

### 4. Deploy

```bash
# Deploy to production
vercel --prod

# Your app will be deployed to: https://your-project.vercel.app
```

---

## ✅ What to Test

After deployment, test these features:

**Working on Vercel:**
- ✅ User login/authentication
- ✅ Dashboard viewing
- ✅ Customer management
- ✅ Invoice viewing
- ✅ Ticket system
- ✅ Profile management
- ✅ Plan browsing

**NOT Working on Vercel (Requires Full Hosting):**
- ❌ AI Website Editor (no SSH2 extension)
- ❌ File uploads (no persistent storage)
- ❌ SSH connections
- ❌ Automated backups

---

## 🐛 Troubleshooting

### "Database connection failed"
```bash
# Test connection locally first
php -r "
\$conn = new PDO('mysql:host=YOUR_HOST;dbname=shieldstack_panel', 'USER', 'PASS');
echo 'Connected successfully!';
"
```

### "Environment variable not found"
```bash
# List all environment variables
vercel env ls

# Pull environment variables to local
vercel env pull .env.local
```

### "502 Bad Gateway"
- Check Vercel function logs: `vercel logs`
- Verify PHP version in vercel.json (should be 8.2)
- Check execution time (10s limit on free tier)

### "CSRF token validation failed"
- This is expected on first load after deployment
- Clear browser cookies and try again
- Sessions may not persist (serverless limitation)

---

## 🎯 Testing Checklist

- [ ] Can access homepage: `https://your-project.vercel.app`
- [ ] Can log in with admin credentials (eric@shieldstack.dev / jinho2310)
- [ ] Dashboard loads without errors
- [ ] Can view customers list
- [ ] Can view invoices
- [ ] Can open a support ticket
- [ ] Profile page displays correctly
- [ ] No console errors in browser DevTools

---

## ⚠️ Important Limitations

**This is a TESTING deployment.** For production, use:

1. **Docker** (Best for full testing)
   ```bash
   docker-compose up -d
   # Access at http://localhost:8080
   ```

2. **Railway.app** (Best for production)
   ```bash
   railway init
   railway add mysql
   railway up
   ```

3. **Traditional PHP Hosting** (Namecheap, Hostinger, SiteGround)
   - Full PHP support
   - Built-in MySQL
   - SSH access for AI Editor

---

## 📊 Expected Costs

**Vercel:**
- Free tier: 100GB bandwidth, 100 serverless function hours
- Pro ($20/mo): More bandwidth, 1000 function hours
- **IMPORTANT:** No MySQL included, need external database

**External Database:**
- PlanetScale: Free tier (5GB storage, 1B reads/month)
- AWS RDS: ~$15/mo (db.t3.micro)
- DigitalOcean: $15/mo (1GB RAM, 10GB storage)

**OpenAI API:**
- GPT-4: ~$0.03 per 1K input tokens, $0.06 per 1K output tokens
- GPT-3.5-turbo: ~$0.001 per 1K tokens (much cheaper)

---

## 🔗 Useful Links

- Vercel Dashboard: https://vercel.com/dashboard
- Vercel CLI Docs: https://vercel.com/docs/cli
- PlanetScale: https://planetscale.com
- OpenAI API Keys: https://platform.openai.com/api-keys
- Full Deployment Guide: See `DEPLOYMENT.md`
- Full Vercel Guide: See `VERCEL_DEPLOYMENT.md`

---

## 💡 Pro Tips

1. **Use PlanetScale for Free MySQL**
   - No credit card required for free tier
   - Automatic backups
   - Simple branching model

2. **Start with GPT-3.5-turbo**
   - Much cheaper than GPT-4
   - Faster response times
   - Good enough for testing

3. **Monitor Your Usage**
   - Check Vercel dashboard for function execution time
   - Watch OpenAI API usage: https://platform.openai.com/usage
   - Set billing alerts

4. **Enable Error Logging**
   ```bash
   # View real-time logs
   vercel logs --follow
   ```

5. **Use Custom Domain (Optional)**
   ```bash
   vercel domains add yourdomain.com
   # Follow DNS configuration instructions
   ```

---

## 📞 Need Help?

**If deployment succeeds but something doesn't work:**
1. Check `vercel logs` for errors
2. Review `AI_EDITOR_TESTING_CHECKLIST.md` for manual tests
3. See `AI_EDITOR_AUDIT_RESULTS.md` for known issues

**If AI Editor doesn't work:**
- This is expected! SSH2 extension not available on Vercel
- Use Docker or Railway for full AI Editor functionality

**If you need full production deployment:**
- See `DEPLOYMENT.md` for all hosting options
- Docker is recommended for development/testing
- Railway or traditional PHP hosting for production

---

**Ready to deploy? Run:**
```bash
vercel --prod
```

**Want full functionality? Run:**
```bash
docker-compose up -d
```
