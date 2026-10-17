# CertiHub API

Laravel 13 API for CertiHub Demo. Setup, configuration and usage are described in the [root README](../README.md); endpoints are in [docs/API.md](../docs/API.md).

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
php artisan test
```
