# Usa la imagen oficial de PHP 8.2 (puedes ajustar según tu versión)
FROM php:8.2-cli

# Instala extensiones requeridas
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo_mysql

# Copia los archivos del proyecto
COPY . /app
WORKDIR /app

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala dependencias
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Expone el puerto de Laravel
EXPOSE 8000

# Comando para iniciar Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
