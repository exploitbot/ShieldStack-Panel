# CRITICAL: Claude AI Assistant Rules for ShieldStack.dev

**⚠️ THESE RULES ARE NON-NEGOTIABLE AND MUST BE FOLLOWED AT ALL TIMES ⚠️**

---

## 🔴 THE 4 MANDATORY RULES 🔴

### Rule #1: ALWAYS BACKUP BEFORE CHANGES
**Priority: CRITICAL**

Before making ANY changes to website files, configurations, or code:

```bash
# Create timestamped backup
sudo mkdir -p /var/www/backups/html_backup_$(date +%Y%m%d_%H%M%S)
sudo cp -r /var/www/html/* /var/www/backups/html_backup_$(date +%Y%m%d_%H%M%S)/
```

**This includes:**
- Editing HTML, CSS, or JavaScript files
- Modifying Nginx configurations
- Updating any website content
- Installing new components
- Making any file system changes

**NO EXCEPTIONS. BACKUP FIRST, THEN MODIFY.**

---

### Rule #2: VERIFY VISUAL CHANGES WITH SCREENSHOTS
**Priority: CRITICAL**

When fixing or modifying ANYTHING related to visuals (HTML, CSS, design, layout):

1. Make the changes
2. **IMMEDIATELY** verify using screenshot-website.js:
```bash
node ~/screenshot-website.js https://shieldstack.dev shieldstack-verify-$(date +%Y%m%d_%H%M%S).png
```
3. **READ THE SCREENSHOT** to confirm changes are correct
4. If incorrect, restore from backup and try again

**Visual changes include:**
- Any HTML modifications
- Any CSS changes
- Layout adjustments
- Color scheme updates
- Typography changes
- Adding/removing visual elements
- Responsive design modifications

**YOU MUST VISUALLY VERIFY. DO NOT ASSUME IT WORKS.**

---

### Rule #3: UPDATE DOCUMENTATION
**Priority: CRITICAL**

After completing ANY task or making ANY changes:

1. Update `/var/www/html/documents/SETUP_GUIDE.md` with:
   - What was changed
   - Why it was changed
   - New commands or procedures
   - Any new configurations

2. **MOST IMPORTANT:** Always ensure this file (`CLAUDE_RULES.md`) is preserved and referenced
   - Add a note about what was done
   - Update any relevant sections
   - Keep the 4 rules clearly visible

3. Add date-stamped entries for significant changes

**Example entry format:**
```markdown
## Change Log

### 2025-10-11 - Initial Setup
- Created website with LEMP stack
- Configured SSL with Let's Encrypt
- Implemented cybersecurity-themed design
- Created documentation

### [NEXT DATE] - [Description]
- What was changed
- Why it was changed
- Important notes
```

---

### Rule #4: FOLLOW THE RULES (Meta-Rule)
**Priority: CRITICAL**

- **ALWAYS** read this file before starting any task
- **NEVER** skip any of the above 3 rules
- **ALWAYS** complete all 4 rules for every task
- **NEVER** assume you can skip backups "because it's a small change"
- **NEVER** skip visual verification "because it's just CSS"
- **NEVER** skip documentation "because I'll remember"

**These rules exist to prevent:**
- Data loss
- Broken websites
- Visual bugs going unnoticed
- Loss of institutional knowledge
- Having to rebuild from scratch

---

## 🔧 Standard Operating Procedures

### Before Starting Any Task

