# Slack Claude Bot - Complete Guide

**Last Updated**: November 12, 2025
**Version**: Screen Bridge Architecture v2.0

---

## 🚀 Quick Start (Most Important!)

### After Every Reboot
```bash
cd /home/appsforte/slackaiagent
./init.sh start
```

That's it! One command does everything.

### Test It Works
1. In Slack, DM the bot: **"What is 2+2?"**
2. Wait 2-3 seconds
3. Send: **"pull"**
4. You should see Claude's response!

---

## 📚 Documentation Locations

### Web Documentation (Recommended)
**Main Hub**: http://shieldstack.dev:9090/slackaiagent/

Available pages:
- `index.html` - Main documentation hub ⭐
- `QUICK_REFERENCE.html` - Terminal-style quick reference
- `commands.html` - Complete command guide
- `troubleshooting.html` - Troubleshooting guide
- `technical.html` - Technical architecture
- `api-links.html` - API and integration links

### Local Documentation
Location: `/home/appsforte/slackaiagent/`

Files:
- `AGENTS.md` - Complete operator guide ⭐
- `README.md` - Quick start guide
- `START_HERE.txt` - Bootstrap instructions
- `IMPLEMENTATION_SUMMARY.md` - Technical implementation details
- `COMPLETE_GUIDE.md` - This file
- `init.sh` - Management script (executable)

---

## 🎯 Essential Commands

### Management (via init.sh)

```bash
./init.sh           # Interactive menu
./init.sh start     # Full setup (use after reboot) ⭐
./init.sh test      # Check if everything is working
./init.sh logs      # View recent logs
./init.sh attach    # Attach to Claude session (debug)
./init.sh stop      # Stop everything
```

### Slack Commands

**In direct message or mention the bot:**

| Command | What It Does |
|---------|--------------|
| `pull` | Get last 80 lines from Claude |
| `pull 200` | Get last 200 lines (max 400) |
| `status` | Check system health |
| `help` | Show available commands |
| Anything else | Sent to Claude for processing |

**Slash commands:**

| Command | What It Does |
|---------|--------------|
| `/agent status` | Check service status |
| `/agent restart` | Restart the bridge |
| `/agent stop` | Send Ctrl+C to Claude |
| `/agent recent` | Show recent output |

### Service Commands

```bash
# Check service status
systemctl status slack-claude-bot

# Restart service
sudo systemctl restart slack-claude-bot

# View service logs
sudo journalctl -u slack-claude-bot -f

# View last 50 log lines
sudo journalctl -u slack-claude-bot -n 50
```

### Screen Session Commands

```bash
# List all screen sessions
screen -ls

# Attach to Claude session
screen -r slack-claude

# Detach from session (when attached)
# Press: Ctrl+A, then D

# Kill the screen session
screen -S slack-claude -X quit
```

---

## 🏗️ System Architecture

### Components

```
┌─────────────────┐
│   Slack User    │
└────────┬────────┘
         │ sends message
         ▼
┌─────────────────┐
│   Slack Bot     │ (slack_bot.py)
│  Socket Mode    │
└────────┬────────┘
         │ forwards to
         ▼
┌─────────────────┐
│ Screen Bridge   │ (screen_agent_bridge.py)
│                 │
└────────┬────────┘
         │ injects via `screen -X stuff`
         ▼
┌─────────────────┐
│  GNU Screen     │ (slack-claude)
│   Session       │
└────────┬────────┘
         │ running
         ▼
┌─────────────────┐
│   Claude CLI    │ (--dangerously-skip-permissions)
│  Full Powers    │
└────────┬────────┘
         │ output logged
         ▼
┌─────────────────┐
│   Log File      │ (logs/claude_screen.log)
│                 │
└────────┬────────┘
         │ retrieved via
         ▼
┌─────────────────┐
│  Pull Command   │ → Back to Slack User
└─────────────────┘
```

### Key Locations

| Item | Path |
|------|------|
| Project Directory | `/home/appsforte/slackaiagent` |
| Log File | `/home/appsforte/slackaiagent/logs/claude_screen.log` |
| Service File | `/etc/systemd/system/slack-claude-bot.service` |
| Environment Config | `/home/appsforte/slackaiagent/.env` |
| Screen Session | `slack-claude` |
| Web Docs | `/var/www/html/slackaiagent/` |

---

## 🔧 Troubleshooting

### Common Issues

| Problem | Solution |
|---------|----------|
| Empty `pull` results | Wait longer - Claude is still processing |
| Old/stale output | Run `./init.sh start` to restart |
| Bot not responding | Check `./init.sh test` |
| Service crashed | Run `sudo systemctl restart slack-claude-bot` |
| Screen session missing | Run `./init.sh screen` or `./init.sh start` |
| Can't find logs | Check `/home/appsforte/slackaiagent/logs/claude_screen.log` |

### Debug Workflow

1. **Quick health check:**
   ```bash
   ./init.sh test
   ```

2. **View logs:**
   ```bash
   ./init.sh logs
   ```

3. **If issues persist, restart everything:**
   ```bash
   ./init.sh start
   ```

4. **For deep debugging, attach to Claude:**
   ```bash
   ./init.sh attach
   # Press Ctrl+A then D to detach
   ```

