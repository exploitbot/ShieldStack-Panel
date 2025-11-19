#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
TMP_DIR="$ROOT/uploads/temp"

if [ ! -d "$TMP_DIR" ]; then
  echo "Temp directory $TMP_DIR does not exist; nothing to clean."
  exit 0
fi

find "$TMP_DIR" -mindepth 1 -delete

# Recreate directory structure in case it was removed
mkdir -p "$TMP_DIR"
chown ${WEB_USER:-apache}:${WEB_GROUP:-nginx} "$TMP_DIR" 2>/dev/null || true
chmod ${WEB_MODE:-2775} "$TMP_DIR" 2>/dev/null || true

echo "Temporary uploads cleared in $TMP_DIR"
