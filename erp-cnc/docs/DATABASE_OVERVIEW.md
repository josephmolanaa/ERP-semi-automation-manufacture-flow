# Database Overview

Ringkasan tabel bisnis utama ERP CNC.

## Core Tables

- `customers`: data customer dan kontak perusahaan.
- `quotations`: dokumen penawaran ke customer.
- `quotation_items`: detail item pekerjaan dalam quotation.
- `pos`: purchase order hasil follow-up quotation.
- `job_orders`: pekerjaan produksi berdasarkan PO.
- `job_progress`: catatan progress setiap job order.
- `surat_jalans`: dokumen pengiriman barang.
- `invoices`: tagihan dan status pembayaran.

## Supporting Tables

- `users`: akun admin dan user aplikasi.
- `notifications`: notifikasi aplikasi.
- Permission tables dari Spatie mengatur role dan akses.

## Business Flow

`Customer -> Quotation -> PO -> Job Order -> Surat Jalan -> Invoice`
