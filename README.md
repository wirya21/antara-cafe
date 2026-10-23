# Antara Cafe

Sistem coffee shop: menu dengan foto produk, pemesanan (pelanggan sendiri atau lewat kasir), pencatatan transaksi, laporan keuangan, dan promosi. Dibangun dengan Laravel + MySQL.

## Status

- [x] Langkah 1: login admin, kategori, produk dengan foto, skema database (transaksi, promo, member)
- [ ] Langkah 2: layar menu TV, menu QR untuk pelanggan, halaman kasir
- [ ] Langkah 3: laporan keuangan dan modul promo
- [ ] Langkah 4: hosting dan go-live

## Menjalankan di komputer sendiri

Butuh PHP 8.3+, Composer, dan Node.js.

```sh
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed   # membuat akun admin + contoh menu
php artisan storage:link     # agar foto produk bisa tampil
php artisan serve
```

Buka http://127.0.0.1:8000/login. Email dan password admin tercetak di terminal saat `--seed` (atau isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env` sebelum seed).

Secara bawaan database lokal memakai SQLite. Untuk MySQL, ubah di `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=antara_cafe
DB_USERNAME=...
DB_PASSWORD=...
```

## Tes

```sh
php artisan test
```
