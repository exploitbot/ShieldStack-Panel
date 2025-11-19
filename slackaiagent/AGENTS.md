# Slack AI Agent Bot – Operator Guide *(updated Nov 12, 2025)*

The underlying AI CLI runs inside a dedicated GNU **screen** session so it can keep Edit/Bash capabilities, but the Slack bot only refers to it as “the agent.” The bridge (`screen_agent_bridge.py`) types messages into that session, presses Enter for you, and lets you pull the latest agent output from the session log.

> FYI (Nov 2025): ShieldStack AI Editor now supports multi-website selection per customer and multi-session chat (clear/reset). If the Slack agent is asked about editor behavior, point to `/var/www/html/ai-editor/` docs.

---

## 1. Current State

- **Service**: `slack-claude-bot.service` ⇒ runs `screen_agent_bridge.py`
- **Agent UI**: launched manually (or via `./init.sh start`) inside screen session `slack-claude`
- **Log file**: `/home/appsforte/slackaiagent/logs/claude_screen.log` (what the `pull`/`show` commands read)
- **Workflow**: send a message → wait ~2 s → run `pull`/`show` to read the reply
- **Fix applied**: the bridge now sends a real carriage return, so every prompt actually submits and the log contains the cleaned response

---

## 2. After-Reboot Quick Start

```bash
cd /home/appsforte/slackaiagent
./init.sh start
```

This single command:
1. Starts/refreshes the `slack-claude` screen session
2. Ensures `.env` uses the screen bridge
3. Restarts `slack-claude-bot.service`
4. Prints quick health checks

**Smoke test in Slack**
1. DM the bot: `What is 2+2?`
2. Wait ~2 seconds
3. Send: `pull 80` (or `show 50`)
4. The agent’s reply appears in Slack (copied from the log)

---

## 3. Manual Bring-Up (if `init.sh` isn’t available)

1. **Dependencies**
   ```bash
   cd /home/appsforte/slackaiagent
   pip3 install -r requirements.txt
   ```

2. **Config**
   ```bash
   cat .env
   # confirm SLACK_BOT_TOKEN, SLACK_APP_TOKEN, and
   # AGENT_COMMAND=python3 /home/appsforte/slackaiagent/screen_agent_bridge.py
   ```

3. **Start the agent CLI inside screen (run as `appsforte`)**
   ```bash
   mkdir -p logs
   screen -L -Logfile logs/claude_screen.log \
          -S slack-claude \
          -dm bash -lc 'cd /home/appsforte/slackaiagent && /home/appsforte/.nvm/versions/node/v24.10.0/bin/claude --dangerously-skip-permissions'
   ```

4. **Restart the Slack bot**
   ```bash
   sudo systemctl restart slack-claude-bot
   sudo systemctl status slack-claude-bot
   ```

---

## 4. Slack Workflow (for everyone)

| Step | Action |
|------|--------|
| 1 | Send any message (natural language or shortcut). The bridge acknowledges with “Sent message…”. |
| 2 | Wait ~`CLAUDE_SCREEN_SEND_DELAY` seconds (default 1.5). |
| 3 | Run `pull 80` or `show 50` (up to `CLAUDE_SCREEN_PULL_MAX`) to fetch the latest log lines. |
| 4 | For long tasks, keep running `pull 200` until the response finishes. |

Messages are injected into screen verbatim, so URLs and special characters are preserved.

Extra commands handled by the bridge:
- `pull [N]` – tail the log (default 80 lines)
- `show [N]` – same as `pull` but defaults to 50 lines (handy for quick glances)
- `status` – confirm screen + log exist
- `logpath on|off` – reveal or hide the log directory in bridge responses
- `command <text>` – send agent slash commands (built-ins: `command connectvpn`, `command autoconnectvpn`, `command stopvpn`)
- `help` – mini cheat sheet

By default the bridge hides the exact log path in Slack replies; run `logpath on` if you need to see it (and `logpath off` to hide it again).

Remember: `/agent status`, `/agent restart`, etc. still work but only manage the Python bridge, **not** the screen session. Use `screen -r slack-claude` if you need to watch the CLI live (Ctrl+A then D to detach).

---

## 5. Management Script (`init.sh`)

```bash
./init.sh          # interactive menu
./init.sh start    # full setup (screen + .env + service)
./init.sh screen   # restart agent screen session only
./init.sh service  # restart slack-claude-bot.service only
./init.sh test     # show quick health report
./init.sh attach   # attach to screen for debugging
./init.sh logs     # show tail of screen + service logs
./init.sh stop     # stop screen session + systemd service
```

The `send-test` option now uses the same carriage-return logic as the bridge, so the test prompt will generate a real agent response.

---

## 6. Environment Variables

| Key | Description | Default |
|-----|-------------|---------|
| `SLACK_BOT_TOKEN` | Slack xoxb token | — |
| `SLACK_APP_TOKEN` | Slack xapp Socket-Mode token | — |
| `AGENT_COMMAND` | Must stay `python3 …/screen_agent_bridge.py` | — |
| `AGENT_RESPONSE_TIMEOUT` | Slack wait time (seconds) | `120` |
| `CLAUDE_SCREEN_SESSION` | Screen session name | `slack-claude` |
| `CLAUDE_SCREEN_LOG` | Log file path | `logs/claude_screen.log` |
| `CLAUDE_SCREEN_PULL_DEFAULT` | Lines returned by `pull` | `80` |
| `CLAUDE_SCREEN_PULL_MAX` | Maximum allowed lines | `400` |
| `CLAUDE_SCREEN_SEND_DELAY` | Hint for how long to wait before pulling | `1.5` |

---

## 7. Troubleshooting Cheat Sheet

| Symptom | Fix |
|---------|-----|
| `pull` shows only UI/hints | Make sure a message was actually sent (bridge reply should mention success). If still blank, attach with `screen -r slack-claude` to verify the CLI is alive, then rerun `pull 200`. |
| `pull` says log missing | Relaunch screen session with `init.sh screen` (log file is created when screen starts). |
| Slack bot says “session not running” | Start screen session or check `screen -ls`. |
| Need to stop agent action | Run `/agent stop` (sends Ctrl+C to the bridge) or attach to screen and press Ctrl+C manually. |
| Log too noisy | `truncate -s 0 logs/claude_screen.log` while the agent is idle. |

---

## 8. To‑Do / Enhancements

1. ANSI cleanup for `pull` output (strip colors/box drawing before posting to Slack).
2. Streaming/tail command (auto-pull every few seconds until stopped).
3. Health check command that verifies *both* the bridge and screen session/log paths.

Keep this file updated whenever the workflow changes. The latest copy also lives at `/var/www/html/slackaiagent/AGENTS.md` for quick reference.
