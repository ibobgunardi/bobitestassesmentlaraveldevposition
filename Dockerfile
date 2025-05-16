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
    supervisor \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . /var/www/

# Create environment file if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env || touch .env; fi
RUN php artisan key:generate --force

# Set permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install dependencies
RUN composer install --no-scripts --no-interaction --no-dev --optimize-autoloader
RUN composer dump-autoload -o
RUN php artisan clear-compiled || true
RUN php artisan optimize || true
RUN npm ci && npm run build

# Copy nginx configuration
COPY docker/nginx/conf.d/app.conf /etc/nginx/conf.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# Expose port 80
EXPOSE 80

# Start with the startup script
CMD ["/usr/local/bin/startup.sh"]
