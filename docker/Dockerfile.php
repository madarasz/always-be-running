# PHP 7.2 with Laravel 6 compatible extensions (Alpine-based to avoid Debian EOL repos)
FROM php:7.2-fpm-alpine

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
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
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
