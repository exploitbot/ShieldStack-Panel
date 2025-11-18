#!/bin/bash

echo "================================================"
echo "ShieldStack Panel - Quick Setup Script"
echo "================================================"
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
   echo "⚠️  Please don't run as root"
   exit 1
fi

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "ℹ $1"
}

# Check PHP installation
echo "Checking requirements..."
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed"
    echo "Install PHP 7.4+ with: sudo apt-get install php php-mysql php-mbstring"
    exit 1
else
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    print_success "PHP $PHP_VERSION installed"
fi

# Check MySQL
if ! command -v mysql &> /dev/null; then
    print_warning "MySQL client not found"
    echo "Install with: sudo apt-get install mysql-client"
else
    print_success "MySQL client installed"
fi

# Check SSH2 extension
if php -m | grep -q ssh2; then
    print_success "SSH2 extension installed"
else
    print_warning "SSH2 extension not installed"
    echo "Install with: sudo pecl install ssh2"
    echo "Then add 'extension=ssh2.so' to php.ini"
fi

echo ""
echo "================================================"
echo "Setting up configuration files..."
echo "================================================"

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
    print_success "Created .env file from template"
    print_warning "IMPORTANT: Edit .env with your actual credentials!"
else
    print_info ".env file already exists"
fi

# Create config.php if it doesn't exist
if [ ! -f "includes/config.php" ]; then
    cp includes/config.example.php includes/config.php
    print_success "Created includes/config.php"
    print_warning "IMPORTANT: Edit includes/config.php with your database credentials!"
else
    print_info "includes/config.php already exists"
fi

echo ""
echo "================================================"
echo "Database Setup"
echo "================================================"

read -p "Do you want to set up the database now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Enter MySQL root password: " -s MYSQL_ROOT_PASS
    echo
    read -p "Enter database name [shieldstack_panel]: " DB_NAME
    DB_NAME=${DB_NAME:-shieldstack_panel}

    echo "Creating database and importing schema..."

    # Create database
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

    if [ $? -eq 0 ]; then
        print_success "Database created"

        # Import base schema
        if [ -f "database_schema.sql" ]; then
            mysql -u root -p"$MYSQL_ROOT_PASS" "$DB_NAME" < database_schema.sql 2>/dev/null
            if [ $? -eq 0 ]; then
                print_success "Base schema imported"
            else
                print_error "Failed to import base schema"
            fi
        fi

        # Import AI editor schema
        if [ -f "ai_editor_migration.sql" ]; then
            mysql -u root -p"$MYSQL_ROOT_PASS" "$DB_NAME" < ai_editor_migration.sql 2>/dev/null
            if [ $? -eq 0 ]; then
                print_success "AI Editor schema imported"
            else
                print_error "Failed to import AI Editor schema"
            fi
        fi
    else
        print_error "Failed to create database"
    fi
fi

echo ""
echo "================================================"
echo "Generating Encryption Key"
echo "================================================"

ENCRYPTION_KEY=$(php -r "echo bin2hex(random_bytes(32));")
print_success "Generated encryption key: $ENCRYPTION_KEY"
echo "Add this to your .env file: AI_SSH_ENCRYPTION_KEY=$ENCRYPTION_KEY"

echo ""
echo "================================================"
echo "File Permissions"
echo "================================================"

# Set proper permissions
chmod 644 includes/config.php 2>/dev/null
chmod 600 .env 2>/dev/null
chmod 755 ai-editor 2>/dev/null
chmod 755 ai-editor/api 2>/dev/null
chmod 755 ai-editor/includes 2>/dev/null

print_success "File permissions set"

echo ""
echo "================================================"
echo "Setup Complete!"
echo "================================================"
echo ""
print_info "Next steps:"
echo "1. Edit .env file with your actual credentials"
echo "2. Edit includes/config.php with database credentials"
echo "3. Configure OpenAI API key in .env"
echo "4. Start PHP development server: php -S localhost:8000"
echo "5. Open browser to: http://localhost:8000"
echo ""
print_warning "Default admin login:"
echo "   Email: eric@shieldstack.dev"
echo "   Password: jinho2310"
echo "   (Change this immediately!)"
echo ""
print_info "For production deployment, see DEPLOYMENT.md"
echo ""