1. ✅ Read these rules
2. ✅ Read the SETUP_GUIDE.md
3. ✅ Understand what needs to be done
4. ✅ Create a backup (Rule #1)
5. ✅ Proceed with changes

### After Completing Any Task

1. ✅ Test the changes work
2. ✅ If visual changes: Take screenshot and verify (Rule #2)
3. ✅ Update documentation (Rule #3)
4. ✅ Verify backup was created (Rule #1)
5. ✅ Confirm all 4 rules were followed (Rule #4)

---

## 📋 Quick Reference Commands

### Create Backup
```bash
sudo mkdir -p /var/www/backups/html_backup_$(date +%Y%m%d_%H%M%S)
sudo cp -r /var/www/html/* /var/www/backups/html_backup_$(date +%Y%m%d_%H%M%S)/
```

### Take Screenshot
```bash
cd /home/appsforte
node screenshot-website.js https://shieldstack.dev verify-$(date +%Y%m%d_%H%M%S).png
```

### Test Nginx Configuration
```bash
sudo nginx -t
```

### Reload Nginx
```bash
sudo systemctl reload nginx
```

### View Recent Logs
```bash
sudo tail -50 /var/log/nginx/shieldstack.dev-error.log
```

---

## 🚨 Emergency Procedures

### If Website is Broken

1. **Don't Panic**
2. Check recent backups:
```bash
ls -lt /var/www/backups/
```

3. Restore from most recent backup:
```bash
# List available backups
ls -lt /var/www/backups/

# Restore specific backup (replace with actual backup name)
sudo cp -r /var/www/backups/html_backup_YYYYMMDD_HHMMSS/* /var/www/html/

# Fix permissions
sudo chown -R nginx:nginx /var/www/html
sudo chmod -R 755 /var/www/html

# Reload nginx
sudo systemctl reload nginx
```

4. Take screenshot to verify restoration
5. Document what went wrong in SETUP_GUIDE.md

---

## 💾 Backup Strategy

### When to Create Backups

- **ALWAYS** before making changes (Rule #1)
- Before Nginx configuration changes
- Before installing new software
- Before major updates
- Daily (recommended - set up cron job)

### Backup Retention

- Keep at least 7 days of backups
- Keep weekly backups for 1 month
- Keep monthly backups for 1 year
- Delete old backups to save space:
```bash
# Find backups older than 30 days
find /var/www/backups/ -name "html_backup_*" -mtime +30

# Delete backups older than 30 days (BE CAREFUL)
find /var/www/backups/ -name "html_backup_*" -mtime +30 -exec rm -rf {} \;
```

---

## 📝 Documentation Requirements

### Always Document

- Configuration changes
- New features added
- Bugs fixed
- Commands used
- Reasons for changes
- Troubleshooting steps taken

### Documentation Locations

1. `/var/www/html/documents/SETUP_GUIDE.md` - Main technical documentation
2. `/var/www/html/documents/CLAUDE_RULES.md` - This file (rules and procedures)
3. Code comments - For complex code sections

---

## ⚠️ Common Mistakes to AVOID

1. ❌ Making changes without backup
2. ❌ Not verifying visual changes
3. ❌ Forgetting to update documentation
4. ❌ Assuming small changes don't need backups
5. ❌ Not testing after changes
6. ❌ Not checking Nginx config before reload
7. ❌ Not monitoring logs after changes
8. ❌ Making multiple changes at once (change one thing at a time)

---

## ✅ Best Practices

1. ✅ Always backup first
2. ✅ Test in small increments
3. ✅ Verify each change works
4. ✅ Document everything
5. ✅ Use version control if possible
6. ✅ Keep backups organized
7. ✅ Monitor logs after changes
8. ✅ Follow security best practices

---

## 🎯 Summary: The Golden Workflow

```
┌─────────────────────────────────┐
│ 1. READ THIS FILE (Rules)       │
├─────────────────────────────────┤
│ 2. CREATE BACKUP (Rule #1)      │
├─────────────────────────────────┤
│ 3. MAKE CHANGES                  │
├─────────────────────────────────┤
│ 4. TEST CHANGES                  │
├─────────────────────────────────┤
│ 5. SCREENSHOT IF VISUAL (Rule #2)│
├─────────────────────────────────┤
│ 6. UPDATE DOCS (Rule #3)         │
├─────────────────────────────────┤
│ 7. VERIFY ALL RULES (Rule #4)    │
└─────────────────────────────────┘
```

---

## 📞 Important Information

- **Website:** https://shieldstack.dev
- **Web Root:** /var/www/html
- **Backups:** /var/www/backups
- **Nginx Config:** /etc/nginx/conf.d/shieldstack.dev.conf
- **SSL Cert:** /etc/letsencrypt/live/shieldstack.dev/
- **Logs:** /var/log/nginx/

---

## 🔐 Security Reminders

- Never commit sensitive data to repositories
- Always use HTTPS
- Keep SSL certificates updated
- Monitor security logs regularly
- Keep software updated
- Use strong permissions (755 for dirs, 644 for files)
- Never run services as root unless necessary

---

**Remember: These 4 rules are here to protect the website and make your job easier. Follow them every single time, no exceptions.**

**Last Updated:** October 11, 2025  
**Version:** 1.0

---

**🔴 DO NOT DELETE OR IGNORE THIS FILE 🔴**
