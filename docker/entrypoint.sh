#!/usr/bin/env bash
set -euo pipefail

# Konstanta default (tanpa env)
DB_HOST="mysql"
DB_PORT="3306"

echo "[entrypoint] menunggu MySQL di ${DB_HOST}:${DB_PORT} ..."
for i in {1..60}; do
  if nc -z "${DB_HOST}" "${DB_PORT}" >/dev/null 2>&1; then
    echo "[entrypoint] MySQL siap."
    break
  fi
  sleep 1
  if [ "$i" -eq 60 ]; then
    echo "[entrypoint] Gagal konek MySQL dalam 60s"; exit 1
  fi
done

cd /var/www/html

# 1) Composer install (sekali saja)
if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] composer install"
  composer install --no-dev --optimize-autoloader --no-interaction
else
  echo "[entrypoint] vendor sudah ada, skip composer"
fi

# 2) Permission storage/cache
echo "[entrypoint] set permission storage & cache"
chmod -R ug+rw storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# 3) Migrate (idempoten) — asumsi config/database.php sudah hard-coded
if [ -f artisan ]; then
  echo "[entrypoint] php artisan migrate --force (abaikan error bila config belum siap)"
  php artisan migrate --force || true

  echo "[entrypoint] optimize caches"
  php artisan config:cache || true
  php artisan route:cache  || true
  php artisan view:cache   || true
fi

echo "[entrypoint] start php-fpm"
exec "$@"
