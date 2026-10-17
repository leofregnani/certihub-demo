# CertiHub Demo

A demo web system to **register participants, issue PDF certificates and validate them publicly**.
Built as a portfolio project: separate **Laravel API** and **Vue 3 SPA**, role-based access, audit logs and a public validation page.

> All data is fictional. Demo accounts use the password `password`, never deploy them as they are.

## Features

- **Access control:** login, logout, password recovery, token sessions with expiry, `admin` and `user` roles
- **Admin panel:** dashboard, participants, courses/events, certificate history with search and filters
- **Certificates:** PDF generation with a unique code (`CRT-2026-XXXXXXXX`), QR code, workload, date and institution data. Names and hours are copied at issuance, so later edits never change an issued certificate
- **Public validation:** `/validate/<code>` shows whether the certificate is valid, revoked or unknown, with basic data only
- **Audit log:** login, logout, user creation, issuance, edits, deletions and public validations (user, date/time, IP, action)
- **Responsive UI** for desktop, tablet and phone

| Role | Can do |
|---|---|
| `user` | Create/edit participants and courses, issue and download certificates |
| `admin` | Everything above, plus deleting records, revoking certificates, managing users and reading the audit log |

## Stack

| Layer | Tech |
|---|---|
| API | PHP 8.3, Laravel 11+, Sanctum, SQLite (MySQL/PostgreSQL ready) |
| PDF / QR | barryvdh/laravel-dompdf, chillerlan/php-qrcode |
| Frontend | Vue 3, Vue Router, Axios, Vite, Tailwind CSS |
| Tests | PHPUnit feature tests (auth, permissions, issuance, validation, logs, PDF) |

## Getting started

Requirements: PHP 8.3+ (extensions `gd`, `mbstring`, `pdo_sqlite`), Composer 2, Node 20+.

**1. Backend.** The Laravel framework itself is not stored in this repo. The script downloads a fresh Laravel and applies the CertiHub code in `backend-src/`:

```bash
./scripts/setup-backend.sh          # Linux / macOS / WSL
# or, on Windows PowerShell:
./scripts/setup-backend.ps1

cd backend && php artisan serve     # http://localhost:8000
```

**2. Frontend.**

```bash
cd frontend
cp .env.example .env
npm install
npm run dev                         # http://localhost:5173
```

Sign in with `admin@example.com` / `password` (admin) or `user@example.com` / `password` (regular user).

**Tests:** `cd backend && php artisan test`

**Password reset in development:** the mail driver is `log`, so the reset link is written to `backend/storage/logs/laravel.log`.

## Project layout

```
backend-src/    Laravel code applied over a fresh skeleton (models, controllers, migrations, routes, PDF view, tests)
frontend/       Vue 3 SPA
scripts/        Setup scripts
docs/API.md     Endpoint reference
```

## Deploying to a VPS (outline)

1. Nginx (or Apache) + PHP-FPM serving `backend/public`; the Vue build (`npm run build`) served as static files with a fallback to `index.html`.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`, MySQL/PostgreSQL credentials, `FRONTEND_URL` and a real mail driver in `backend/.env`. Set `VITE_API_URL` before building the frontend.
3. `composer install --no-dev -o && php artisan migrate --force && php artisan config:cache && php artisan route:cache`.
4. HTTPS with Let's Encrypt, and create real users instead of running the demo seeder.

## Roadmap ideas

E-mail delivery of certificates (queues), digital signature, multiple certificate templates, CSV import of participants, reports, integrations through the API.

## Screenshots

Rendered from the real UI with fictional data: `docs/screenshots/` (dashboard, certificates, validation page, mobile).
