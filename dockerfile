# Usa la imagen oficial de PHP con extensiones comunes
FROM php:8.2-fpm

# Instala dependencias del sistema necesarias para GD y otras extensiones
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip

# Instala Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia los archivos de tu aplicación
WORKDIR /app
COPY . .

# Instala dependencias de PHP
RUN composer install --optimize-autoloader --no-scripts --no-interaction --no-dev

# Expone el puerto (el mismo que Laravel usa por defecto)
EXPOSE 8000

# Comando de inicio
CMD php artisan serve --host=0.0.0.0 --port=8000
