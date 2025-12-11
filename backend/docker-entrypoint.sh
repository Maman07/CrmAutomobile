#!/bin/bash

# Attendre que PostgreSQL soit prêt
echo "Waiting for PostgreSQL..."
while ! nc -z postgres 5432; do
  sleep 1
done
echo "PostgreSQL is ready!"

# Générer la clé d'application si elle n'existe pas
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

php artisan key:generate --force

# Exécuter les migrations
php artisan migrate --force

# Exécuter les seeders (optionnel, commentez si vous ne voulez pas)
# php artisan db:seed --force

# Nettoyer le cache
php artisan config:cache
php artisan route:cache

# Démarrer Apache
exec "$@"


