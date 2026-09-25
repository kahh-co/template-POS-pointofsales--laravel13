# POSIFY — Panduan Merubah / Edit Struktur Project

> Baca ini dulu sebelum edit. Project: **Laravel 13 + Blade + Tailwind v4 + Alpine.js + SQLite (dev) / MySQL (prod)**. Dark minimalis responsif.

## 1. Cara Jalan Cepat

```powershell
cd C:\template_web\templete-pos-laravel13
composer install
npm install
copy .env.example .env   # jika .env hilang
php artisan key:generate
php artisan migrate:fresh --seed   # reset + isi demo
npm run build                       # production CSS/JS
php artisan serve                   # buka http://127.0.0.1:8000
```

Login demo (sinkron dengan `PosifySeeder` + kotak "Akun demo" di halaman login):

| Role | Username / Email | Password |
|------|------------------|----------|
| Owner (admin) | `admin` / `admin@posify.id` | `admin321` |
| Owner | `owner@posify.id` | `password` |
| Kasir | `kasir@posify.id` | `password` |

## 2. Peta Struktur (yang boleh diedit)

```
app/
 Http/Controllers/ AuthController, DashboardController, PosController,
                   ProductController, CategoryController,
                   TransactionController, SettingController
 Http/Middleware/ EnsureOwner.php          # gate role owner (alias 'owner')
 Models/ User(role), Category, Product, Transaction, TransactionItem, Setting
 Services/ CheckoutService.php             # SATU-SATUNYA tempat logika kasir
routes/web.php                             # semua URL di sini
resources/views/
 layouts/app.blade.php                     # sidebar desktop + drawer + bottom-nav
 auth/login.blade.php  dashboard/index  pos/index
 products/index  transactions/index  settings/index  receipt/print
 components/stat-card, badge, empty-state
resources/css/app.css                       # token warna + .card .btn-primary .input + print struk
resources/js/app.js                         # Alpine + Chart.js
database/migrations/2026_09_22_*            # role + 5 tabel posify
database/seeders/PosifySeeder.php           # user + kategori + produk demo
```

## 3. Resep Edit Umum (copy-paste pola)

### A. Ganti warna / tema
Edit `resources/css/app.css` blok `@theme`:
`--color-base:#0F0F0F; --color-surface:#171717; --color-line:#262626; --color-primary:#6366F1;`
Lalu `npm run build`. Jangan edit file `public/build/*` manual.

### B. Tambah menu baru
1. Buat controller + view, 2. tambah route di `routes/web.php` dalam group `auth`,
3. tambah link di `resources/views/layouts/app.blade.php` 3 tempat: sidebar desktop, mobile drawer, bottom-nav/More sheet.

### C. Tambah kolom produk (misal `barcode`)
1. `php artisan make:migration add_barcode_to_products_table`
2. Tambah ke `$fillable` di `app/Models/Product.php`
3. Tambah validasi di `ProductController@store/update`
4. Tambah kolom di `resources/views/products/index.blade.php` (tabel + modal form)
5. `php artisan migrate`.

### D. Ubah logika diskon / pajak / invoice
HANYA edit `app/Services/CheckoutService.php::process()`. Controller `PosController` tipis, jangan taruh hitung di sana. Format invoice ada di method itu: `TRX-YYYYMMDD-XXX`.

### E. Batasi halaman khusus Owner
Bungkus route dengan `Route::middleware('owner')->group(...)`. Middleware ada di `app/Http/Middleware/EnsureOwner.php`, alias didaftar di `bootstrap/app.php`. Saat ini: `settings/store,receipt,tax` owner-only; `settings.index` + lainnya semua role login.

