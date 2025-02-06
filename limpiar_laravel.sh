#!/bin/bash

echo "🚀 Iniciando limpieza de Laravel y Filament..."

# Verificar si Artisan existe
if [ ! -f artisan ]; then
    echo "❌ Error: No se encontró el archivo 'artisan'. Asegúrate de ejecutar este script desde la raíz del proyecto Laravel."
    exit 1
fi

# Limpiar caché de configuración
echo "🧹 Limpiando caché de configuración..."
php artisan config:clear

# Limpiar caché de rutas
echo "🗺️ Limpiando caché de rutas..."
php artisan route:clear

# Limpiar caché de vistas
echo "🖼️ Limpiando caché de vistas..."
php artisan view:clear

# Limpiar caché de aplicaciones
echo "🧑‍💻 Limpiando caché de aplicaciones..."
php artisan cache:clear

# Limpiar caché de eventos
echo "📅 Limpiando caché de eventos..."
php artisan event:clear

# Limpiar caché de Filament
echo "🎨 Limpiando caché de Filament..."
php artisan filament:cache

# Limpiar sesiones antiguas
echo "🗑️ Eliminando sesiones antiguas..."
php artisan session:clear

# Optimizar la aplicación
echo "⚡ Recompilando archivos optimizados..."
php artisan optimize

# Limpiar dependencias obsoletas en storage y bootstrap
echo "🧹 Eliminando archivos temporales..."
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*
rm -rf bootstrap/cache/*

echo "✅ Limpieza completada con éxito."
