# Troubleshooting

Panduan cepat masalah umum saat setup atau deploy.

## Blank Page atau 500

- Pastikan `APP_KEY` sudah terisi.
- Cek `storage/logs/laravel.log`.
- Set `APP_DEBUG=true` hanya saat debugging lokal.

## Database Gagal Connect

- Cek host, port, username, dan password database.
- Untuk Supabase pooler, biasanya port `6543`.
- Pastikan IP atau network deployment boleh mengakses database.

## Migration Gagal

- Jalankan dari folder `erp-cnc`.
- Pastikan ekstensi PHP PostgreSQL aktif.
- Jalankan ulang `php artisan migrate:status` untuk melihat migration tertahan.

## Asset Tidak Muncul

- Jalankan `npm install` lalu `npm run build`.
- Pastikan output build masuk ke `public/build`.

## File Upload Tidak Terbaca

- Jalankan `php artisan storage:link`.
- Pastikan permission folder `storage` bisa ditulis aplikasi.