### F. Ganti DB ke MySQL (produksi)
Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=posify
DB_USERNAME=root
DB_PASSWORD=
```
Lalu `php artisan migrate:fresh --seed`. Dev default tetap `sqlite` agar tanpa setup MySQL.

### G. Upload logo / QRIS / foto produk
Tersimpan di `storage/app/public/*`, diakses via `asset('storage/...')`. Wajib `php artisan storage:link` sekali. Validasi image ada di `ProductController` dan `SettingController@updateStore`.

### H. Edit struk
File `resources/views/receipt/print.blade.php` (`#receipt-area`, 80mm). CSS print ada di `app.css` `@media print`. Test via `GET /transactions/{id}/receipt`.

## 4. Aturan Penting (jangan dilanggar)

1. Checkout harus tetap transaksional (`DB::transaction` + `lockForUpdate`) — jangan pindah stok decrement ke controller.
2. Harga snapshot: `transaction_items.price` disalin dari produk saat bayar, jangan relasi live untuk histori.
3. View kasir (`pos/index.blade.php`) pakai `fetch POST pos.checkout` + CSRF — jika ganti URL, update juga `@push('scripts')` di view itu.
4. Chart dashboard (`dashboard/index`) butuh `$chartLabels/$chartData` dari `DashboardController` — jangan hapus variabel itu tanpa update view.
5. `routes/web.php` adalah sumber kebenaran URL — view selalu pakai `route('...')`, jangan hardcode `/pos`.

## 5. Troubleshooting

| Gejala | Perintah |
|--------|----------|
| CSS tidak berubah | `npm run build`, hard refresh |
| 403 Settings | login sebagai owner; kasir hanya boleh lihat, update store/tax diblokir |
| Stok tidak berkurang | cek `CheckoutService`, pastikan request `items:[{product_id,quantity}]` valid |
| Gambar 404 | `php artisan storage:link` |
| Route hilang | `php artisan route:list`, `php artisan config:clear` |

## 6. Status Integrasi (per 25 Sep 2026)

> Belum ada integrasi pihak ketiga real. Semua transaksi 100% lokal via `POST /pos/checkout -> PosController -> CheckoutService`.

### A. Yang BELUM ada
- `Payment Gateway (Midtrans / Xendit / Tripay / Stripe)`: tidak ada SDK, tidak ada `API_KEY`, tidak ada webhook.
- `WhatsApp (Fonnte / Wablas)`: tidak ada.
- `RajaOngkir / ekspedisi`: tidak ada.
- `Realtime (Pusher / Firebase)`: `BROADCAST_CONNECTION=log` saja.
- Catatan QRIS: bukan gateway. Hanya upload manual gambar QRIS di `settings/index.blade.php` + metode bayar manual `Cash/QRIS/Debit/Transfer` di `pos/index.blade.php:114`, dicatat di `CheckoutService::process()` tanpa verifikasi otomatis.

### B. Yang SUDAH ada (bawaan Laravel + frontend)
| Kategori | Detail / file acuan |
|---|---|
| DB | `SQLite` dev, siap `MySQL` prod via `.env` + `config/database.php` |
| Storage lokal | Disk `local`, `storage/app/public/*` via `asset('storage/...')`, wajib `php artisan storage:link` sekali |
| Auth/Session/Cache/Queue | Session `database`, Cache `database`, Queue `database`, Broadcast `log` |
| Mail | `MAIL_MAILER=log` (dummy). Placeholder `postmark/resend/ses` di `config/services.php`, `env` masih kosong |
| Logging | `stack/single`, placeholder `LOG_SLACK_WEBHOOK_URL` di `config/logging.php:78` belum diisi |
| AWS S3 | Key di `.env:59-62` ada tapi kosong, tidak dipakai |
| Frontend | `Vite + Tailwind v4 + Alpine.js + Chart.js + Axios` (`package.json`, `resources/js/app.js`). `GuzzleHttp` hanya transitif dari `laravel/framework`, belum dipakai call API eksternal |

### C. Kalau mau nambah integrasi baru
1. Tambah SDK di `composer.json` / `package.json`, jangan taruh secret hardcode — taruh di `.env` + baca via `config/services.php`.
2. Taruh logic call API di `app/Services/` baru (contoh: `PaymentService.php`), jangan di controller. `CheckoutService` tetap satu-satunya tempat hitung kasir.
3. Tambah route webhook di `routes/web.php` (jangan lupa exclude CSRF kalau perlu) + test via `php artisan route:list`.

Selamat ngoprek — kalau mau nambah fitur besar (member, supplier, multi-store), buat tabel + controller baru, jangan menumpuk ke 5 tabel MVP.
