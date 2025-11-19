# Setup Guide – Screen Session Deployment (Slack AI Agent Bot)

Follow these steps to re-create the current deployment from scratch or after a server reboot.

---

## 1. Prerequisites

- Python 3.8+
- Slack app with Socket Mode enabled (bot + app tokens ready)
- AI CLI installed at `/home/appsforte/.nvm/versions/node/v24.10.0/bin/claude`
- GNU screen available (`apt install screen` if missing)

---

## 2. Install Dependencies

```bash
cd /home/appsforte/slackaiagent
pip3 install -r requirements.txt
```

---

## 3. Configure Slack Tokens

1. Copy `.env.example` → `.env`.
2. Set:
   ```bash
   SLACK_BOT_TOKEN=xoxb-***
   SLACK_APP_TOKEN=xapp-***
   AGENT_COMMAND=python3 /home/appsforte/slackaiagent/screen_agent_bridge.py
   AGENT_RESPONSE_TIMEOUT=120
   ```
3. (Optional) override screen settings:
   ```bash
   CLAUDE_SCREEN_SESSION=slack-claude
   CLAUDE_SCREEN_LOG=/home/appsforte/slackaiagent/logs/claude_screen.log
   CLAUDE_SCREEN_PULL_DEFAULT=80
   CLAUDE_SCREEN_PULL_MAX=400
   CLAUDE_SCREEN_SEND_DELAY=1.5
   ```

---

## 4. Launch the AI CLI Inside GNU Screen

Run the following as `appsforte` every time the server boots (or create a systemd unit later):

```bash
mkdir -p /home/appsforte/slackaiagent/logs
screen -L -Logfile /home/appsforte/slackaiagent/logs/claude_screen.log \
       -S slack-claude \
       -dm bash -lc 'cd /home/appsforte/slackaiagent && /home/appsforte/.nvm/versions/node/v24.10.0/bin/claude --dangerously-skip-permissions'
```

- `-S slack-claude` sets the session name (must match `CLAUDE_SCREEN_SESSION`).
- `-L -Logfile …` saves all terminal output so Slack can read it later.
- Attach with `screen -r slack-claude`; detach with `Ctrl+A, D`.

---

## 5. Start the Slack Bot Service

```bash
sudo systemctl restart slack-claude-bot
sudo systemctl status slack-claude-bot
```

- Unit file path: `/etc/systemd/system/slack-claude-bot.service`
- Logs: `sudo journalctl -u slack-claude-bot -f`

Ensure the service user (`appsforte`) has read/write access to the log directory.

---

## 6. Slack Usage Pattern

1. **Send a prompt** (DM or mention). The bridge injects it into the screen session like keyboard input.
2. **Pull the latest log**:
   ```
   pull 80     # default from CLAUDE_SCREEN_PULL_DEFAULT
   show 50     # alias of pull with a 50-line default
   pull 200    # specify custom line count (max default 400)
   status      # check session/log presence
   logpath on/off  # reveal or hide the log directory in responses
   command help    # sends /help inside the agent CLI
   help        # quick reminder
   ```
3. Repeat as needed; wait `CLAUDE_SCREEN_SEND_DELAY` seconds between send + pull for best results.

### Slash Commands (still supported)

| Command | Description |
|---------|-------------|
| `/agent status` | Checks whether the bridge process is running |
| `/agent restart` | Restarts the bridge (does NOT relaunch screen) |
| `/agent stop` | Sends Ctrl+C to the bridge |
| `/agent recent` | Shows PTY buffer captured by the bridge |

---

## 7. Maintenance

### Screen Session

```bash
screen -ls                              # list sessions
screen -r slack-claude                  # attach
screen -S slack-claude -X quit          # stop
```

### Log Rotation

```bash
truncate -s 0 /home/appsforte/slackaiagent/logs/claude_screen.log
```

### Environment Tweaks

After editing `.env`, restart the service:

```bash
sudo systemctl restart slack-claude-bot
```

---

## 8. Troubleshooting

| Issue | Fix |
|-------|-----|
| `pull` says “log missing” | Recreate the screen session with `-L -Logfile ...` |
| `pull` empty but agent is busy | Wait longer or attach via `screen -r slack-claude` to see what the CLI is doing |
| `/agent status` says running but no output | Means the bridge is healthy but the screen session may be dead; restart the screen command |
| Need more than 400 lines | Increase `CLAUDE_SCREEN_PULL_MAX` and restart the service |
| ANSI noise in Slack | Clean up by piping log through `ansi2txt` before printing (todo) |

---

## 9. Post-Reboot Checklist

1. `pip3 install -r requirements.txt`
2. Verify `.env`
3. Launch screen session (Section 4)
4. `sudo systemctl restart slack-claude-bot`
5. DM the bot → `pull 120`

---

## 10. Future Improvements

- Systemd unit for the screen session (auto restart, log rotation)
- Slack command for continuous tail/stream
- Filter ANSI art before sending to Slack
- Health endpoint that verifies Slack bot + screen session in one command

Refer to `AGENTS.md` for a more narrative runbook and next-step ideas.***
