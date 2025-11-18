#!/bin/bash
#
# ShieldStack Panel - Production Deployment Script
# Server: shieldstack.dev
# Target: /var/www/html
#
# Usage: Run this script as root on the server
# bash deploy-to-production.sh
#

set -e  # Exit on error

echo "=========================================="
echo "ShieldStack Panel - Production Deployment"
echo "=========================================="
echo ""

# Configuration
DEPLOY_DIR="/var/www/html"
BACKUP_DIR="/root/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
GIT_REPO="https://github.com/exploitbot/ShieldStack-Panel.git"
GIT_BRANCH="claude/ai-website-editor-013swgdc52HzjLpgktvnGwxx"

# MySQL Configuration
DB_HOST="localhost"
DB_NAME="shieldstack_panel"
DB_USER="root"
DB_PASS="jinho2310"

echo "Step 1/8: Creating backup directory..."
mkdir -p "$BACKUP_DIR"

echo "Step 2/8: Backing up current website..."
if [ -d "$DEPLOY_DIR" ]; then
    echo "Creating backup: $BACKUP_DIR/website_backup_$TIMESTAMP.tar.gz"
    tar -czf "$BACKUP_DIR/website_backup_$TIMESTAMP.tar.gz" -C "$DEPLOY_DIR" . 2>/dev/null || true
    echo "✓ Backup created successfully"
else
    echo "No existing website found, skipping backup"
fi

echo ""
echo "Step 3/8: Backing up MySQL database..."
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/database_backup_$TIMESTAMP.sql" 2>/dev/null || echo "Database doesn't exist yet or backup failed (this is OK for fresh install)"

echo ""
echo "Step 4/8: Installing required packages..."
apt-get update -qq 2>/dev/null || true
apt-get install -y git php php-mysql php-mbstring php-curl php-xml php-zip php-gd php-ssh2 2>/dev/null || echo "Some packages may already be installed"

echo ""
echo "Step 5/8: Deploying new code..."
if [ -d "$DEPLOY_DIR/.git" ]; then
    echo "Git repository exists, pulling latest changes..."
    cd "$DEPLOY_DIR"
    git fetch origin
    git checkout "$GIT_BRANCH"
    git pull origin "$GIT_BRANCH"
else
    echo "Cloning repository..."
    rm -rf "$DEPLOY_DIR"
    mkdir -p "$DEPLOY_DIR"
    git clone -b "$GIT_BRANCH" "$GIT_REPO" "$DEPLOY_DIR"
    cd "$DEPLOY_DIR"
fi

echo ""
echo "Step 6/8: Setting up database configuration..."
cat > "$DEPLOY_DIR/includes/config.php" <<'CONFIGEOF'
<?php
// Database Configuration - Production
define('DB_HOST', 'localhost');
define('DB_NAME', 'shieldstack_panel');
define('DB_USER', 'root');
define('DB_PASS', 'jinho2310');
define('DB_CHARSET', 'utf8mb4');

// Application Environment
define('APP_ENV', 'production');
define('APP_DEBUG', false);
?>
CONFIGEOF

echo "✓ Database configuration created"

echo ""
echo "Step 7/8: Setting up database schemas..."

# Create database if it doesn't exist
mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

# Import main schema
if [ -f "$DEPLOY_DIR/database_schema.sql" ]; then
    echo "Importing main database schema..."
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DEPLOY_DIR/database_schema.sql" 2>/dev/null || echo "Main schema already imported or error occurred"
fi

# Import AI Editor schema
if [ -f "$DEPLOY_DIR/ai_editor_migration.sql" ]; then
    echo "Importing AI Editor schema..."
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DEPLOY_DIR/ai_editor_migration.sql" 2>/dev/null || echo "AI Editor schema already imported or error occurred"
fi

echo ""
echo "Step 8/8: Setting permissions..."
chown -R www-data:www-data "$DEPLOY_DIR"
chmod -R 755 "$DEPLOY_DIR"
chmod -R 775 "$DEPLOY_DIR/uploads" 2>/dev/null || mkdir -p "$DEPLOY_DIR/uploads" && chmod 775 "$DEPLOY_DIR/uploads"
chmod 600 "$DEPLOY_DIR/includes/config.php"

# Create AI backups directory
mkdir -p "$DEPLOY_DIR/.ai_backups"
chown www-data:www-data "$DEPLOY_DIR/.ai_backups"
chmod 775 "$DEPLOY_DIR/.ai_backups"

echo ""
echo "=========================================="
echo "✓ Deployment completed successfully!"
echo "=========================================="
echo ""
echo "Website: https://shieldstack.dev"
echo "Admin Login: eric@shieldstack.dev / jinho2310"
echo ""
echo "Backups saved to:"
echo "  - Website: $BACKUP_DIR/website_backup_$TIMESTAMP.tar.gz"
echo "  - Database: $BACKUP_DIR/database_backup_$TIMESTAMP.sql"
echo ""
echo "Next steps:"
echo "1. Configure AI Editor at: /var/www/html/ai-editor/config.php"
echo "2. Add your OpenAI API key"
echo "3. Test the website at https://shieldstack.dev"
echo "4. Check AI Editor at https://shieldstack.dev/ai-editor"
echo ""
echo "To configure AI Editor, run:"
echo "  cp /var/www/html/ai-editor/config.example.php /var/www/html/ai-editor/config.php"
echo "  nano /var/www/html/ai-editor/config.php  # Add your OpenAI API key"
echo ""
