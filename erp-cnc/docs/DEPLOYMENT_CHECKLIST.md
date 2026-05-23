# Deployment Checklist

Checklist sebelum deploy ERP CNC.

## Environment

- Isi `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, dan `APP_DEBUG=false`.
- Set `APP_LOCALE=id` dan `APP_FALLBACK_LOCALE=en`.
- Pakai `LOG_LEVEL=warning` untuk production.

## Database

- Pastikan `DB_CONNECTION=pgsql` untuk Supabase/PostgreSQL.
- Isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
- Jalankan `php artisan migrate --force`.
- Jalankan seeder admin jika database masih kosong.

## Runtime

- Jalankan `php artisan storage:link` jika butuh akses file publik.
- Jalankan `php artisan config:cache` setelah env final.
- Pastikan queue memakai `database` atau worker production yang sesuai.
- Verifikasi `/health` setelah deploy.
