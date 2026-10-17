# Creates backend/ (fresh Laravel + dependencies) and applies the CertiHub code from backend-src/.
# Requirements: PHP >= 8.3 (with gd, mbstring, pdo_sqlite), Composer 2.
$ErrorActionPreference = 'Stop'

function Run([scriptblock]$cmd) {
    & $cmd
    if ($LASTEXITCODE -ne 0) { throw "Command failed (exit code $LASTEXITCODE)" }
}

$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (Test-Path backend) {
    throw 'backend/ already exists. Remove it first if you want to start over.'
}

Write-Host '==> Creating a fresh Laravel project in backend/'
Run { composer create-project laravel/laravel backend --prefer-dist --no-interaction }

Set-Location backend

Write-Host '==> Installing PDF and QR code libraries'
Run { composer require barryvdh/laravel-dompdf "chillerlan/php-qrcode:^5.0" --no-interaction }

Write-Host '==> Installing the API scaffolding (Laravel Sanctum)'
Run { php artisan install:api --no-interaction }

Write-Host '==> Applying CertiHub code (backend-src/ -> backend/)'
Copy-Item -Path ..\backend-src\* -Destination . -Recurse -Force
Get-Content ..\scripts\env.additions | Add-Content .env

Write-Host '==> Migrating and seeding demo data'
Run { php artisan migrate:fresh --seed --force }

Write-Host ''
Write-Host 'Done. Start the API with:  cd backend; php artisan serve   (http://localhost:8000)'
Write-Host 'Run the tests with:        cd backend; php artisan test'
Write-Host 'Then you can delete backend-src/ (its files now live in backend/).'
