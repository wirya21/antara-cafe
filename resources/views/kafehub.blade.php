<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>kafehub · Sistem pesan, kasir & stok untuk kafe</title>
    <meta name="description" content="Menu digital, pesan lewat QR, kasir, papan pesanan, stok bahan otomatis, HPP dan laba bersih. Satu sistem untuk kafe Anda, langsung dari HP.">
    <meta property="og:title" content="kafehub · Kafe Anda, jalan lebih rapi">
    <meta property="og:description" content="Pesan lewat QR, kasir, stok bahan, dan laporan laba dalam satu sistem. Sudah dipakai Antara Cafe.">
    <meta name="theme-color" content="#2b1b0e">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-espresso-50 font-sans text-espresso-900 antialiased">
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
    <header class="sticky top-0 z-30 border-b border-white/10 bg-espresso-900/95 text-white backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="#" class="flex items-center gap-2 font-display text-xl font-semibold tracking-tight">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-400 text-base text-espresso-900">☕</span>
                kafehub
            </a>
            <nav class="hidden items-center gap-6 text-sm text-espresso-100 md:flex">
                <a href="#fitur" class="hover:text-white">Fitur</a>
                <a href="#cara" class="hover:text-white">Cara mulai</a>
                <a href="#dipakai" class="hover:text-white">Sudah dipakai</a>
                <a href="#tanya" class="hover:text-white">Tanya jawab</a>
            </nav>
            @if ($joinUrl)
                <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-espresso-900 hover:bg-amber-300">Gabung</a>
            @else
                <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-espresso-900 hover:bg-amber-300">Coba demo</a>
            @endif
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-espresso-900 text-white">
        <div class="pointer-events-none absolute -top-40 -right-20 h-96 w-96 rounded-full bg-amber-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-20 h-96 w-96 rounded-full bg-espresso-600/40 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:py-24">
            <div>
                <p class="inline-flex items-center gap-2 rounded-md bg-white/10 px-3 py-1 text-xs font-semibold text-amber-300">
                    <span class="h-2 w-2 rounded-full bg-green-400"></span> Sudah berjalan di Antara Cafe
                </p>
                <h1 class="mt-5 font-display text-4xl leading-[1.08] font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                    Kafe Anda,<br>jalan lebih rapi.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-espresso-100 sm:text-lg">
                    Menu digital, pesan lewat QR, kasir, papan pesanan, stok bahan, sampai laba bersih.
                    Semua dalam satu sistem yang dibuka dari HP. Tanpa aplikasi, tanpa ribet.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($joinUrl)
                        <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-lg bg-amber-400 px-5 py-3 font-semibold text-espresso-900 shadow-lg shadow-amber-500/20 hover:bg-amber-300">Gabungkan kafe saya</a>
                    @endif
                    <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-lg border border-white/25 px-5 py-3 font-semibold text-white hover:bg-white/10">Coba sebagai pelanggan ↗</a>
                </div>
                <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 border-t border-white/10 pt-6 text-sm">
                    <div><dt class="text-espresso-300">Pelanggan</dt><dd class="mt-1 font-semibold">Tanpa install</dd></div>
                    <div><dt class="text-espresso-300">Pembayaran</dt><dd class="mt-1 font-semibold">QRIS · transfer · tunai</dd></div>
                    <div><dt class="text-espresso-300">Stok</dt><dd class="mt-1 font-semibold">Terpotong otomatis</dd></div>
                </dl>
            </div>

            {{-- Ilustrasi HP: menu dan status pesanan --}}
            <div class="relative mx-auto w-full max-w-xs" aria-hidden="true">
                <div class="rounded-[2.5rem] border-[10px] border-espresso-800 bg-espresso-50 p-3 shadow-2xl shadow-black/40">
                    <div class="rounded-xl bg-espresso-900 p-4 text-white">
                        <p class="text-[10px] text-espresso-300">Antara Cafe</p>
                        <p class="mt-1 font-display text-lg leading-tight font-semibold">Mau ngopi apa hari ini?</p>
                    </div>
                    <div class="mt-3 rounded-lg border border-dashed border-amber-400 bg-amber-50 px-3 py-2 text-[11px] text-espresso-800">
                        <span class="rounded bg-amber-400 px-1.5 py-0.5 font-bold text-espresso-900">PROMO</span> Diskon 10% pakai kode <b>PAGI</b>
                    </div>
                    <div class="mt-3 space-y-2 text-espresso-900">
                        @foreach ([['Kopi susu aren', '22.000', 'Sisa 4'], ['Caffe latte', '28.000', null], ['Croissant', '18.000', 'Habis']] as [$name, $price, $tag])
                            <div class="flex items-center gap-3 rounded-lg bg-white p-2 shadow-sm">
                                <div class="grid h-10 w-10 place-items-center rounded-md bg-espresso-100 text-lg">{{ $loop->last ? '🥐' : '☕' }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-semibold">{{ $name }}</p>
                                    <p class="text-[11px] text-espresso-600">Rp{{ $price }}</p>
                                </div>
                                @if ($tag)
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $tag === 'Habis' ? 'bg-espresso-200 text-espresso-700' : 'bg-amber-100 text-amber-800' }}">{{ $tag }}</span>
                                @else
                                    <span class="grid h-6 w-6 place-items-center rounded-md bg-espresso-700 text-xs text-white">+</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 rounded-lg bg-espresso-700 px-3 py-2.5 text-center text-xs font-semibold text-white">Pesan 2 item · Rp50.000</div>
                </div>
                <div class="absolute -right-4 bottom-16 w-48 rounded-xl bg-white p-3 text-espresso-900 shadow-xl ring-1 ring-espresso-100 sm:-right-12">
                    <p class="flex items-center gap-2 text-xs font-semibold"><span class="h-2 w-2 rounded-full bg-green-500"></span> Pesanan A-7KQ2M siap!</p>
                    <p class="mt-1 text-[11px] text-espresso-600">Silakan ambil di kasir.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Masalah yang diselesaikan --}}
    <section class="border-b border-espresso-100 bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-10 text-sm sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
            @foreach (['Antrean panjang di kasir saat ramai' => 'Pelanggan pesan dari meja, kasir tinggal konfirmasi.', 'Pesanan salah catat atau terlewat' => 'Semua pesanan tercatat lengkap dengan catatan pelanggan.', 'Stok bahan bocor tanpa ketahuan' => 'Bahan berkurang sesuai resep, selisih mudah dilacak.', 'Ramai, tapi untung bersih tidak jelas' => 'Laba bersih dihitung setelah bahan dan biaya operasional.'] as $pain => $fix)
                <div>
                    <p class="text-espresso-500 line-through decoration-espresso-300">{{ $pain }}</p>
                    <p class="mt-1 font-semibold">✓ {{ $fix }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold text-espresso-600">Fitur</p>
            <h2 class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl">Dari pesanan masuk sampai laporan laba.</h2>
            <p class="mt-3 text-espresso-700">Semua bagian kafe saling terhubung. Pesanan memotong stok, stok menentukan menu yang bisa dipesan, dan laporan langsung terisi.</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($features as [$icon, $title, $text])
                <div class="rounded-xl border border-espresso-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-espresso-100 text-xl">{{ $icon }}</span>
                    <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-espresso-700">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Alur pesanan --}}
    <section class="bg-espresso-900 text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="font-display text-3xl font-semibold tracking-tight">Satu pesanan, empat langkah jelas.</h2>
            <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['Masuk', 'Pelanggan memesan dari HP. Kasir mendengar bunyi dan melihatnya di papan pesanan.', 'bg-amber-400 text-espresso-900'], ['Dibayar', 'Kasir mencocokkan bukti QRIS/transfer atau menerima tunai, lalu menekan Terima.', 'bg-blue-500 text-white'], ['Dibuat', 'Dapur melihat pesanan dengan jam tunggu dan perkiraan waktu siap.', 'bg-espresso-500 text-white'], ['Siap', 'Geser tombol hijau. HP pelanggan berbunyi dan bergetar, stok sudah terpotong.', 'bg-green-500 text-white']] as [$step, $text, $badge])
                    <li class="rounded-xl bg-white/5 p-5 ring-1 ring-white/10">
                        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-sm font-bold {{ $badge }}">{{ $loop->iteration }}</span>
                        <p class="mt-3 font-semibold">{{ $step }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-espresso-200">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Kenapa kafehub --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p class="text-sm font-semibold text-espresso-600">Kenapa kafehub</p>
                <h2 class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl">Dibuat untuk kafe kecil yang ingin tumbuh.</h2>
                <p class="mt-3 text-espresso-700">Tanpa mesin kasir mahal dan tanpa pelatihan panjang. Kalau Anda bisa memakai WhatsApp, Anda bisa memakai kafehub.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($reasons as [$title, $text])
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-espresso-100">
                        <p class="font-semibold">{{ $title }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-espresso-700">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cara mulai --}}
    <section id="cara" class="scroll-mt-20 border-y border-espresso-100 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="text-sm font-semibold text-espresso-600">Cara mulai</p>
            <h2 class="mt-2 font-display text-3xl font-semibold tracking-tight">Siap dipakai dalam hitungan hari.</h2>
            <ol class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach ([['Hubungi kami', 'Ceritakan kafe Anda. Kami siapkan alamat sendiri, misalnya namakafe.kafehub.id.'], ['Isi menu & resep', 'Masukkan menu, foto, harga, bahan, dan QRIS kafe dari panel admin. Kami bantu bila perlu.'], ['Tempel QR di meja', 'Cetak QR, taruh di meja, dan pesanan pertama bisa langsung masuk.']] as [$title, $text])
                    <li class="flex gap-4">
                        <span class="font-display text-4xl leading-none font-semibold text-espresso-300">{{ $loop->iteration }}</span>
                        <div>
                            <p class="font-semibold">{{ $title }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-espresso-700">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Sudah dipakai --}}
    <section id="dipakai" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-24">
        <p class="text-sm font-semibold text-espresso-600">Sudah dipakai</p>
        <h2 class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl">Lihat langsung di kafe sungguhan.</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($showcase as $cafe)
                <div class="overflow-hidden rounded-xl bg-espresso-900 text-white shadow-lg">
                    <div class="p-6">
                        <p class="flex items-center gap-2 text-xs font-semibold text-green-300"><span class="h-2 w-2 rounded-full bg-green-400"></span> Aktif</p>
                        <p class="mt-3 font-display text-2xl font-semibold">{{ $cafe['name'] }}</p>
                        <p class="mt-1 font-mono text-sm text-espresso-200">{{ str_replace('https://', '', $cafe['url']) }}</p>
                        <p class="mt-4 text-sm leading-relaxed text-espresso-100">{{ $cafe['name'] }} memakai kafehub untuk pesan lewat QR, kasir, papan pesanan, stok bahan, dan laporan harian.</p>
                    </div>
                    <div class="grid grid-cols-2 border-t border-white/10 text-sm font-semibold">
                        <a href="{{ $cafe['url'] }}/menu" target="_blank" rel="noopener" class="px-6 py-3 hover:bg-white/5">Buka menu ↗</a>
                        <a href="{{ $cafe['url'] }}/layar" target="_blank" rel="noopener" class="border-l border-white/10 px-6 py-3 hover:bg-white/5">Layar TV ↗</a>
                    </div>
                </div>
            @endforeach
            <div class="flex flex-col justify-center rounded-xl border-2 border-dashed border-espresso-200 p-6">
                <p class="font-display text-2xl font-semibold">Kafe Anda berikutnya?</p>
                <p class="mt-2 text-sm text-espresso-700">Bergabung dan tampil di sini bersama kafe lain yang sudah memakai kafehub.</p>
                @if ($joinUrl)
                    <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="btn-primary mt-4 self-start">Gabungkan kafe saya</a>
                @endif
            </div>
        </div>
    </section>

    {{-- Tanya jawab --}}
    <section id="tanya" class="scroll-mt-20 border-t border-espresso-100 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <h2 class="font-display text-3xl font-semibold tracking-tight">Tanya jawab</h2>
            <div class="mt-6 divide-y divide-espresso-100">
                @foreach ($faqs as [$q, $a])
                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">
                            {{ $q }}
                            <span class="text-espresso-400 transition group-open:rotate-45">＋</span>
                        </summary>
                        <p class="mt-2 text-sm leading-relaxed text-espresso-700">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Ajakan akhir --}}
    <section class="bg-amber-400 text-espresso-900">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 py-14 sm:px-6 md:flex-row md:items-center">
            <div>
                <h2 class="font-display text-3xl font-semibold tracking-tight">Siap membuat kafe Anda lebih rapi?</h2>
                <p class="mt-2 text-espresso-800">Coba dulu sebagai pelanggan di Antara Cafe, lalu bayangkan itu kafe Anda.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($joinUrl)
                    <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-lg bg-espresso-900 px-5 py-3 font-semibold text-white hover:bg-espresso-800">Hubungi via WhatsApp</a>
                @endif
                <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-lg border border-espresso-900/30 px-5 py-3 font-semibold hover:bg-white/30">Coba demo ↗</a>
            </div>
        </div>
    </section>

    <footer class="bg-espresso-900 text-sm text-espresso-300">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-6 sm:px-6">
            <p>© {{ now()->year }} kafehub.id</p>
            <p>Dipakai oleh <a href="{{ $demo }}" class="font-semibold text-white hover:underline">Antara Cafe</a></p>
        </div>
    </footer>
</body>
</html>
