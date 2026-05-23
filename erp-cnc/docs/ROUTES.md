# Application Routes

Daftar route penting ERP CNC.

| Method | URI | Name | Purpose |
| --- | --- | --- | --- |
| GET | `/` | - | Redirect ke `/admin`. |
| GET | `/health` | - | Health check untuk deployment. |
| GET | `/lang/{locale}` | `lang.switch` | Ganti bahasa aplikasi. |
| GET | `/quotation/approve/{token}` | `quotation.approve` | Approval quotation lewat token. |
| GET | `/quotation/{quotation}/pdf` | `quotation.pdf` | Download PDF quotation. |

## Notes

- Panel utama aplikasi berada di `/admin` melalui Filament.
- Locale yang didukung: `id` dan `en`.
- Route approval memakai token, jadi URL harus dijaga dari akses publik yang tidak perlu.
