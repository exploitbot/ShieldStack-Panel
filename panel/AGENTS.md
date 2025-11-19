**Filesystem in use for shieldstack.dev panel**  
- Portal root: `/var/www/html/panel/` (client UI), `/var/www/html/panel/admin/` (admin)  
- Shared includes: `/var/www/html/panel/includes/` (auth, DB) and `/var/www/html/includes/`  
- Assets: `/var/www/html/assets/` (CSS/JS used by landing/panel shell)  
- Landing redirect: `/var/www/html/index.php` → `/var/www/html/index.html`  

> Update (Nov 2025): AI Editor customer UI now supports multi-website selection and multi-session chat (website-scoped sessions with clear/reset). Core changes live in `/var/www/html/ai-editor/` (see its CLAUDE/AGENTS docs).
> Update (Nov 2025): Admin dashboard has “Browser Cache Control” to bump the global cache-buster (system_settings.cache_buster_version) and force fresh CSS/JS for all users.
> Update (Nov 2025): Product catalog reset to 3 categories / 4 plans only (Website Creation & Management, DNS Management, AI Coding Services). AI plan shows “Contact us” pricing on UI when price=0; DB billing_cycle set to `contact us`.
> Update (Nov 2025): Browse Plans page fixed (removed stray PHP output). All panel links are absolute (`/panel/...`) so they work from ai-editor contexts; plans.php also reachable via root `/plans.php` redirect.
- Login: `/var/www/html/login.php`  
- AI Editor linkage: `/var/www/html/ai-editor/` (customer UI), `/ai-editor/admin/` (admin), `/ai-editor/api/chat.php` (API)  
- Database: `shieldstack_panel` (config in `/panel/includes/config.php` and `/includes/config.php`)  
- Logs: PHP-FPM (`/var/log/php-fpm/www-error.log`), Nginx (`/var/log/nginx/ai.shieldstack.dev-error.log`, `ai_error.log` if present)

# ShieldStack Panel — Agent Guide

## Overview
Customer/admin portal for hosting, billing, support, and AI Editor access. Requires PHP 7.4+ and MySQL. Primary entry points:
- Client dashboard: `/panel/dashboard.php`
- Admin dashboard: `/panel/admin/dashboard.php`
- Shared includes live in `panel/includes/`.

## Access & Auth
- Default admin: `eric@shieldstack.dev / jinho2310` (rotate in production).
- Session/auth: `panel/includes/auth.php`; DB connection: `panel/includes/database.php` with config in `panel/includes/config.php`.
- Sidebar link to AI editor admin is absolute (`/panel/admin/dashboard.php`), AI editor user-facing at `/ai-editor/`.
- Hamburger menu: `panel/assets/js/mobile-menu.js` guarded against double init. Landing menu overlay gap fixed in `styles-v2.css` (full-width overlay on mobile).

## Database
- DSN: `shieldstack_panel` (default user `shieldstack` / `shieldstack123`; root `jinho2310` also works).
- Schema autoloaded in `panel/includes/database.php`; AI settings stored in `system_settings`.
- Key AI rows (read via MySQL): `ai_openai_endpoint`, `ai_openai_key`, `ai_model_name`, `ai_editor_enabled`.
- Plans: `plans` table currently contains only 4 rows (Website Creation & Full Management, Website Management Only, Full DNS Management, Full Autonomous Coding AI). Categories live in `product_categories` (3 active rows).

## Files & Paths
- Client views: `panel/*.php`
- Admin views: `panel/admin/*.php`
- Shared includes/components: `panel/includes/*`, `panel/admin/includes/*`
- Assets: `/panel/assets/*`
- Logs: `/var/log/php-fpm/www-error.log` (PHP), `/var/log/nginx/ai.shieldstack.dev-error.log` (web)
- PWA/app icon used globally: `/upload/uploads/temp/B2-QCJKt.png` (copied to apple-touch-icon, favicons, android-chrome sizes).

## Testing & Health
- Web: hit `/panel/admin/test_system.php` (if present) or basic page loads.
- Database reachability is verified on load via `Database::getInstance()`.
- AI config lives in DB; see AI Editor guide for runtime tests.

## SSH & File Operations
- SSH credential CRUD lives in `ai-editor/admin/manage-ssh.php` (shared DB table `customer_ssh_credentials`).
- Encryption helper: `ai-editor/includes/encryption.php`.

## Safety Notes
- Do not hardcode secrets; prefer DB-stored values.
- Keep `includes/config.php` permissions restrictive.
- Avoid duplicating `Database` class loads in the same request (namespace collision).

## Deployment
- Document root: `/var/www/html`
- Landing redirect handled in `/index.php` (sends to `/index.html`).
- Nginx/PHP-FPM on shieldstack.dev; config reload via `systemctl reload nginx` / `systemctl restart php-fpm` (if permitted).
