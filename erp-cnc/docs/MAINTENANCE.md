# Maintenance Notes

Catatan perawatan rutin ERP CNC.

## Daily

- Cek `/health` setelah deploy atau restart service.
- Pantau error di `storage/logs/laravel.log`.
- Cek invoice overdue dari dashboard.

## Weekly

- Backup database production.
- Review job order yang tertunda.
- Review quotation yang belum dikonversi.

## Before Release

- Jalankan `composer test`.
- Jalankan `npm run build`.
- Jalankan `php artisan config:cache` untuk production.
- Pastikan `.env` tidak ikut commit.
