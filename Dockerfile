# ===============================
# STAGE 0: BUILD
# ===============================
FROM php:8.2-fpm-alpine AS build

# Instalación de dependencias del sistema
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    curl \
    npm \
    nodejs \
    npm \
    icu-dev \
    zlib-dev \
    && docker-php-ext-install pdo_mysql mbstring intl zip bcmath

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar composer.lock y composer.json para optimizar cache de composer
COPY composer.lock composer.json ./

# Instalar dependencias de PHP
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

RUN composer install --no-dev --optimize-autoloader

# Copiar el resto del proyecto
COPY . .

# Instalar dependencias de Node y compilar assets
RUN npm install
RUN npm run build

# ===============================
# STAGE 1: PRODUCTION
# ===============================
FROM php:8.2-fpm-alpine

# Instalar extensiones necesarias
RUN apk add --no-cache \
    bash \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring intl zip bcmath

WORKDIR /var/www/html

# Copiar todo desde el stage de build
COPY --from=build /var/www/html /var/www/html

# Copiar entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exponer puerto
EXPOSE 8000

# Ejecutar entrypoint al iniciar el contenedor
ENTRYPOINT ["/entrypoint.sh"]
