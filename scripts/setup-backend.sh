#!/usr/bin/env bash
# Creates backend/ (fresh Laravel + dependencies) and applies the CertiHub code from backend-src/.
# Requirements: PHP >= 8.3 (with gd, mbstring, pdo_sqlite), Composer 2.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [ -d backend ]; then
  echo "backend/ already exists. Remove it first if you want to start over." >&2
  exit 1
fi

echo "==> Creating a fresh Laravel project in backend/"
composer create-project laravel/laravel backend --prefer-dist --no-interaction

cd backend

echo "==> Installing PDF and QR code libraries"
composer require barryvdh/laravel-dompdf "chillerlan/php-qrcode:^5.0" --no-interaction

echo "==> Installing the API scaffolding (Laravel Sanctum)"
php artisan install:api --no-interaction

echo "==> Applying CertiHub code (backend-src/ -> backend/)"
cp -R ../backend-src/. .
cat ../scripts/env.additions >> .env

echo "==> Migrating and seeding demo data"
php artisan migrate:fresh --seed --force

cat <<'MSG'

Done. Start the API with:
  cd backend && php artisan serve        # http://localhost:8000
Run the tests with:
  cd backend && php artisan test
Then you can delete backend-src/ (its files now live in backend/).
MSG
