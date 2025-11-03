
#!/bin/sh

# Esperar a que la base de datos esté disponible (opcional, útil si Railway tarda en levantar MySQL)
# Ajusta host y port según tu config
echo "Esperando a que la base de datos esté lista..."
until nc -z -v -w30 turntable.proxy.rlwy.net 38355
do
  echo "Esperando 5 segundos..."
  sleep 5
done

echo "Base de datos disponible, continuando..."

# Limpiar y cachear configuración
php artisan config:clear
php artisan config:cache

# Limpiar cache de rutas y vistas
php artisan route:clear
php artisan view:clear

# Ejecutar migraciones si es necesario
php artisan migrate --force

# Iniciar el servidor de Laravel
php artisan serve --host=0.0.0.0 --port=8000
