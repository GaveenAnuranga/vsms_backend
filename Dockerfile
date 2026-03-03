FROM php:8.2-cli

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev libonig-dev libxml2-dev libssl-dev \
    && docker-php-ext-install \
        pdo pdo_pgsql pdo_mysql \
        mbstring zip xml bcmath opcache \
        tokenizer ctype fileinfo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first (layer caching)
COPY composer.json composer.lock ./

# Install Laravel dependencies
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy the rest of the project
COPY . .

# Run post-install scripts now that full app is present
RUN COMPOSER_MEMORY_LIMIT=-1 composer dump-autoload --optimize

# Expose Render port
EXPOSE 10000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000
