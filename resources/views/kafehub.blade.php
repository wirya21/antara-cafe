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
    <meta property="og:image" content="{{ asset('images/kafehub/logo.png') }}">
    <x-kafehub-icons />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    @php $demo = $showcase[0]['url'] ?? url('/'); @endphp

    {{-- Navigasi --}}
    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="#" class="flex items-center">
                <img src="{{ asset('images/kafehub/logo.png') }}" alt="KafeHub.id" class="h-11 w-auto sm:h-12" width="304" height="160">
            </a>
            <nav class="hidden items-center gap-6 text-sm font-medium text-gray-600 md:flex">
                <a href="#fitur" class="hover:text-gray-900">Fitur</a>
                <a href="#cara" class="hover:text-gray-900">Cara mulai</a>
                <a href="#dipakai" class="hover:text-gray-900">Sudah dipakai</a>
                @if ($c['faqs'])<a href="#tanya" class="hover:text-gray-900">Tanya jawab</a>@endif
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
                    <span class="h-2 w-2 rounded-full bg-green-500"></span> {{ $c['hero_badge'] }}
                </p>
                <h1 class="mt-5 text-4xl leading-[1.08] font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                    {{ $c['hero_title'] }}<br><span class="text-brand-500">{{ $c['hero_highlight'] }}</span>
                </h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-gray-600 sm:text-lg">
                    {{ $c['hero_subtitle'] }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($joinUrl)
                        <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="rounded-2xl bg-brand-500 px-6 py-3.5 font-bold text-white shadow-lg shadow-brand-500/30 hover:bg-brand-600">Gabungkan kafe saya</a>
                    @endif
                    <a href="{{ $demo }}/menu" target="_blank" rel="noopener" class="rounded-2xl border border-gray-200 bg-white px-6 py-3.5 font-bold text-gray-800 hover:bg-gray-50">Coba sebagai pelanggan ↗</a>
                </div>
                <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 border-t border-gray-100 pt-6 text-sm">
                    @foreach ($c['stats'] as $stat)
                        <div><dt class="text-gray-500">{{ $stat['label'] }}</dt><dd class="mt-1 font-bold">{{ $stat['value'] }}</dd></div>
                    @endforeach
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
            @foreach ($c['pains'] as $item)
                <div>
                    <p class="text-gray-500 line-through decoration-gray-300">{{ $item['pain'] }}</p>
                    <p class="mt-1.5 flex gap-2 font-semibold"><x-line-icon name="centang" class="h-5 w-5 shrink-0 text-brand-500" />{{ $item['fix'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-bold tracking-wider text-brand-600 uppercase">Fitur</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $c['features_title'] }}</h2>
            <p class="mt-3 text-gray-600">{{ $c['features_subtitle'] }}</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($c['features'] as $item)
                <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                    <span class="grid h-12 w-12 place-items-center rounded-xl border border-brand-100 bg-brand-50/60 text-brand-600 transition group-hover:bg-brand-500 group-hover:text-white">
                        <x-line-icon :name="$item['icon']" />
                    </span>
                    <h3 class="mt-5 font-bold">{{ $item['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Alur pesanan (kelas grid: lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4) --}}
    <section class="relative overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute -top-40 right-0 h-96 w-96 rounded-full bg-brand-500/15 blur-3xl"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="text-sm font-bold tracking-wider text-brand-400 uppercase">Alur pesanan</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $c['flow_title'] }}</h2>
            <ol class="relative mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-{{ max(1, min(4, count($c['flow']))) }} lg:gap-6">
                <div class="pointer-events-none absolute top-7 right-[12%] left-[12%] hidden h-px bg-gradient-to-r from-brand-500/0 via-brand-500/60 to-brand-500/0 lg:block" aria-hidden="true"></div>
                @foreach ($c['flow'] as $item)
                    <li class="relative">
                        <div class="flex items-center gap-4">
                            <span class="relative grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gray-900 text-brand-400 ring-1 ring-brand-500/40 shadow-lg shadow-brand-500/10">
                                <x-line-icon :name="$item['icon']" class="h-6 w-6" />
                            </span>
                            <span class="bg-gradient-to-b from-white to-white/10 bg-clip-text text-5xl leading-none font-black tracking-tighter text-transparent tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="mt-5 text-lg font-bold">{{ $item['title'] }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-400">{{ $item['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Kenapa kafehub --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p class="text-sm font-bold tracking-wider text-brand-600 uppercase">Kenapa kafehub</p>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $c['reasons_title'] }}</h2>
                <p class="mt-3 text-gray-600">{{ $c['reasons_subtitle'] }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($c['reasons'] as $item)
                    <div class="flex gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-brand-100 bg-brand-50/60 text-brand-600"><x-line-icon :name="$item['icon']" class="h-5 w-5" /></span>
                        <div>
                            <p class="font-bold">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $item['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cara mulai --}}
    <section id="cara" class="scroll-mt-20 border-y border-gray-100 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="text-sm font-bold tracking-wider text-brand-600 uppercase">Cara mulai</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $c['start_title'] }}</h2>
            <ol class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach ($c['start'] as $item)
                    <li class="flex gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-black text-white shadow-md shadow-brand-500/30 tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <p class="font-bold">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $item['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Sudah dipakai --}}
    <section id="dipakai" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-24">
        <p class="text-sm font-bold tracking-wider text-brand-600 uppercase">Sudah dipakai</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $c['showcase_title'] }}</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($showcase as $cafe)
                <div class="overflow-hidden rounded-2xl bg-gray-900 text-white shadow-lg">
                    <div class="p-6">
                        <p class="flex items-center gap-2 text-xs font-semibold text-green-300"><span class="h-2 w-2 rounded-full bg-green-400"></span> Aktif</p>
                        <p class="mt-3 text-2xl font-extrabold">{{ $cafe['name'] }}</p>
                        <p class="mt-1 font-mono text-sm text-gray-300">{{ str_replace('https://', '', $cafe['url']) }}</p>
                        @if (filled($cafe['text'] ?? null))<p class="mt-4 text-sm leading-relaxed text-gray-200">{{ $cafe['text'] }}</p>@endif
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
    @if ($c['faqs'])
    <section id="tanya" class="scroll-mt-20 border-t border-gray-100 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <h2 class="text-3xl font-extrabold tracking-tight">Tanya jawab</h2>
            <div class="mt-6 divide-y divide-gray-100">
                @foreach ($c['faqs'] as $item)
                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">
                            {{ $item['q'] }}
                            <span class="text-gray-400 transition group-open:rotate-45">＋</span>
                        </summary>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Ajakan akhir --}}
    <section class="bg-gradient-to-br from-brand-500 to-brand-700 text-white">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 py-14 sm:px-6 md:flex-row md:items-center">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $c['cta_title'] }}</h2>
                <p class="mt-2 text-white/85">{{ $c['cta_subtitle'] }}</p>
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
            <p class="flex items-center gap-2"><img src="{{ asset('images/kafehub/mark.png') }}" alt="" class="h-6 w-auto"> © {{ now()->year }} KafeHub.id</p>
            @if ($showcase)<p>Dipakai oleh <a href="{{ $demo }}" class="font-semibold text-white hover:underline">{{ $showcase[0]['name'] }}</a></p>@endif
        </div>
    </footer>
</body>
</html>
