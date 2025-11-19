# Slack Bot Command Reference

Your Slack bot now supports convenient command shortcuts for common operations!

---

## How It Works

The bot recognizes special command patterns and translates them into natural Claude requests.

**Example:**
```
You type:    disk /home
Bot sees:    "Show disk usage for /home"
Claude runs: df -h /home (or similar)
```

You can still use natural language! The bot supports BOTH:
- ✅ Command shortcuts: `disk /home`
- ✅ Natural requests: `check disk usage in /home`

---

## File Operations

| Command | Example | What It Does |
|---------|---------|--------------|
| `read <path>` | `read /etc/hosts` | Read and display file contents |
| `edit <path>` | `edit /var/www/config.php` | Open file for editing |
| `write <path>` | `write /tmp/notes.txt` | Create or write to file |

**Examples:**
```
read /var/log/nginx/error.log
edit /home/user/.bashrc
write /tmp/backup.sh
```

---

## Search & Discovery

| Command | Example | What It Does |
|---------|---------|--------------|
| `find <pattern>` | `find *.py` | Find files matching pattern |
| `search "<text>" in <path>` | `search "error" in /var/log` | Search for text in files |
| `grep <pattern>` | `grep "TODO"` | Search codebase |

**Examples:**
```
find *.conf
search "database" in /etc
grep "import pandas"
```

---

## System Commands

| Command | Example | What It Does |
|---------|---------|--------------|
| `run <command>` | `run ls -la` | Execute bash command |
| `bash <command>` | `bash df -h` | Run bash command |
| `exec <command>` | `exec pwd` | Execute command |
| `ls [path]` | `ls /var/www` | List directory |
| `cd <path>` | `cd /home/user` | Change directory |
| `pwd` | `pwd` | Show current directory |

**Examples:**
```
run systemctl status nginx
bash ps aux | grep python
ls /var/log
cd /home/appsforte
pwd
```

---

## Analysis & Review

| Command | Example | What It Does |
|---------|---------|--------------|
| `analyze <target>` | `analyze script.py` | Analyze and explain code/file |
| `explain <topic>` | `explain how nginx works` | Get detailed explanation |
| `review <path>` | `review /app/main.py` | Code review |

**Examples:**
```
analyze /etc/nginx/nginx.conf
explain authentication flow
review /var/www/api/handler.php
```

---

## Web Operations

| Command | Example | What It Does |
|---------|---------|--------------|
| `fetch <url>` | `fetch https://example.com` | Fetch and summarize webpage |
| `websearch <query>` | `websearch python 3.12 features` | Search the web |

**Examples:**
```
fetch https://docs.python.org/3/whatsnew
websearch best nginx configuration 2024
```

---

## Advanced Operations

| Command | Example | What It Does |
|---------|---------|--------------|
| `ssh <command>` | `ssh user@host "df -h"` | SSH and execute |
| `deploy <target>` | `deploy my-app` | Deploy application |
| `backup <path>` | `backup /important/data` | Create backup |
| `monitor <service>` | `monitor nginx` | Monitor service status |

**Examples:**
```
ssh root@10.100.90.6 "systemctl status nginx"
deploy production-api
backup /var/www/html
monitor postgresql
```

---

## Quick Shortcuts

These are super convenient for common tasks:

| Command | Example | What It Does |
|---------|---------|--------------|
| `disk [path]` | `disk /home` | Check disk usage |
| `mem` | `mem` | Show memory usage |
| `top` | `top` | Show top processes |
| `logs <service>` | `logs nginx` | View service logs |
| `status <service>` | `status apache` | Check service status |

**Examples:**
```
disk /var
mem
top
logs postgresql
status docker
```

---

## Real-World Examples

### System Administration
```
# Check disk space
disk /var

# View error logs
logs nginx

# Check service status
status postgresql

# View running processes
top

# Check memory
mem
```

### Remote Server Management
```
# SSH and check disk
ssh root@10.100.90.6 "df -h"

# Run command on remote
run ssh user@server "systemctl restart app"

# Monitor service
monitor nginx
```

