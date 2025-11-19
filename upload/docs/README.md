# Shieldstack Upload App

This mini-app lives under `/var/www/html/upload` and powers `https://shieldstack.dev/upload`. It lets anyone drop in an image and receive a direct, viewable link hosted from the same folder.

## Stack
- PHP 8.x with built-in file upload handling.
- Plain HTML/CSS (no JS build tooling).

## Quick Start
1. Ensure PHP is enabled for this vhost.
2. Run `./init.sh` once to create/permission the `uploads` directory.
3. Visit `https://shieldstack.dev/upload` and upload an image.
4. Tap the built-in **Copy** button to grab the direct link or relative path instantly.
5. (Optional) On iOS/Android, open the share menu and pick “Add to Home Screen” for a one-tap uploader.

## Configuration
- **Destination**: files land in `uploads/` relative to `index.php`.
- **Size limit**: 10 MB per upload (see `$maxFileSize` in `index.php`).
- **Types allowed**: PNG, JPG/JPEG, GIF, WebP. Extend via `mapExtension()`.
- **Permissions**: `uploads/` should be writable by the web server user. `init.sh` defaults to `WEB_USER=apache` and `WEB_GROUP=nginx` and applies mode `2775` so group ownership is preserved.
- **PWA bits**: `manifest.webmanifest` plus icons in `icons/` enable the “Add to Home Screen” experience.
- **Nginx**: mirror the sample in `docs/nginx-location.conf` so `/upload/uploads/` is served as static content, PHP files route through FPM, and `client_max_body_size` comfortably exceeds the PHP upload limit.
- **Copy-to-clipboard**: implemented with the Clipboard API + fallback. No extra config, but the page must be served over HTTPS for navigator.clipboard to work everywhere (fallback still works).
- **Storage layout**: `uploads/permanent` holds “keep forever” files; `uploads/temp` is the weekly purge pool.

## Permissions & Troubleshooting
- Run `WEB_USER=apache WEB_GROUP=nginx ./init.sh` (or override per host) after deploys/permission resets.
- Script sets `umask 002`, applies setgid (`2775`), and, when available, adds ACL + SELinux context so every future upload stays group-writable and web-readable.
- “Moving the uploaded file failed” almost always means the server cannot write to `uploads/`. Re-run the init script or `chown/chmod` manually.
- `favicon.svg`, `manifest.webmanifest`, and files in `icons/` are all static—serve them with long cache headers for snappier repeat visits.
- If images 404, double-check the `/upload/uploads/` location block and ensure the Nginx worker runs under the same group granted in `init.sh`.

## Temporary Upload Cleanup
- Users can nuke the temp pool instantly with the “Clear temporary uploads” button in the UI (requires confirmation and only touches `uploads/temp`).
- Ops should schedule `cleanup-temp.sh` weekly (e.g. `0 3 * * 0 WEB_USER=apache WEB_GROUP=nginx /var/www/html/upload/cleanup-temp.sh`).
- `cleanup-temp.sh` recreates the temp folder with the same permissions, so the web app keeps working after each wipe.
- Permanent uploads are never deleted automatically; if you set your own retention policy, update the docs and AGENTS accordingly.

## Operational Notes
- The generated links are publicly readable. If you need auth, front the directory with basic auth or move uploads outside the doc root and proxy them.
- Consider enabling HTTPS-only cookies or an allowlist if abuse becomes a problem.
- Log uploads at the web-server level if auditing is required.
