# Local Setup

Langkah menjalankan ERP CNC di lokal.

## Requirements

- PHP 8.3 atau lebih baru.
- Composer.
- Node.js dan npm.
- Database PostgreSQL atau SQLite untuk development ringan.

## Steps

```bash
cd erp-cnc
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

## Verification

- Buka `/health` untuk cek aplikasi hidup.
- Buka `/admin` untuk masuk panel Filament.
- Pastikan user admin sudah dibuat lewat seeder.