### Development Tasks
```
# Review code
review /app/authentication.py

# Find all Python files
find *.py

# Search for errors
search "Exception" in /var/log/app

# Analyze configuration
analyze /etc/nginx/sites-available/default
```

### File Management
```
# Read config file
read /etc/mysql/my.cnf

# Edit configuration
edit /etc/php/php.ini

# Backup important data
backup /var/www/uploads
```

---

## Bot Management Commands

Use `/agent <command>` for bot management:

| Command | Description |
|---------|-------------|
| `/agent status` | Check if bot is running |
| `/agent help` | Show all commands (detailed) |
| `/agent clear` | Clear conversation (keep session) |
| `/agent newsession` | Fresh start (new session ID) |
| `/agent restart` | Restart bot process |
| `/agent recent` | Show recent output |

---

## Tips & Tricks

### 1. **Commands are Optional**
You can still use natural language:
```
❌ Don't feel forced to use commands
✅ "Check disk usage" works too!
✅ "disk /home" is just faster
```

### 2. **Combine with Natural Language**
Mix commands with explanation:
```
"disk /var and explain what's using the most space"
"read /etc/hosts and show me any suspicious entries"
```

### 3. **Complex Requests**
For complex tasks, natural language is better:
```
✅ "SSH into 10.100.90.6, check disk usage, and restart nginx if disk is above 80%"
❌ Would require multiple commands
```

### 4. **Quick Checks**
Commands are perfect for quick info:
```
disk
mem
top
status nginx
```

### 5. **Piping Not Supported Directly**
Use `run` or `bash` for complex commands:
```
run ps aux | grep python | wc -l
bash df -h | grep /var
```

---

## How Commands Are Processed

1. **You send**: `disk /home`
2. **Bot detects** command pattern
3. **Translates to**: "Show disk usage for /home"
4. **Claude processes** the natural request
5. **Uses appropriate tools** (Bash, Read, etc.)
6. **Returns** formatted response

**Behind the scenes:**
```python
Input:  "disk /home"
        ↓
Parse:  Command detected: disk
        ↓
Convert: "Show disk usage for /home"
        ↓
Claude:  Uses Bash tool → df -h /home
        ↓
Output:  Disk usage statistics
```

---

## Natural Language Still Works!

**These all do the same thing:**
```
disk /home                                    ← Command shortcut
show disk usage for /home                     ← Natural
check how much space is used in /home         ← Natural
what's the disk usage in /home directory?     ← Natural
```

Use whichever feels natural to you!

---

## Getting Help

**In Slack:**
```
/agent help              ← Show full command list
help                     ← Alternative
commands                 ← Alternative
```

**Ask Claude:**
```
"How do I check disk usage?"
"What commands can you run?"
"Show me how to search for errors in logs"
```

---

## Examples by Use Case

### System Monitoring
```
disk                    # Overall disk usage
disk /var              # Specific partition
mem                     # Memory usage
top                     # Process list
status nginx           # Service status
logs nginx             # Recent logs
```

### File Management
```
ls /var/www                              # List files
read /etc/nginx/nginx.conf              # View config
find *.conf                              # Find all config files
search "server_name" in /etc/nginx      # Search in configs
```

### Remote Administration
```
ssh root@10.100.90.6 "systemctl status nginx"
ssh user@host "df -h"
run ssh server "docker ps"
```

### Development
```
review app.py                    # Code review
analyze /src/auth/login.py      # Analyze code
explain how authentication works # Get explanation
find *.py                        # Find Python files
grep "TODO"                      # Search for TODOs
```

---

## Frequently Asked Questions

**Q: Do I have to use commands?**
A: No! Natural language works great. Commands are just shortcuts.

**Q: Can I mix commands and natural language?**
A: Yes! "disk /var and tell me what's using the most space"

**Q: What if Claude doesn't understand my command?**
A: Try natural language or check `/agent help` for correct syntax.

**Q: Can I create custom commands?**
A: Not yet, but you can request new commands to be added!

**Q: Do commands work in channels and DMs?**
A: Yes! Commands work everywhere the bot can see messages.

---

**Last Updated**: 2025-11-12
**Version**: 1.0 (Command support added)
