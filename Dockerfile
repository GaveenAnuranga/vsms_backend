FROM php:8.2-cli

# v4 - 2026-03-03 - split apt and ext installs into separate RUN steps

# Step 1: system libs
RUN apt-get update -y && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Step 2: pdo extensions
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql

# Step 3: string / utility extensions
RUN docker-php-ext-install mbstring bcmath opcache

# Step 4: zip and xml
RUN docker-php-ext-install zip xml

# Step 5: intl
RUN docker-php-ext-install intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --ignore-platform-reqs

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
