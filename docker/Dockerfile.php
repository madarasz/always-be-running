# PHP 8.2.x (Laravel 11 minimum)
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring gd exif pcntl bcmath zip

# Install Composer 2.x
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.2.25 --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Create Laravel storage directories
RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
