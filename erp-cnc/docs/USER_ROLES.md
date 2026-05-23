# User Roles

Aplikasi memakai Spatie Permission untuk role dan permission.

## Recommended Roles

- `admin`: akses penuh ke semua resource dan konfigurasi.
- `sales`: kelola customer, quotation, dan follow-up quotation.
- `production`: kelola job order dan progress produksi.
- `finance`: kelola invoice, pembayaran, dan laporan revenue.
- `viewer`: akses baca untuk dashboard dan dokumen tertentu.

## Access Notes

- Admin user awal dibuat lewat seeder.
- Permission sebaiknya mengikuti resource Filament.
- Role production tidak perlu akses edit invoice.
- Role finance tidak perlu akses ubah progress produksi.
