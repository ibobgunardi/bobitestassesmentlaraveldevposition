FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create necessary directories and set permissions
RUN mkdir -p /var/www/storage/app/public \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/testing \
    /var/www/storage/framework/views \
    /var/www/bootstrap/cache

# Set working directory
WORKDIR /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install Composer dependencies (without running scripts)
RUN composer install --no-scripts --no-interaction --no-dev --prefer-dist --no-autoloader

# Copy the rest of the application
COPY . .

# Generate the autoload files
RUN composer dump-autoload -o

# Create environment file if it doesn't exist
COPY .env.example .env.example
RUN if [ ! -f .env ]; then cp .env.example .env || touch .env; fi

# Set proper permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy configuration files
COPY docker/nginx/conf.d/app.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create startup script
RUN echo '#!/bin/bash\n\
php artisan key:generate --force\n\
php artisan storage:link\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan migrate --force\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf\n\
' > /usr/local/bin/startup.sh \
    && chmod +x /usr/local/bin/startup.sh

# Install frontend dependencies and build
COPY package.json package-lock.json* ./
RUN npm ci && npm run build || echo "Frontend build skipped"

# Expose port 80
EXPOSE 80

# Start with the startup script
CMD ["/usr/local/bin/startup.sh"]
