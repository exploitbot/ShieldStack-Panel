# Slack Slash Command Setup

## Issue: `/agent` command shows error in Slack

If you see an error when typing `/agent` in Slack, it means the slash command isn't registered in your Slack app configuration.

## Fix: Register the `/agent` Slash Command

### Step 1: Go to Slack App Settings
1. Visit https://api.slack.com/apps
2. Select your app (EricAIAgents - App ID: A09SW99R5MX)

### Step 2: Create Slash Command
1. In the left sidebar, click **Slash Commands**
2. Click **Create New Command**

### Step 3: Configure the Command
Fill in the following details:

**Command:** `/agent`

**Request URL:** You can use any URL here since we're using Socket Mode (not webhooks). Use:
```
https://example.com/slack/commands
```
(This URL is never called in Socket Mode, but Slack requires it)

**Short Description:**
```
Control and manage the AI agent bot
```

**Usage Hint:**
```
[status|restart|stop|clear|newsession|help]
```

**Escape channels, users, and links sent to your app:** ☐ (unchecked)

4. Click **Save**

### Step 4: Reinstall App (if needed)
1. Go to **Install App** in the left sidebar
2. If you see a message about reinstalling, click **Reinstall to Workspace**
3. Authorize the permissions

## Available `/agent` Commands

Once registered, you can use:

- `/agent status` - Check if bot is running
- `/agent restart` - Restart the agent process
- `/agent stop` - Stop current Claude operation (Ctrl+C)
- `/agent clear` - Clear conversation context
- `/agent newsession` - Generate new session ID
- `/agent help` - Show all commands

## Alternative: Use Regular Messages

If you can't register slash commands (permissions issue), you can still use the bot by sending regular messages:

Instead of `/agent help`, just type:
```
agent help
```

The bot will understand and respond to commands in regular messages too!

## Verification

After setup, test with:
```
/agent status
```

You should see: `Agent status: *running*`
