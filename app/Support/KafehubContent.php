<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Isi halaman kafehub.id yang bisa diubah dari panel admin.
 * Disimpan sebagai satu JSON di pengaturan; bagian yang belum diubah memakai teks bawaan.
 */
class KafehubContent
{
    public const KEY = 'kafehub_content';

    /** Daftar yang bisa ditambah/dikurangi beserta kolomnya. */
    public const LISTS = [
        'stats' => ['label', 'value'],
        'pains' => ['pain', 'fix'],
        'features' => ['icon', 'title', 'text'],
        'flow' => ['icon', 'title', 'text'],
        'reasons' => ['icon', 'title', 'text'],
        'start' => ['title', 'text'],
        'showcase' => ['name', 'url', 'text'],
        'faqs' => ['q', 'a'],
    ];

    public const TEXTS = [
        'whatsapp', 'hero_badge', 'hero_title', 'hero_highlight', 'hero_subtitle',
        'features_title', 'features_subtitle', 'flow_title', 'reasons_title', 'reasons_subtitle',
        'start_title', 'showcase_title', 'cta_title', 'cta_subtitle',
    ];

    public static function defaults(): array
    {
        return [
            'whatsapp' => config('kafehub.whatsapp'),
            'hero_badge' => 'Sudah berjalan di Antara Cafe',
            'hero_title' => 'Kafe Anda,',
            'hero_highlight' => 'jalan lebih rapi.',
            'hero_subtitle' => 'Menu digital, pesan lewat QR, kasir, papan pesanan, stok bahan, sampai laba bersih. Semua dalam satu sistem yang dibuka dari HP. Tanpa aplikasi, tanpa ribet.',
            'stats' => [
                ['label' => 'Pelanggan', 'value' => 'Tanpa install'],
                ['label' => 'Pembayaran', 'value' => 'QRIS · transfer · tunai'],
                ['label' => 'Stok', 'value' => 'Terpotong otomatis'],
            ],
            'pains' => [
                ['pain' => 'Antrean panjang di kasir saat ramai', 'fix' => 'Pelanggan pesan dari meja, kasir tinggal konfirmasi.'],
                ['pain' => 'Pesanan salah catat atau terlewat', 'fix' => 'Semua pesanan tercatat lengkap dengan catatan pelanggan.'],
                ['pain' => 'Stok bahan bocor tanpa ketahuan', 'fix' => 'Bahan berkurang sesuai resep, selisih mudah dilacak.'],
                ['pain' => 'Ramai, tapi untung bersih tidak jelas', 'fix' => 'Laba bersih dihitung setelah bahan dan biaya operasional.'],
            ],
            'features_title' => 'Dari pesanan masuk sampai laporan laba.',
            'features_subtitle' => 'Semua bagian kafe saling terhubung. Pesanan memotong stok, stok menentukan menu yang bisa dipesan, dan laporan langsung terisi.',
            'features' => [
                ['icon' => 'qr', 'title' => 'Pesan lewat QR', 'text' => 'Pelanggan scan QR di meja, pilih menu, bayar QRIS, transfer, atau tunai. Tanpa unduh aplikasi, tanpa daftar.'],
                ['icon' => 'struk', 'title' => 'Kasir cepat', 'text' => 'Pesanan langsung di layar kasir. Struk bisa dicetak, lengkap dengan QR untuk cek status pesanan.'],
                ['icon' => 'lonceng', 'title' => 'Papan pesanan', 'text' => 'Masuk, diproses, selesai. Geser tombol hijau saat pesanan siap, HP pelanggan langsung berbunyi.'],
                ['icon' => 'kotak', 'title' => 'Stok bahan otomatis', 'text' => 'Setiap penjualan memotong bahan sesuai resep. Menu berlabel "Habis" sendiri saat bahannya kurang.'],
                ['icon' => 'grafik', 'title' => 'HPP & laba bersih', 'text' => 'Hitung harga pokok dari resep plus gaji, listrik, sewa, dan bensin. Atur harga jual dari margin yang Anda mau.'],
                ['icon' => 'tiket', 'title' => 'Promo & voucher', 'text' => 'Kartu promo di menu, popup promo, kode voucher, dan poin member tanpa kartu fisik.'],
                ['icon' => 'tv', 'title' => 'Layar TV menu', 'text' => 'Tampilkan menu andalan dan promo di TV kafe. Ikut berubah saat menu habis.'],
                ['icon' => 'chat', 'title' => 'Notifikasi WhatsApp', 'text' => 'Setiap pesanan baru dari pelanggan juga dikirim ke WhatsApp pemilik atau kasir.'],
            ],
            'flow_title' => 'Satu pesanan, empat langkah jelas.',
            'flow' => [
                ['icon' => 'masuk', 'title' => 'Masuk', 'text' => 'Pelanggan memesan dari HP. Kasir mendengar bunyi dan melihatnya di papan pesanan.'],
                ['icon' => 'dompet', 'title' => 'Dibayar', 'text' => 'Kasir mencocokkan bukti QRIS/transfer atau menerima tunai, lalu menekan Terima.'],
                ['icon' => 'kopi', 'title' => 'Dibuat', 'text' => 'Dapur melihat pesanan dengan jam tunggu dan perkiraan waktu siap.'],
                ['icon' => 'centang', 'title' => 'Siap', 'text' => 'Geser tombol hijau. HP pelanggan berbunyi dan bergetar, stok sudah terpotong.'],
            ],
            'reasons_title' => 'Dibuat untuk kafe kecil yang ingin tumbuh.',
            'reasons_subtitle' => 'Tanpa mesin kasir mahal dan tanpa pelatihan panjang. Kalau Anda bisa memakai WhatsApp, Anda bisa memakai kafehub.',
            'reasons' => [
                ['icon' => 'kilat', 'title' => 'Ringan di HP murah', 'text' => 'Halaman kecil dan cepat, tetap lancar di HP Android biasa dan sinyal pas-pasan.'],
                ['icon' => 'dompet', 'title' => 'Uang langsung ke Anda', 'text' => 'Pembayaran masuk ke QRIS atau rekening kafe Anda sendiri, bukan ditahan pihak ketiga.'],
                ['icon' => 'orang', 'title' => 'Pelanggan tanpa repot', 'text' => 'Tidak ada aplikasi untuk diunduh dan tidak perlu membuat akun. Riwayat pesanan tersimpan di HP mereka.'],
                ['icon' => 'bola', 'title' => 'Bahasa Indonesia', 'text' => 'Dibuat untuk kafe di Indonesia: rupiah, QRIS, transfer bank, dan istilah yang dipakai sehari-hari.'],
            ],
            'start_title' => 'Siap dipakai dalam hitungan hari.',
            'start' => [
                ['title' => 'Hubungi kami', 'text' => 'Ceritakan kafe Anda. Kami siapkan alamat sendiri, misalnya namakafe.kafehub.id.'],
                ['title' => 'Isi menu & resep', 'text' => 'Masukkan menu, foto, harga, bahan, dan QRIS kafe dari panel admin. Kami bantu bila perlu.'],
                ['title' => 'Tempel QR di meja', 'text' => 'Cetak QR, taruh di meja, dan pesanan pertama bisa langsung masuk.'],
            ],
            'showcase_title' => 'Lihat langsung di kafe sungguhan.',
            'showcase' => [
                ['name' => 'Antara Cafe', 'url' => 'https://antara.kafehub.id', 'text' => 'Antara Cafe memakai kafehub untuk pesan lewat QR, kasir, papan pesanan, stok bahan, dan laporan harian.'],
            ],
            'faqs' => [
                ['q' => 'Apakah pelanggan harus install aplikasi?', 'a' => 'Tidak. Pelanggan cukup scan QR dan memesan dari browser HP. Mereka juga tidak perlu mendaftar.'],
                ['q' => 'Perangkat apa yang dibutuhkan kafe?', 'a' => 'HP, tablet, atau laptop dengan browser untuk kasir. Printer struk dan TV bersifat pilihan.'],
                ['q' => 'Bagaimana dengan pembayaran QRIS?', 'a' => 'Anda memasang gambar QRIS milik kafe sendiri. Pelanggan mengirim bukti bayar, kasir mencocokkan, lalu pesanan diteruskan ke dapur.'],
                ['q' => 'Apakah menu dan tampilan bisa diubah sendiri?', 'a' => 'Bisa. Foto menu, harga, resep, promo, gambar halaman depan, sampai warna tema diatur dari panel admin.'],
                ['q' => 'Kafe saya akan punya alamat sendiri?', 'a' => 'Ya, misalnya namakafe.kafehub.id, seperti antara.kafehub.id.'],
            ],
            'cta_title' => 'Siap membuat kafe Anda lebih rapi?',
            'cta_subtitle' => 'Coba dulu sebagai pelanggan di Antara Cafe, lalu bayangkan itu kafe Anda.',
        ];
    }

    public static function get(): array
    {
        $saved = json_decode((string) Setting::get(self::KEY), true);

        return array_replace(self::defaults(), is_array($saved) ? array_filter($saved, fn ($v) => $v !== null && $v !== '') : []);
    }

    public static function save(array $content): void
    {
        Setting::put(self::KEY, json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Link WhatsApp untuk tombol Gabung; null bila nomor belum diisi. */
    public static function joinUrl(array $content): ?string
    {
        $number = preg_replace('/\D/', '', (string) ($content['whatsapp'] ?? ''));
        if ($number === '') {
            return null;
        }
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode('Halo kafehub, saya ingin memakai sistem kafehub untuk kafe saya.');
    }
}
