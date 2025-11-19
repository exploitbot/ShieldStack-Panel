# Slack AI Agent Bot - Documentation Index

## 📍 You Are Here
`/var/www/html/slackaiagent/` - Web documentation directory

## 🌐 Web Documentation (Recommended)

Access via web browser for best experience with navigation and styling:

- **Main Hub:** `index.html` - Start here
- **Quick Start:** `quick-start.html` - Get running in 30 seconds
- **Commands:** View COMMAND_REFERENCE.md below
- **Troubleshooting:** View FIXES_2025-11-12.md for latest solutions
- **Slash Commands:** View SLACK_COMMAND_SETUP.md for /agent setup

## 📄 Markdown Documentation

These files can be read directly in terminal or text editor:

### Getting Started
- `QUICK_START.md` - 30-second quick start guide
- `SETUP_COMPLETE.txt` - Initial setup confirmation

### Command Reference
- `COMMAND_REFERENCE.md` - Complete guide to all 40+ commands
  - File operations (read, edit, write)
  - System commands (run, bash, exec)
  - Search & discovery (find, search, grep)
  - Quick shortcuts (disk, mem, logs, status)
  - SSH and remote execution
  - Bot management (/agent commands)

### Troubleshooting & Fixes
- `FIXES_2025-11-12.md` - **Latest fixes applied today:**
  - Fixed: Bot stuck in "Processing..." forever
  - Added: /agent stop command (Ctrl+C equivalent)
  - Fixed: /agent slash command registration guide
  - Solution: Single newline vs double newline issue

- `SLACK_COMMAND_SETUP.md` - Register /agent in Slack app
  - Step-by-step registration instructions
  - Workaround if can't register
  - Verification steps

### Technical Documentation
- `technical_details.html` - Architecture and implementation details
- `/home/appsforte/slackaiagent/AGENTS.md` - Main technical reference
  - Now points to this web documentation
  - Contains architecture details
  - Session isolation explanation

## 🔧 Configuration Files

Located in `/home/appsforte/slackaiagent/`:

- `.env` - Bot tokens and configuration
- `.slack_bot_session_id` - Unique session ID (auto-generated)
- `slack_bot.py` - Main bot implementation
- `claude_persistent_wrapper.py` - Claude Code wrapper
- `command_handler.py` - Command parsing (40+ commands)
- `agent_manager.py` - Process management

## 🚀 Quick Reference

### Check Status
```bash
sudo systemctl status slack-claude-bot
sudo journalctl -u slack-claude-bot -f
```

### View Documentation
```bash
# Read markdown files
cat /var/www/html/slackaiagent/QUICK_START.md
cat /var/www/html/slackaiagent/COMMAND_REFERENCE.md
cat /var/www/html/slackaiagent/FIXES_2025-11-12.md

# Or open in web browser
# http://your-server-ip/slackaiagent/
```

### Latest Updates (Nov 12, 2025)
- ✅ Fixed processing loop (single vs double newline)
- ✅ Added /agent stop command
- ✅ Created comprehensive web documentation
- ✅ Added slash command setup guide

## 📚 Documentation Hierarchy

```
/var/www/html/slackaiagent/ (WEB DOCS - YOU ARE HERE)
├── index.html (Main hub with navigation)
├── quick-start.html (Interactive getting started)
├── styles.css (Shared styling)
├── QUICK_START.md (Markdown version)
├── COMMAND_REFERENCE.md (All commands)
├── SLACK_COMMAND_SETUP.md (Slash command setup)
├── FIXES_2025-11-12.md (Latest fixes)
├── technical_details.html (Architecture)
└── README.txt (This directory info)

/home/appsforte/slackaiagent/ (APPLICATION)
├── AGENTS.md (Now points to web docs)
├── slack_bot.py (Main application)
├── claude_persistent_wrapper.py (Wrapper with fix)
├── command_handler.py (40+ commands)
├── .env (Configuration)
└── .slack_bot_session_id (Session ID)
```

## 🎯 Where to Find Information

| Need to... | Look here |
|------------|-----------|
| Get started quickly | `QUICK_START.md` or web `quick-start.html` |
| See all commands | `COMMAND_REFERENCE.md` |
| Fix /agent error | `SLACK_COMMAND_SETUP.md` |
| Solve "Processing..." issue | `FIXES_2025-11-12.md` |
| Understand architecture | `/home/appsforte/slackaiagent/AGENTS.md` |
| Check what's new | `FIXES_2025-11-12.md` (top of file) |

## 🔗 Important Links

- Slack App Management: https://api.slack.com/apps/A09SW99R5MX
- OAuth Tokens: https://api.slack.com/apps/A09SW99R5MX/oauth
- Socket Mode: https://api.slack.com/apps/A09SW99R5MX/socket-mode

## ✅ Current Status

- Service: **RUNNING**
- Session ID: `1189d4f5-6375-4db7-b8e9-b902aba69ad2`
- App ID: `A09SW99R5MX`
- Latest Fix: Nov 12, 2025 (wrapper newline issue resolved)

---

**Last Updated:** November 12, 2025
**Location:** `/var/www/html/slackaiagent/DOCUMENTATION_INDEX.md`
