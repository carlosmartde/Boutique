# ---- Etapa base ----
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring bcmath zip exif pcntl \
    && docker-php-ext-enable gd \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear directorio de la app
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Composer
RUN composer install --optimize-autoloader --no-scripts --no-interaction --ignore-platform-reqs

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto (Railway lo asigna automáticamente)
EXPOSE 8080

# ---- Servidor de Laravel ----
# Usamos sh -c para expandir la variable $PORT
CMD sh -c "php -S 0.0.0.0:\${PORT:-8080} -t public"
