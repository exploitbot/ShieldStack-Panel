**Filesystem in use for shieldstack.dev AI Editor**  
- AI Editor root: `/var/www/html/ai-editor/` (customer UI)  
- Admin UI: `/var/www/html/ai-editor/admin/`  
- API: `/var/www/html/ai-editor/api/chat.php`  
- Core includes: `/var/www/html/ai-editor/includes/` (AI client, Anthropic adapter, SSH manager, safety, backup)  
- Assets: `/var/www/html/ai-editor/assets/` (CSS/JS)  
- Shared auth/DB: `/var/www/html/panel/includes/` and `/var/www/html/includes/`  
- Database: `shieldstack_panel` (`system_settings` holds AI endpoint/key/model)  
- Logs: PHP-FPM (`/var/log/php-fpm/www-error.log`), Nginx (`/var/log/nginx/ai.shieldstack.dev-error.log`, `ai_error.log` if present)
- Entry points: `/var/www/html/index.html` (landing), `/var/www/html/login.php` (login), `/var/www/html/panel/` (portal shell)

## Latest Updates (multi-site & multi-session)
- Customers can now have multiple active websites; selection is required before loading chat sessions (persisted in session).
- Chat sessions are website-scoped with a new session drawer + open tab bar; multiple sessions can be open at once.
- Sessions can be cleared (messages + token counters reset) via UI or `POST ai-editor/api/sessions.php` (`action=clear`).
- New API: `ai-editor/api/sessions.php` (list/create/clear); existing `chat.php`/`get-session.php` now enforce `website_id` and `is_active`.
- UI JS overhauled (`assets/js/chat-interface.js`) to manage session tabs, welcome state per website, and localStorage keys per site.

# AI Editor — Agent Guide

## Overview
AI-powered website editor (customer UI + admin UI). Relies on Claude via Clove proxy in Anthropic native format. Core entry points:
- Customer UI: `/ai-editor/`
- Admin UI: `/ai-editor/admin/`
- Chat API: `/ai-editor/api/chat.php`

## Access & Auth
- Requires logged-in panel user session (Auth from `panel/includes/auth.php`).
- Test account: `eric@shieldstack.dev / jinho2310` (rotate in production).
- Admin-only pages guarded with `requireAdmin()` in admin scripts.

## Runtime AI Configuration (DB-driven)
- Table: `system_settings`
- Keys:
  - `ai_openai_endpoint = https://clove.shieldstack.dev/v1/messages`
  - `ai_openai_key = 16efcca21beec3597d3b2c66c49c98788617d8ffa33f314ea5c7aa292915f4ba` (admin key; replace for prod)
  - `ai_model_name = claude-sonnet-4-5-20250929`
- Auth header: `X-API-Key`, not `Authorization: Bearer`
- Only `/v1/messages` is supported; `/v1/chat/completions` returns 405 and is unused.
- Legacy `ai-editor/config*.php` contains `api_key => 'eric'` and `/v1/chat/completions` but is NOT read at runtime.

## Key Files
- API client: `ai-editor/includes/ai-client.php` (loads config from DB; now guards against duplicate `Database` class loads)
- Anthropic adapter: `ai-editor/includes/ai-anthropic-adapter.php`
- Chat endpoint: `ai-editor/api/chat.php`
- Session management: `ai-editor/api/sessions.php` (list/create/clear, website-scoped)
- SSH manager: `ai-editor/includes/ssh-manager.php`
- Safety validator: `ai-editor/includes/safety-validator.php`
- Backup manager: `ai-editor/includes/backup-manager.php`
- Admin pages: `ai-editor/admin/*`
- Styles/JS: `ai-editor/assets/css/ai-editor.css`, `ai-editor/assets/js/chat-interface.js`

## Health Checks (built-in)
- API health: `/ai-editor/admin/` → “Health Checks” card → Run API Health Check (pings Clove via AIClient).
- SSH credential test: `/ai-editor/admin/manage-ssh.php` → “Test Connection” per credential (connects, runs `pwd`, checks web root).

## Plans / Access Control
- Active plan required: `ai_service_plans` (status `active`; -1 tokens for unlimited).
- SSH credentials: `customer_ssh_credentials` (is_active = 1).
- Sessions: `ai_chat_sessions` stores conversation history.

## Logs & Errors
- PHP-FPM: `/var/log/php-fpm/www-error.log`
- Nginx (ai subdomains): `/var/log/nginx/ai.shieldstack.dev-error.log`, `/var/log/nginx/ai_error.log` (if configured).
- Chat failures surface as JSON errors from `ai-editor/api/chat.php`.

## Safety Rails
- Path validation and backups before writes; critical files guarded in `safety-validator`.
- Default plans disallow destructive commands; command whitelist in configuration arrays.
- Always validate web root boundaries before file operations (SSHManager).

## Testing Snippets
- DB config check:
  ```sql
  SELECT `key`, value FROM system_settings WHERE `key` LIKE 'ai_%';
  ```
- List sessions for a site:
  ```bash
  curl -s "https://shieldstack.dev/ai-editor/api/sessions.php?website_id=<ssh_credential_id>" \
    -b "PHPSESSID=<session_cookie>"
  ```
- PHP CLI AI ping:
  ```bash
  php -r "require 'ai-editor/includes/ai-client.php'; \$ai=new AIClient(); var_export(\$ai->sendMessage([['role'=>'user','content'=>'say OK']], 'health'));"
  ```

## Deployment Notes
- Document root: `/var/www/html`
- Requires PHP ssh2 extension for SSH features.
- Cache/backup dirs: `.ai_backups` created alongside target paths by backup manager.
