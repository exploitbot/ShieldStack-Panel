# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> Update (Nov 2025): AI Editor now supports multi-website selection per customer and multi-session chat (website-scoped sessions with clear/reset). Relevant changes in `/var/www/html/ai-editor/` (new `api/sessions.php`, updated `chat.php/get-session.php`, refreshed UI JS/CSS).

## Project Overview

This is a Claude API usage tracking dashboard built with PHP and vanilla JavaScript. It monitors Claude Code usage by parsing the local history file (`~/.claude/history.jsonl`) and displaying statistics through a web interface.

## Architecture

### Data Sources

The dashboard uses two primary data sources:

1. **Local history file**: `/home/appsforte/.claude/history.jsonl`
   - JSONL format (one JSON object per line)
   - Each entry contains: `display`, `timestamp`, `project`, and optionally `model`
   - Read directly by `index.php` on page load

2. **Credentials file**: `/home/appsforte/.claude/.credentials.json`
   - Contains OAuth tokens and subscription type
   - Used by `get_usage.php` to fetch live API rate limit data

### File Structure

- **`index.php`**: Main dashboard page
  - Server-side: Parses history.jsonl and credentials, extracts subscription type
  - Client-side: Handles time filtering, chart rendering, and live API data integration
  - Single-file architecture with embedded PHP, CSS, and JavaScript

- **`get_usage.php`**: API proxy endpoint
  - Makes authenticated requests to Anthropic API to retrieve rate limit headers
  - Returns JSON with rate limit data and subscription info
  - Called asynchronously by index.php's JavaScript

### Model Detection Logic

Model usage is determined by checking (in order):
1. `model` field in history entry (most reliable)
2. Model name in `display` field text
3. Default to "sonnet" if undetectable

This logic exists in both PHP (for initial page load) and JavaScript (for filtering).

## Development

### Testing Changes

Since this is a web dashboard, test by:
1. Opening the page in a browser: `http://your-server/claudeusage/`
2. Testing mobile responsiveness using browser DevTools
3. Verifying API integration by checking the "Live API data connected" status badge

### File Permissions

Files must be readable by the web server (nginx) but are owned by `appsforte`:
```bash
sudo chown appsforte:appsforte *.php
chmod 644 *.php
```

### Design System

The dashboard uses a dark slate blue theme with cyan accents:
- Primary gradient: `#0f172a` → `#1e293b`
- Accent colors: `#0ea5e9`, `#2563eb`, `#38bdf8`
- Cards use semi-transparent dark backgrounds with glowing borders
- Mobile-first responsive design with breakpoints at 640px and 768px

### Common Issues

**Usage data appears inaccurate:**
- Verify `/home/appsforte/.claude/history.jsonl` exists and is readable
- Check that `get_usage.php` can access the credentials file
- Look for "Live API data connected" status - if missing, API integration failed

**Model breakdown shows all Sonnet:**
- History entries may not have `model` field populated
- This is expected for older Claude Code versions
- Model detection defaults to Sonnet when uncertain

## Data Flow

1. User loads `index.php`
2. PHP reads history.jsonl and credentials.json
3. PHP outputs prompts array and model counts as JSON embedded in page
4. JavaScript renders initial view with 7-day filter
5. JavaScript calls `get_usage.php` asynchronously
6. `get_usage.php` makes live API request to Anthropic
7. API response headers are parsed for rate limit data
8. JavaScript updates progress bars with live data

## Key Dependencies

- **Chart.js 4.4.0**: Line chart rendering (loaded from CDN)
- **PHP with cURL**: Required for `get_usage.php` API calls
- **Anthropic API**: Rate limit headers are fetched from `/v1/messages` endpoint
