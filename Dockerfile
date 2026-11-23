# ==============================================================================
# Stage 1: Build Frontend Vite Assets (Tailwind CSS v4 + Flux UI)
# ==============================================================================
FROM php:8.4-cli AS builder

RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring zip bcmath intl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY package.json package-lock.json* ./
RUN npm install --no-audit --no-fund

COPY . .

RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs \
    && npm run build \
    && rm -rf node_modules

# ==============================================================================
# Stage 2: Production Runtime Image
# ==============================================================================
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring zip bcmath intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=builder /var/www/html /var/www/html

RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database \
    && touch database/database.sqlite \
    && sed -i 's/\r$//' /var/www/html/docker-entrypoint.sh \
    && chmod -R 775 storage bootstrap/cache database \
    && chmod +x /var/www/html/docker-entrypoint.sh

ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
