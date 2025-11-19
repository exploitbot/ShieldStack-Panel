# Quick Start – Slack Screen Bridge Workflow

1. **Find the bot in Slack** (Ctrl/Cmd + K → search name).  
2. **Send a question** – e.g. `What is 2+2?`.  
3. **Wait ~2 seconds.**  
4. **Run `pull 80` or `show 50`** to read the agent’s reply from the log.  
   - Increase the number when answers are long (`pull 300`, `show 250`).  
5. Repeat: send another instruction → wait → `pull`/`show` again.

### Useful Commands

| Command | Description |
|---------|-------------|
| `pull [N]` | Show the last `CLAUDE_SCREEN_PULL_DEFAULT` lines (80 default). |
| `show [N]` | Same as pull but defaults to 50 lines for quick checks. |
| `status` | Confirm the `slack-claude` screen session and log are available. |
| `command <text>` | Runs `/text` inside the agent session (e.g. `command help`). |
| `logpath on/off` | Reveal or hide the log directory shown in replies. |
| `help` | Show a short reference directly in Slack. |
| `/agent status` | Check the Python bridge (systemd service). |
| `/agent restart` | Restart the bridge (does not restart screen). |
| `/agent stop` | Send Ctrl+C to the agent if it’s stuck. |
| `command connectvpn` | Built-in VPN helper (also `autoconnectvpn`, `stopvpn`). |

### When Something Looks Off

- If `pull`/`show` only shows UI hints, wait a bit longer or run `pull 200`. The bridge sends a real Enter keystroke, so the agent will respond once it finishes thinking.  
- Still blank? Attach with `screen -r slack-claude` to confirm the CLI is running, then detach (Ctrl+A → D).  
- Make sure someone ran `./init.sh start` (or at least `./init.sh screen`) after rebooting the server.

### Need More Help?
Check `AGENTS.md`, `START_HERE.txt`, or visit the docs portal at `http://shieldstack.dev:9090/slackaiagent/`.***
