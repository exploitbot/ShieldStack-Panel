# AGENTS

> Update (Nov 2025): ShieldStack AI Editor gained multi-website selection + multi-session chat management (see `/var/www/html/ai-editor/`). Keep this in mind if uploads or shared assets are referenced from editor tasks.

## Build Agent
- Owns `index.php`, `shortcutupload.php`, CSS, and the manifest/icon assets in `icons/`.
- Builds/maintains the copy-to-clipboard buttons and storage toggle so sharing links from phones is one tap and permanent uploads stay intuitive.
- Ensures upload validation (size, types, naming) stays aligned with security expectations and that the Shortcut endpoint matches the same rules (shared short tokens + `uploads/temp` path).
- Keeps the page mobile-first so “Add to Home Screen” continues to feel app-like (e.g., theme colors, touch targets) while avoiding heavy assets that slow launch.
- Keeps `shortcutupload.php` compatible with both multipart (`file` field) and raw binary POST bodies from iOS Shortcuts.
- Maintains `/upload/ips/` visitor/IP dashboard (logging JSON + uploads gallery) and keeps it performant/lightweight.
- Notifies Ops before introducing dependencies beyond vanilla PHP/HTML.

## Ops Agent
- Runs `./init.sh` whenever permissions or new hosts need priming; set `WEB_USER`/`WEB_GROUP`/`WEB_MODE` env vars if the defaults (`apache:nginx`, `2775`) differ.
- Confirms ACL/SELinux context were applied (script logs warnings) so new uploads remain world-readable; rerun with `sudo` if necessary.
- Publishes the contents of `icons/`, `manifest.webmanifest`, `howtouseshortcut.php`, and new helper scripts (`cleanup-temp.sh`) when deploying so mobile devices and Shortcut users see the right guidance.
- Keeps `/upload/uploads/` exposed via the Nginx block in `docs/nginx-location.conf`, ensures `client_max_body_size` is >= PHP’s limit, and confirms `shortcutupload.php` is executable via HTTPS for both multipart and raw uploads.
- Monitors `/var/www/html/upload/uploads` for disk usage and purges/archives according to retention policy (temp weekly via cron, permanent as policy dictates).
- Ensures `/upload/ips/visitor-log.json` stays writable (permissions/SELinux) and optionally restricts direct web access if required.
- Ensures a cron (or systemd timer) invokes `cleanup-temp.sh` weekly and that manual wipes via the UI remain enabled in production.
- Keeps HTTPS + correct host headers in place; the direct-link builder and Shortcut endpoint assume TLS and proper `$_SERVER` values.

## QA Agent
- Tests uploads for each MIME type in `mapExtension()` plus boundary cases (exact 10 MB file, >10 MB rejection, unsupported file).
- Confirms generated links render inline in the browser and are reachable over the public URL for both permanent and temporary folders.
- Verifies copy buttons on both desktop (Clipboard API) and mobile (falls back to execCommand) and checks the toast text resets.
- Exercises the “Keep this upload forever” checkbox + manual “Clear temporary uploads” button.
- Verifies `shortcutupload.php` works via iOS Shortcuts for both multipart form uploads (Share Sheet + `file` field) and raw binary POSTs, including optional `storage=permanent` toggles, and that temp uploads land in `uploads/temp`.
- Confirms short file names (8-char token + extension) are returned for both web and Shortcut uploads.
- Verifies PWA affordances: manifest is valid JSON, icons resolve, and iOS/Android surfaces “Add to Home Screen”.
- Checks failure messaging (no file selected, partial uploads, permission errors) after any change to init, Shortcut, or upload flow.
- Validates `/upload/ips/` shows visitor IP chains and renders upload previews/links from both temp and permanent folders.

## Docs Agent
- Keeps `docs/README.md` aligned with reality (limits, destinations, init steps, PWA instructions).
- Maintains `howtouseshortcut.php` (and any related docs) so the Shortcuts recipe matches the current API requirements, including form vs. raw upload options.
- Records any operational runbooks (e.g., log locations, retention strategy) and troubleshooting tips.
- Notes when config knobs change (`$maxFileSize`, new MIME types, CDN/front-end requirements) and that filenames use short tokens.
- Documents cron usage for `cleanup-temp.sh` and ensures manual clear instructions stay accurate.
- Adds `/upload/ips/` usage/expectations (IP logging behavior, gallery paths, log location/rotation) to runbooks/docs.
