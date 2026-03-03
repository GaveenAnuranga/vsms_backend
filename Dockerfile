FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpq-dev libzip-dev libonig-dev libxml2-dev libssl-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install \
    pdo pdo_pgsql pdo_mysql \
    mbstring zip xml bcmath \
    tokenizer ctype fileinfo intl opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy entire project
COPY . .

# Install dependencies (ignore platform reqs to prevent missing-ext false positives)
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --ignore-platform-reqs

# Expose Render port
EXPOSE 10000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000
