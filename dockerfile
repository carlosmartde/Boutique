# ---- Etapa de compilación ----
FROM php:8.2-apache as builder

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mbstring \
    bcmath \
    zip \
    exif \
    pcntl

# Verificar que GD está instalado
RUN php -r 'if(!extension_loaded("gd")) exit(1);'

# Instalar y configurar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de composer primero
COPY composer.json composer.lock ./

# Instalar dependencias ignorando requerimientos de plataforma temporalmente
RUN composer install \
    --no-scripts \
    --no-autoloader \
    --no-dev \
    --ignore-platform-reqs

# Copiar el resto de la aplicación
COPY . .

# Optimizar el autoloader
RUN composer dump-autoload --optimize --no-dev

# Configurar Apache y PHP
RUN a2enmod rewrite
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto por defecto para Apache
EXPOSE 80

# Configurar variables de entorno para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Iniciar Apache
CMD ["apache2-foreground"]
