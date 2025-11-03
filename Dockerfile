FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Configurar e instalar extensión GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Instalar otras extensiones de PHP necesarias
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de dependencias primero (para cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copiar el resto de los archivos
COPY . .

# Instalar dependencias de Node (si usas yarn, cambia a yarn)
RUN npm install

# Compilar assets
RUN npm run build

# Optimizar Laravel
RUN php artisan config:clear && \
    php artisan cache:clear

# Dar permisos a directorios necesarios
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 8080

# Comando de inicio
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}