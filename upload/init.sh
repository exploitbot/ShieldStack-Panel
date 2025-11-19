#!/usr/bin/env bash
set -euo pipefail

# Basic initializer for the upload web app so copied links stay readable.
# Creates/repairs uploads/, uploads/permanent, and uploads/temp with correct ACL/SELinux.
PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
UPLOAD_DIR="$PROJECT_ROOT/uploads"
TMP_DIR="$UPLOAD_DIR/temp"
PERM_DIR="$UPLOAD_DIR/permanent"

WEB_USER="${WEB_USER:-apache}"
WEB_GROUP="${WEB_GROUP:-nginx}"
WEB_MODE="${WEB_MODE:-2775}"

umask 002

maybe_sudo() {
    if "$@" 2>/dev/null; then
        return 0
    fi

    if command -v sudo >/dev/null 2>&1; then
        sudo "$@"
        return $?
    fi

    return 1
}

for dir in "$UPLOAD_DIR" "$TMP_DIR" "$PERM_DIR"; do
    mkdir -p "$dir"

    if ! maybe_sudo chown "$WEB_USER:$WEB_GROUP" "$dir"; then
        echo "Warning: could not chown $dir to $WEB_USER:$WEB_GROUP. Adjust manually if needed." >&2
    fi

    if ! maybe_sudo chmod "$WEB_MODE" "$dir"; then
        echo "Warning: could not change permissions on $dir to $WEB_MODE. Adjust manually if needed." >&2
    fi

    if command -v setfacl >/dev/null 2>&1; then
        maybe_sudo setfacl -m "g:$WEB_GROUP:rwx" "$dir" || true
        maybe_sudo setfacl -d -m "g:$WEB_GROUP:rwx" "$dir" || true
    fi

    if command -v selinuxenabled >/dev/null 2>&1 && selinuxenabled; then
        maybe_sudo chcon -R -t httpd_sys_rw_content_t "$dir" || true
    fi
done

echo "Uploads directory is ready at $UPLOAD_DIR"
echo "  Permanent uploads: $PERM_DIR"
echo "  Temporary uploads: $TMP_DIR"
