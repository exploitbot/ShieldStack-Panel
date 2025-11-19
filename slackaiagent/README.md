# Slack AI Agent Bot

This repo hosts a Slack Socket‑Mode bot plus a small “screen bridge.” The underlying AI CLI runs in a GNU **screen** session so Edit/Bash tools keep working, but the Slack bot only refers to it as “the agent.” The bridge pipes Slack messages into that session, presses Enter, and lets users pull the agent’s replies from the session log.

---

## Architecture

```
Slack → slack_bot.py → agent_manager.py → screen_agent_bridge.py
                                                   │
                                                   └── GNU screen session "slack-claude"
                                                         logs → logs/claude_screen.log
```

- `screen_agent_bridge.py` handles `pull`, `pull <N>`, `show <N>`, `status`, `help`, and injects every other message into screen.
- The agent CLI must be running inside screen (`screen -S slack-claude ... claude --dangerously-skip-permissions`).
- Responses are read from `logs/claude_screen.log`; nothing is streamed automatically to Slack.

---

## After-Reboot Checklist

```bash
cd /home/appsforte/slackaiagent
./init.sh start          # full setup (screen + .env + systemd)
```

Then in Slack:
1. Send a question (e.g., “What is 2+2?”).  
2. Wait ~2 seconds.  
3. Send `pull 80` (or `pull 200` for longer replies) to read the latest agent output.

The `send` + `pull` pattern is required because the CLI UI renders inside the screen session.

---

## Slack Commands

| Type | Command | Purpose |
|------|---------|---------|
| Message | `pull` / `pull 150` | Tail the log (default 80 lines; max configurable) |
| Message | `show` / `show 200` | Same as `pull` but defaults to 50 lines |
| Message | `status` / `help` | Check the screen/log or show a quick guide |
| Message | `logpath on/off` | Reveal or hide the log directory in bridge responses |
| Message | `command <text>` | Send agent slash commands (e.g. `command help` → `/help`). Built-ins: `command connectvpn`, `command autoconnectvpn`, `command stopvpn`. |
| Slash | `/agent status` | Is the bridge process running? *(Does not check the screen session.)* |
| Slash | `/agent restart`, `/agent stop`, `/agent clear` | Bridge lifecycle helpers |

Any other message is forwarded straight to the agent; the bridge replies “Sent message …” and reminds you to run `pull`/`show`. The log path stays hidden unless you explicitly run `logpath on`. Messages are injected verbatim, so links, punctuation, and Unicode characters are preserved. Use `/agent stop` whenever you need to cancel a long-running task (sends Ctrl+C).

---

## Manual Operations

1. **Install deps**
   ```bash
   pip3 install -r requirements.txt
   ```

2. **Ensure `.env`**
   ```bash
   AGENT_COMMAND=python3 /home/appsforte/slackaiagent/screen_agent_bridge.py
   SLACK_BOT_TOKEN=xoxb-…
    SLACK_APP_TOKEN=xapp-…
   ```

3. **Start the agent CLI manually**
   ```bash
   mkdir -p logs
   screen -L -Logfile logs/claude_screen.log \
          -S slack-claude \
          -dm bash -lc 'cd /home/appsforte/slackaiagent && /home/appsforte/.nvm/versions/node/v24.10.0/bin/claude --dangerously-skip-permissions'
   ```

4. **Restart the bot**
   ```bash
   sudo systemctl restart slack-claude-bot
   sudo systemctl status slack-claude-bot
   ```

---

## Management Script (`init.sh`)

```bash
./init.sh            # interactive menu
./init.sh start      # screen + config + service (recommended)
./init.sh screen     # restart screen session only
./init.sh service    # restart systemd service only
./init.sh test       # quick health check
./init.sh attach     # attach to screen (Ctrl+A then D to detach)
./init.sh logs       # tail screen + service logs
./init.sh stop       # stop everything
```

The script lives at `/home/appsforte/slackaiagent/init.sh` and runs as the `appsforte` user.

---

## Environment Variables

| Variable | Notes |
|----------|-------|
| `SLACK_BOT_TOKEN` / `SLACK_APP_TOKEN` | Grab from Slack app; bot token must start with `xoxb-`, app token with `xapp-`. |
| `AGENT_COMMAND` | Keep set to `python3 …/screen_agent_bridge.py`. |
| `AGENT_RESPONSE_TIMEOUT` | Slack wait in seconds (default 120). |
| `CLAUDE_SCREEN_SESSION` | Default `slack-claude`. |
| `CLAUDE_SCREEN_LOG` | Default `logs/claude_screen.log`. |
| `CLAUDE_SCREEN_PULL_DEFAULT` / `CLAUDE_SCREEN_PULL_MAX` | Tail lengths (80/400 by default). |
| `CLAUDE_SCREEN_SEND_DELAY` | Hint shown in Slack for how long to wait before running `pull`. |

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `pull` only shows UI banners and no answer | Make sure a question was sent, wait a moment, run `pull 150`. If still empty, attach via `screen -r slack-claude` and check that the CLI is running. |
| Newlines in Slack stay in the composer | Expected—use `pull` to see the response. |
| Screen session missing | `./init.sh screen` or run the manual screen command. |
| Need to stop a long command | `/agent stop` or attach to screen and Ctrl+C. |
| Log huge | `truncate -s 0 logs/claude_screen.log` (while the agent is idle). |

---

## Docs & Web View

- Local files: `AGENTS.md`, `START_HERE.txt`, `QUICK_START.md`, `SETUP_GUIDE.md`, `FIXES_2025-11-12.md`
- Web portal: `http://shieldstack.dev:9090/slackaiagent/`
