<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>kafehub · Sistem pesan, kasir & stok untuk kafe</title>
    <meta name="description" content="Menu digital, pesan lewat QR, kasir, papan pesanan, stok bahan otomatis, HPP dan laba bersih. Satu sistem untuk kafe Anda, langsung dari HP.">
    <meta property="og:title" content="kafehub · Kafe Anda, jalan lebih rapi">
    <meta property="og:description" content="Pesan lewat QR, kasir, stok bahan, dan laporan laba dalam satu sistem. Sudah dipakai Antara Cafe.">
    <meta name="theme-color" content="#ffffff">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    @php
        $demo = $showcase[0]['url'] ?? url('/');
        $features = [
            ['📱', 'Pesan lewat QR', 'Pelanggan scan QR di meja, pilih menu, bayar QRIS, transfer, atau tunai. Tanpa unduh aplikasi, tanpa daftar.'],
            ['🧾', 'Kasir cepat', 'Pesanan langsung di layar kasir. Struk bisa dicetak, lengkap dengan QR untuk cek status pesanan.'],
            ['🔔', 'Papan pesanan', 'Masuk, diproses, selesai. Geser tombol hijau saat pesanan siap, HP pelanggan langsung berbunyi.'],
            ['🥛', 'Stok bahan otomatis', 'Setiap penjualan memotong bahan sesuai resep. Menu berlabel "Habis" sendiri saat bahannya kurang.'],
            ['📊', 'HPP & laba bersih', 'Hitung harga pokok dari resep plus gaji, listrik, sewa, dan bensin. Atur harga jual dari margin yang Anda mau.'],
            ['🎟️', 'Promo & voucher', 'Kartu promo di menu, popup promo, kode voucher, dan poin member tanpa kartu fisik.'],
            ['📺', 'Layar TV menu', 'Tampilkan menu andalan dan promo di TV kafe. Ikut berubah saat menu habis.'],
            ['💬', 'Notifikasi WhatsApp', 'Setiap pesanan baru dari pelanggan juga dikirim ke WhatsApp pemilik atau kasir.'],
        ];
        $reasons = [
            ['Ringan di HP murah', 'Halaman kecil dan cepat, tetap lancar di HP Android biasa dan sinyal pas-pasan.'],
            ['Uang langsung ke Anda', 'Pembayaran masuk ke QRIS atau rekening kafe Anda sendiri, bukan ditahan pihak ketiga.'],
            ['Pelanggan tanpa repot', 'Tidak ada aplikasi untuk diunduh dan tidak perlu membuat akun. Riwayat pesanan tersimpan di HP mereka.'],
            ['Bahasa Indonesia', 'Dibuat untuk kafe di Indonesia: rupiah, QRIS, transfer bank, dan istilah yang dipakai sehari-hari.'],
        ];
        $faqs = [
            ['Apakah pelanggan harus install aplikasi?', 'Tidak. Pelanggan cukup scan QR dan memesan dari browser HP. Mereka juga tidak perlu mendaftar.'],
            ['Perangkat apa yang dibutuhkan kafe?', 'HP, tablet, atau laptop dengan browser untuk kasir. Printer struk dan TV bersifat pilihan.'],
            ['Bagaimana dengan pembayaran QRIS?', 'Anda memasang gambar QRIS milik kafe sendiri. Pelanggan mengirim bukti bayar, kasir mencocokkan, lalu pesanan diteruskan ke dapur.'],
            ['Apakah menu dan tampilan bisa diubah sendiri?', 'Bisa. Foto menu, harga, resep, promo, gambar halaman depan, sampai warna tema diatur dari panel admin.'],
            ['Kafe saya akan punya alamat sendiri?', 'Ya, misalnya namakafe.kafehub.id, seperti antara.kafehub.id.'],
        ];
    @endphp

    {{-- Navigasi --}}
    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="#" class="flex items-center gap-2 text-xl font-extrabold tracking-tight">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500 text-base shadow-md shadow-brand-500/30">☕</span>
                <span>kafe<span class="text-brand-500">hub</span></span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-medium text-gray-600 md:flex">
                <a href="#fitur" class="hover:text-gray-900">Fitur</a>
                <a href="#cara" class="hover:text-gray-900">Cara mulai</a>
                <a href="#dipakai" class="hover:text-gray-900">Sudah dipakai</a>
                <a href="#tanya" class="hover:text-gray-900">Tanya jawab</a>
            </nav>
            @if ($joinUrl)
                <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-full bg-brand-500 px-5 py-2 text-sm font-bold text-white shadow-md shadow-brand-500/30 hover:bg-brand-600">Gabung</a>
            @else
                <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-full bg-brand-500 px-5 py-2 text-sm font-bold text-white shadow-md shadow-brand-500/30 hover:bg-brand-600">Coba demo</a>
            @endif
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-white">
        <div class="pointer-events-none absolute -top-32 -right-24 h-[28rem] w-[28rem] rounded-full bg-brand-100 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:py-24">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 ring-1 ring-brand-100">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span> Sudah berjalan di Antara Cafe
                </p>
                <h1 class="mt-5 text-4xl leading-[1.08] font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                    Kafe Anda,<br><span class="text-brand-500">jalan lebih rapi.</span>
                </h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-gray-600 sm:text-lg">
                    Menu digital, pesan lewat QR, kasir, papan pesanan, stok bahan, sampai laba bersih.
                    Semua dalam satu sistem yang dibuka dari HP. Tanpa aplikasi, tanpa ribet.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($joinUrl)
                        <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-2xl bg-brand-500 px-6 py-3.5 font-bold text-white shadow-lg shadow-brand-500/30 hover:bg-brand-600">Gabungkan kafe saya</a>
                    @endif
                    <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-2xl border border-gray-200 bg-white px-6 py-3.5 font-bold text-gray-800 hover:bg-gray-50">Coba sebagai pelanggan ↗</a>
                </div>
                <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 border-t border-gray-100 pt-6 text-sm">
                    <div><dt class="text-gray-500">Pelanggan</dt><dd class="mt-1 font-bold">Tanpa install</dd></div>
                    <div><dt class="text-gray-500">Pembayaran</dt><dd class="mt-1 font-bold">QRIS · transfer · tunai</dd></div>
                    <div><dt class="text-gray-500">Stok</dt><dd class="mt-1 font-bold">Terpotong otomatis</dd></div>
                </dl>
            </div>

            {{-- Ilustrasi HP: menu dan status pesanan --}}
            <div class="relative mx-auto w-full max-w-xs" aria-hidden="true">
                <div class="rounded-[2.5rem] border-[10px] border-gray-900 bg-gray-50 p-3 shadow-2xl shadow-gray-900/20">
                    <p class="px-1 text-sm font-extrabold">Antara<span class="text-brand-500">Cafe</span></p>
                    <div class="mt-2 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 p-4 text-white">
                        <p class="text-[10px] font-bold tracking-wider uppercase opacity-80">Promo hari ini</p>
                        <p class="mt-1 text-lg leading-tight font-extrabold">Diskon 10% pakai kode PAGI</p>
                        <span class="mt-2 inline-block rounded-full bg-white px-3 py-1 text-[10px] font-bold text-brand-600">Pesan sekarang</span>
                    </div>
                    <div class="mt-3 space-y-2">
                        @foreach ([['Kopi susu aren', '22.000', 'Sisa 4'], ['Caffe latte', '28.000', null], ['Croissant', '18.000', 'Habis']] as [$name, $price, $tag])
                            <div class="flex items-center gap-3 rounded-xl bg-white p-2 shadow-sm ring-1 ring-gray-100">
                                <div class="grid h-10 w-10 place-items-center rounded-lg bg-brand-50 text-lg">{{ $loop->last ? '🥐' : '☕' }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold">{{ $name }}</p>
                                    <p class="text-[11px] font-semibold text-gray-500">Rp{{ $price }}</p>
                                </div>
                                @if ($tag === 'Habis')
                                    <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500">Habis</span>
                                @else
                                    <span class="grid h-6 w-6 place-items-center rounded-full bg-brand-500 text-xs font-bold text-white">+</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 rounded-xl bg-brand-500 px-3 py-2.5 text-center text-xs font-bold text-white">Lihat keranjang · Rp50.000</div>
                </div>
                <div class="absolute -right-4 bottom-16 w-48 rounded-2xl bg-white p-3 shadow-xl ring-1 ring-gray-100 sm:-right-12">
                    <p class="flex items-center gap-2 text-xs font-bold"><span class="h-2 w-2 rounded-full bg-green-500"></span> Pesanan A-7KQ2M siap!</p>
                    <p class="mt-1 text-[11px] text-gray-500">Silakan ambil di kasir.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Masalah yang diselesaikan --}}
    <section class="border-b border-gray-100 bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-10 text-sm sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
            @foreach (['Antrean panjang di kasir saat ramai' => 'Pelanggan pesan dari meja, kasir tinggal konfirmasi.', 'Pesanan salah catat atau terlewat' => 'Semua pesanan tercatat lengkap dengan catatan pelanggan.', 'Stok bahan bocor tanpa ketahuan' => 'Bahan berkurang sesuai resep, selisih mudah dilacak.', 'Ramai, tapi untung bersih tidak jelas' => 'Laba bersih dihitung setelah bahan dan biaya operasional.'] as $pain => $fix)
                <div>
                    <p class="text-gray-500 line-through decoration-gray-300">{{ $pain }}</p>
                    <p class="mt-1 font-semibold">✓ {{ $fix }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-bold text-brand-600">Fitur</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Dari pesanan masuk sampai laporan laba.</h2>
            <p class="mt-3 text-gray-600">Semua bagian kafe saling terhubung. Pesanan memotong stok, stok menentukan menu yang bisa dipesan, dan laporan langsung terisi.</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($features as [$icon, $title, $text])
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-brand-50 text-xl">{{ $icon }}</span>
                    <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Alur pesanan --}}
    <section class="bg-gray-900 text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="text-3xl font-extrabold tracking-tight">Satu pesanan, empat langkah jelas.</h2>
            <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['Masuk', 'Pelanggan memesan dari HP. Kasir mendengar bunyi dan melihatnya di papan pesanan.', 'bg-brand-500 text-white'], ['Dibayar', 'Kasir mencocokkan bukti QRIS/transfer atau menerima tunai, lalu menekan Terima.', 'bg-blue-500 text-white'], ['Dibuat', 'Dapur melihat pesanan dengan jam tunggu dan perkiraan waktu siap.', 'bg-amber-500 text-white'], ['Siap', 'Geser tombol hijau. HP pelanggan berbunyi dan bergetar, stok sudah terpotong.', 'bg-green-500 text-white']] as [$step, $text, $badge])
                    <li class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-sm font-bold {{ $badge }}">{{ $loop->iteration }}</span>
                        <p class="mt-3 font-semibold">{{ $step }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-gray-300">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Kenapa kafehub --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p class="text-sm font-bold text-brand-600">Kenapa kafehub</p>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Dibuat untuk kafe kecil yang ingin tumbuh.</h2>
                <p class="mt-3 text-gray-600">Tanpa mesin kasir mahal dan tanpa pelatihan panjang. Kalau Anda bisa memakai WhatsApp, Anda bisa memakai kafehub.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($reasons as [$title, $text])
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <p class="font-semibold">{{ $title }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cara mulai --}}
    <section id="cara" class="scroll-mt-20 border-y border-gray-100 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="text-sm font-bold text-brand-600">Cara mulai</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Siap dipakai dalam hitungan hari.</h2>
            <ol class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach ([['Hubungi kami', 'Ceritakan kafe Anda. Kami siapkan alamat sendiri, misalnya namakafe.kafehub.id.'], ['Isi menu & resep', 'Masukkan menu, foto, harga, bahan, dan QRIS kafe dari panel admin. Kami bantu bila perlu.'], ['Tempel QR di meja', 'Cetak QR, taruh di meja, dan pesanan pertama bisa langsung masuk.']] as [$title, $text])
                    <li class="flex gap-4">
                        <span class="text-4xl leading-none font-extrabold text-brand-500">{{ $loop->iteration }}</span>
                        <div>
                            <p class="font-semibold">{{ $title }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Sudah dipakai --}}
    <section id="dipakai" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-24">
        <p class="text-sm font-bold text-brand-600">Sudah dipakai</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Lihat langsung di kafe sungguhan.</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($showcase as $cafe)
                <div class="overflow-hidden rounded-2xl bg-gray-900 text-white shadow-lg">
                    <div class="p-6">
                        <p class="flex items-center gap-2 text-xs font-semibold text-green-300"><span class="h-2 w-2 rounded-full bg-green-400"></span> Aktif</p>
                        <p class="mt-3 text-2xl font-extrabold">{{ $cafe['name'] }}</p>
                        <p class="mt-1 font-mono text-sm text-gray-300">{{ str_replace('https://', '', $cafe['url']) }}</p>
                        <p class="mt-4 text-sm leading-relaxed text-gray-200">{{ $cafe['name'] }} memakai kafehub untuk pesan lewat QR, kasir, papan pesanan, stok bahan, dan laporan harian.</p>
                    </div>
                    <div class="grid grid-cols-2 border-t border-white/10 text-sm font-semibold">
                        <a href="{{ $cafe['url'] }}/menu" target="_blank" rel="noopener" class="px-6 py-3 hover:bg-white/5">Buka menu ↗</a>
                        <a href="{{ $cafe['url'] }}/layar" target="_blank" rel="noopener" class="border-l border-white/10 px-6 py-3 hover:bg-white/5">Layar TV ↗</a>
                    </div>
                </div>
            @endforeach
            <div class="flex flex-col justify-center rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50/40 p-6">
                <p class="text-2xl font-extrabold">Kafe Anda berikutnya?</p>
                <p class="mt-2 text-sm text-gray-600">Bergabung dan tampil di sini bersama kafe lain yang sudah memakai kafehub.</p>
                @if ($joinUrl)
                    <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="mt-4 self-start rounded-full bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-500/30 hover:bg-brand-600">Gabungkan kafe saya</a>
                @endif
            </div>
        </div>
    </section>

    {{-- Tanya jawab --}}
    <section id="tanya" class="scroll-mt-20 border-t border-gray-100 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <h2 class="text-3xl font-extrabold tracking-tight">Tanya jawab</h2>
            <div class="mt-6 divide-y divide-gray-100">
                @foreach ($faqs as [$q, $a])
                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">
                            {{ $q }}
                            <span class="text-gray-400 transition group-open:rotate-45">＋</span>
                        </summary>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Ajakan akhir --}}
    <section class="bg-gradient-to-br from-brand-500 to-brand-700 text-white">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 py-14 sm:px-6 md:flex-row md:items-center">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">Siap membuat kafe Anda lebih rapi?</h2>
                <p class="mt-2 text-white/85">Coba dulu sebagai pelanggan di Antara Cafe, lalu bayangkan itu kafe Anda.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($joinUrl)
                    <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-2xl bg-white px-6 py-3.5 font-bold text-brand-600 hover:bg-brand-50">Hubungi via WhatsApp</a>
                @endif
                <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-lg border border-white/40 px-6 py-3.5 font-bold hover:bg-white/10">Coba demo ↗</a>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-sm text-gray-400">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-6 sm:px-6">
            <p>© {{ now()->year }} kafehub.id</p>
            <p>Dipakai oleh <a href="{{ $demo }}" class="font-semibold text-white hover:underline">Antara Cafe</a></p>
        </div>
    </footer>
</body>
</html>
