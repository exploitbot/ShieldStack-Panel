FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libssh2-1-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install SSH2 extension
RUN pecl install ssh2-1.3.1 && docker-php-ext-enable ssh2

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 600 .env 2>/dev/null || true \
    && chmod 644 includes/config.php 2>/dev/null || true

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
