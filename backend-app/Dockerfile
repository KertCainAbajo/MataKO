# MataKo backend + admin, as one container for Render (or any Docker host).
# Built in three steps so the final image has no build tools, Node.js or Composer in it.

# 1. Admin styles and icons (Tailwind + Vite)
# Node 24 ships npm 11, the same npm that wrote package-lock.json; if the strict install still disagrees
# with the lock file, fall back to a normal install rather than failing the deploy.
FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts --no-audit --no-fund \
    || (echo "npm ci failed; falling back to npm install" && npm install --ignore-scripts --no-audit --no-fund)
COPY . .
RUN npm run build

# 2. PHP libraries (no development tools)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction --no-scripts

# 3. The server: PHP 8.4 with Apache
FROM php:8.4-apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql gd opcache \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# Serve only the public/ folder; hide server and PHP versions; production PHP settings.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}/>!; s!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && printf 'ServerTokens Prod\nServerSignature Off\nTraceEnable Off\n' > /etc/apache2/conf-enabled/zz-matako-security.conf \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'expose_php=Off\nupload_max_filesize=4M\npost_max_size=8M\nopcache.validate_timestamps=0\n' > "$PHP_INI_DIR/conf.d/zz-matako.ini"

WORKDIR /var/www/html
COPY --from=vendor /app ./
COPY --from=assets /app/public/build ./public/build
COPY --from=assets /app/public/ionicons ./public/ionicons
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/start.sh

CMD ["docker/start.sh"]
