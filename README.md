# Antara Cafe

Sistem coffee shop: menu dengan foto produk, pemesanan (pelanggan sendiri atau lewat kasir), pencatatan transaksi, laporan keuangan, dan promosi. Dibangun dengan Laravel + MySQL.

## Status

- [x] Langkah 1: login admin, kategori, produk dengan foto
- [x] Langkah 2: menu pelanggan tanpa login (`/menu`), kasir (`/kasir`), layar TV (`/layar`)
- [x] Langkah 3: riwayat transaksi, stok bahan & resep, laporan keuangan, promo/voucher, poin member
- [ ] Langkah 4: hosting dan go-live

## Halaman

| Alamat | Untuk | Login |
|---|---|---|
| `/menu` (`/menu?meja=5`) | Pelanggan memesan dari HP/tablet | Tidak |
| `/layar` | TV/monitor di kafe | Tidak |
| `/kasir` | Kasir: pesanan masuk, input, bayar, struk | Kasir atau admin |
| `/admin` | Dashboard, laporan, transaksi, produk, stok, promo, member, akun | Admin |

## Cara kerja stok

Setiap bahan (biji kopi, susu, cup, …) punya stok, stok minimum, dan harga rata-rata per satuan.
Resep di setiap produk menentukan berapa bahan yang terpakai per porsi. Saat pesanan **dibayar**,
stok bahan berkurang otomatis dan HPP tercatat di transaksi. Void transaksi mengembalikan stok.
Stok masuk (pembelian), opname, dan barang terbuang dicatat di halaman Stok bahan, dan semua
pergerakan bisa dilihat di kartu stok tiap bahan.

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

Alamat produksi: https://antara.kafehub.id (set `APP_URL` di `.env` server).

Buka http://127.0.0.1:8000/login. Username admin adalah `admin` dan passwordnya tercetak di terminal saat `--seed` (atau isi `ADMIN_USERNAME` dan `ADMIN_PASSWORD` di `.env` sebelum seed). Ganti password lewat nama Anda di pojok kanan atas.

Untuk mencoba laporan dengan data contoh 30 hari (jangan di server produksi):

```sh
php artisan db:seed --class=DemoSalesSeeder
```

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

## Deploy ke Hostinger (shared hosting)

Produksi: https://antara.kafehub.id, repo di-deploy ke `public_html` lewat fitur Git Hostinger
(`composer install` berjalan otomatis). Server tidak menjalankan npm, jadi **jalankan `npm run build`
dan commit `public/build` setiap kali mengubah CSS/JS** (CI akan gagal jika lupa).

- `.htaccess` di root mengarahkan semua permintaan ke `public/`, sehingga `.env` dan kode tidak bisa diakses.
- `.env` produksi hanya ada di server (tidak di Git). Isinya antara lain `APP_ENV=production`,
  `APP_DEBUG=false`, `APP_URL=https://antara.kafehub.id`, `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`,
  dan `PUBLIC_STORAGE_ROOT=public/storage` (Hostinger mematikan `symlink`, jadi `storage:link` tidak dipakai).
- Setelah deploy: `php artisan migrate --force` lalu `php artisan optimize`.