5. **Check service logs:**
   ```bash
   sudo journalctl -u slack-claude-bot -n 50
   ```

---

## ⚙️ Configuration

### Environment Variables (.env)

| Variable | Description | Default |
|----------|-------------|---------|
| `SLACK_BOT_TOKEN` | Bot OAuth token (xoxb-...) | Required |
| `SLACK_APP_TOKEN` | App-level token (xapp-...) | Required |
| `AGENT_COMMAND` | Command to run bridge | `python3 .../screen_agent_bridge.py` |
| `AGENT_RESPONSE_TIMEOUT` | Response timeout (seconds) | 120 |
| `CLAUDE_SCREEN_SESSION` | Screen session name | `slack-claude` |
| `CLAUDE_SCREEN_LOG` | Log file path | `logs/claude_screen.log` |
| `CLAUDE_SCREEN_PULL_DEFAULT` | Default lines to pull | 80 |
| `CLAUDE_SCREEN_PULL_MAX` | Max lines allowed | 400 |

### Important Files

| File | Purpose |
|------|---------|
| `init.sh` | Main management script |
| `slack_bot.py` | Slack bot handler |
| `screen_agent_bridge.py` | Bridge to screen session |
| `agent_manager.py` | Process manager |
| `main.py` | Entry point |
| `.env` | Configuration |

---

## 📖 How to Use This Bot

### Typical Workflow

1. **Send a complex request to the bot:**
   ```
   "Create a Python script that lists all files in the current directory"
   ```

2. **Wait for Claude to process** (usually 2-5 seconds)

3. **Pull the response:**
   ```
   pull
   ```

4. **Review Claude's response and code**

5. **If response is long, pull more:**
   ```
   pull 200
   ```

### Tips for Best Results

- **Wait before pulling**: Give Claude 2-3 seconds to process
- **Use specific line counts**: If you know response is long, use `pull 300`
- **Check status first**: If unsure, send `status` to verify system health
- **Multiple turns**: You can have back-and-forth conversations - just send new messages and pull
- **Complex tasks**: Claude has full Edit and Bash capabilities, so complex multi-file edits work

### Example Session

```
You: "What files are in the current directory?"
You: "pull"
Bot: [Shows ls output from Claude]

You: "Create a test.txt file with 'Hello World'"
You: "pull"
Bot: [Shows Claude creating the file]

You: "Show me the contents of test.txt"
You: "pull"
Bot: [Shows file contents]
```

---

## 🚨 Critical Information

### After Every Reboot

**YOU MUST RUN:**
```bash
cd /home/appsforte/slackaiagent
./init.sh start
```

This starts:
- ✅ Claude in screen session
- ✅ Configures environment
- ✅ Restarts Slack service
- ✅ Tests everything works

### Sudo Password

If prompted: `Bbcimlr!`

### Screen Session Details

- **Name**: `slack-claude`
- **Log**: `/home/appsforte/slackaiagent/logs/claude_screen.log`
- **Attach**: `screen -r slack-claude`
- **Detach**: `Ctrl+A` then `D`

### Service Details

- **Name**: `slack-claude-bot.service`
- **User**: `appsforte`
- **Auto-restart**: Yes (on failure, 10s delay)

---

## 📝 Maintenance

### Regular Tasks

**Weekly:**
- Check log file size: `du -h logs/claude_screen.log`
- If >100MB, consider truncating: `truncate -s 0 logs/claude_screen.log`

**After Updates:**
- Restart service: `sudo systemctl restart slack-claude-bot`
- Test: `./init.sh test`

**After System Updates:**
- Full restart: `./init.sh start`

### Updating Documentation

1. Edit files in `/home/appsforte/slackaiagent/`
2. Copy to web: `cp FILE /var/www/html/slackaiagent/`
3. Update timestamps

---

## 🎓 Learning More

### For Operators
- Start with: `AGENTS.md`
- Web hub: http://shieldstack.dev:9090/slackaiagent/
- Quick reference: `QUICK_REFERENCE.html`

### For Developers
- Implementation: `IMPLEMENTATION_SUMMARY.md`
- Architecture: `technical.html`
- Code: Review Python files in project directory

### For Troubleshooting
- Quick fixes: `troubleshooting.html`
- Debug: `./init.sh attach`
- Logs: `./init.sh logs`

---

## ✅ Success Checklist

After following this guide, you should be able to:

- [ ] Run `./init.sh start` after reboot
- [ ] Send messages to bot in Slack
- [ ] Use `pull` to get responses
- [ ] Check system status with `./init.sh test`
- [ ] Access web documentation
- [ ] Troubleshoot common issues
- [ ] Attach to screen session for debugging
- [ ] Restart the service when needed

---

## 🆘 Quick Help

**Something broken?**
```bash
./init.sh start
```

**Need to see what's happening?**
```bash
./init.sh attach
```

**Want to check status?**
```bash
./init.sh test
```

**Need documentation?**
http://shieldstack.dev:9090/slackaiagent/

---

**Remember**: `./init.sh start` fixes most problems!

---

*Last updated: November 12, 2025*
*Location: /home/appsforte/slackaiagent/*
*Version: Screen Bridge Architecture v2.0*
