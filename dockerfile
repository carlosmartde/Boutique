# Imagen base de PHP con extensiones necesarias
FROM php:8.2-cli

# Instalar dependencias del sistema y extensiones requeridas
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip

# Verificar que GD está habilitado
RUN php -m | grep gd || (echo "GD not loaded" && exit 1)

# Instalar Composer (desde imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear carpeta de trabajo
WORKDIR /app
COPY . .

# Ejecutar composer install dentro del contenedor con GD habilitado
RUN composer install --optimize-autoloader --no-scripts --no-interaction --no-dev

# Exponer el puerto para Laravel
EXPOSE 8000

# Comando de inicio de la app
CMD php artisan serve --host=0.0.0.0 --port=8000
