<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>Menu · {{ $cafe?->name }}</title>
    <x-kafehub-icons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
<div x-data="menuApp(@js($menu), @js([
        'tableNumber' => old('table_number', $table),
        'customerName' => old('customer_name', ''),
        'memberPhone' => old('member_phone', ''),
        'voucherCode' => old('voucher_code', ''),
        'paymentMethod' => old('payment_method', ''),
        'openCart' => $errors->any(),
        'autoPromos' => $autoPromos,
        'voucherUrl' => route('menu.voucher'),
    ]))" class="min-h-screen lg:pr-[400px]">

    {{-- Kepala: nama kafe, meja, riwayat, dan pencarian --}}
    <header class="bg-white">
        <div class="mx-auto max-w-5xl px-4 pt-[max(1rem,env(safe-area-inset-top))] pb-4 sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="flex items-center gap-1 text-xs font-medium text-gray-500">
                        <svg class="h-4 w-4 text-brand-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>
                        <span x-text="tableNumber ? 'Makan di meja ' + tableNumber : 'Pesan dari mana saja'"></span>
                    </p>
                    <p class="mt-1 flex items-center gap-2 text-2xl font-extrabold tracking-tight">
                        <x-cafe-logo class="h-9 w-9 rounded-xl ring-1 ring-gray-200"><span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500 text-lg shadow-md shadow-brand-500/30">☕</span></x-cafe-logo>
                        @php [$brandA, $brandB] = $cafe->brandParts(); @endphp
                        <span>{{ $brandA }}<span class="text-brand-500">{{ $brandB }}</span></span>
                    </p>
                </div>
                <a href="{{ route('menu.history') }}" class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full border border-gray-200 text-gray-700 hover:bg-gray-50" aria-label="Pesanan saya">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/></svg>
                    <span x-show="hasHistory" x-cloak class="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-brand-500 ring-2 ring-white"></span>
                </a>
            </div>

            <label class="mt-4 flex items-center gap-3 rounded-2xl bg-gray-100 px-4 py-3.5">
                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input x-ref="search" x-model="search" type="search" placeholder="Cari kopi, minuman, makanan…" class="w-full bg-transparent text-sm outline-none placeholder:text-gray-500">
            </label>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 pb-44 sm:px-6 lg:pb-12">

        @if ($busyMessage)
            <div class="mt-4 flex items-start gap-3 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
                <span class="text-xl leading-none">⏳</span><p class="font-semibold">{{ $busyMessage }}</p>
            </div>
        @endif

        {{-- Sorotan utama: judul & gambar diatur dari Pengaturan admin --}}
        <section x-show="!search" class="relative mt-4 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-500 to-brand-700 text-white shadow-lg shadow-brand-500/25">
            <div class="pointer-events-none absolute -top-16 -left-10 h-48 w-48 rounded-full bg-white/10"></div>
            <div class="relative grid min-h-44 grid-cols-[1fr_auto] items-center gap-2 p-5 sm:p-7">
                <div class="min-w-0">
                    <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold tracking-wider uppercase">{{ $cafe?->name }}</span>
                    <h1 class="mt-3 text-2xl leading-tight font-extrabold sm:text-3xl">{{ $hero['title'] }}</h1>
                    <p class="mt-1.5 line-clamp-3 text-sm text-white/85">{{ $hero['subtitle'] }}</p>
                    <button type="button" @click="document.getElementById('daftar-menu').scrollIntoView({ behavior: 'smooth' })"
                            class="mt-4 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-brand-600 shadow-md transition active:scale-95">Pesan sekarang</button>
                </div>
                @if ($hero['image'])
                    <img src="{{ $hero['image'] }}" alt="" fetchpriority="high" class="-mr-10 h-36 w-36 rounded-full object-cover shadow-2xl ring-4 ring-white/30 sm:-mr-4 sm:h-48 sm:w-48">
                @else
                    <span class="-mr-2 text-7xl drop-shadow-xl sm:text-8xl">☕</span>
                @endif
            </div>
        </section>

        {{-- Kategori --}}
        <section x-show="!search" class="mt-6">
            <div class="no-scrollbar -mx-4 flex gap-4 overflow-x-auto px-4 sm:-mx-6 sm:px-6">
                <template x-for="c in menu" :key="'t' + c.id">
                    <button type="button" @click="scrollToCategory(c.id)" class="flex w-20 shrink-0 flex-col items-center gap-2 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl text-3xl shadow-sm ring-1 ring-black/5 transition active:scale-95" :class="placeholder(c.group).tile" x-text="placeholder(c.group).emoji"></span>
                        <span class="line-clamp-2 text-xs leading-tight font-semibold text-gray-700" x-text="c.name"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- Promo --}}
        @if ($banners->isNotEmpty())
            <section x-show="!search" class="mt-7">
                <h2 class="text-lg font-bold">Promo hari ini</h2>
                <div class="no-scrollbar -mx-4 mt-3 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 sm:-mx-6 sm:scroll-px-6 sm:px-6">
                    @foreach ($banners as $banner)
                        <div class="relative aspect-[2/1] min-h-36 w-[85%] shrink-0 snap-start overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-gray-700 shadow-md sm:w-[400px]">
                            @if ($banner->image_path)
                                <img loading="lazy" decoding="async" src="{{ Storage::disk('public')->url($banner->image_path) }}" alt="{{ $banner->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="pointer-events-none absolute -right-8 -bottom-10 h-40 w-40 rounded-full bg-brand-500/40 blur-2xl"></div>
                                <div class="pointer-events-none absolute right-5 bottom-5 text-5xl drop-shadow-lg">🎉</div>
                            @endif
                            @if ($banner->discount_type)
                                <span class="absolute top-3 left-3 rounded-lg bg-brand-500 px-2.5 py-1 text-xs font-bold text-white shadow">
                                    {{ $banner->discount_type === 'percent' ? 'Diskon '.$banner->discount_value.'%' : 'Hemat Rp'.number_format($banner->discount_value, 0, ',', '.') }}
                                </span>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-4 pr-16 text-white">
                                <p class="text-base font-bold">{{ $banner->title }}</p>
                                @if ($banner->description)<p class="line-clamp-2 text-xs text-white/80">{{ $banner->description }}</p>@endif
                                @if ($banner->voucher_code)<p class="mt-1.5 text-xs">Kode <b class="rounded-md border border-dashed border-white/60 px-1.5 py-0.5 tracking-wider">{{ $banner->voucher_code }}</b></p>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Favorit --}}
        <section x-show="!search && featured.length" class="mt-7">
            <h2 class="text-lg font-bold">Favorit pelanggan</h2>
            <div class="no-scrollbar -mx-4 mt-3 flex snap-x scroll-px-4 gap-4 overflow-x-auto px-4 pb-3 sm:-mx-6 sm:scroll-px-6 sm:px-6">
                <template x-for="p in featured" :key="'f' + p.id">
                    <article class="w-56 shrink-0 snap-start overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                        <button type="button" @click="openDetail(p)" class="relative block aspect-[4/3] w-full overflow-hidden">
                            <template x-if="p.image"><img loading="lazy" decoding="async" :src="p.image" :alt="p.name" class="h-full w-full object-cover"></template>
                            <template x-if="!p.image"><span class="grid h-full w-full place-items-center bg-gradient-to-br text-5xl" :class="placeholder(p.group).bg" x-text="placeholder(p.group).emoji"></span></template>
                            <span x-show="qtyOf(p.id)" x-text="qtyOf(p.id) + ' di keranjang'" class="absolute top-2 left-2 rounded-full bg-brand-500 px-2.5 py-1 text-[11px] font-bold text-white shadow"></span>
                        </button>
                        <div class="flex items-end justify-between gap-2 p-3">
                            <button type="button" @click="openDetail(p)" class="min-w-0 text-left">
                                <p class="truncate text-sm font-bold" x-text="p.name"></p>
                                <p class="mt-0.5 text-sm font-semibold text-gray-600" x-text="rupiah(p.price)"></p>
                            </button>
                            <button type="button" @click="add(p.id)" :disabled="!canAdd(p.id)" aria-label="Tambah"
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-500 text-white shadow-md shadow-brand-500/30 transition active:scale-90 disabled:opacity-30">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                            </button>
                        </div>
                    </article>
                </template>
            </div>
        </section>

        {{-- Navigasi kategori yang menempel saat menggulir --}}
        <nav id="daftar-menu" class="no-scrollbar sticky top-0 z-20 -mx-4 mt-5 flex scroll-mt-0 gap-2 overflow-x-auto border-b border-gray-100 bg-gray-50/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
            <template x-for="c in filteredMenu" :key="'c' + c.id">
                <button type="button" @click="scrollToCategory(c.id)"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                        :class="activeCategory === c.id ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'"
                        x-text="c.name"></button>
            </template>
        </nav>

        {{-- Daftar menu per kategori --}}
        <template x-for="c in filteredMenu" :key="'s' + c.id">
            <section :id="'kategori-' + c.id" class="scroll-mt-20 pt-5"
                     x-intersect:enter.margin.-40%.0px.-55%.0px="activeCategory = c.id">
                <h2 class="flex items-baseline gap-2 text-lg font-bold">
                    <span x-text="c.name"></span>
                    <span class="text-sm font-medium text-gray-400" x-text="c.products.length + ' menu'"></span>
                </h2>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <template x-for="p in c.products" :key="p.id">
                        <article class="flex gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-gray-100" :class="!p.available && 'opacity-60'">
                            <button type="button" @click="openDetail(p)" :disabled="!p.available" class="relative h-24 w-24 shrink-0 overflow-hidden rounded-xl sm:h-28 sm:w-28">
                                <template x-if="p.image"><img :src="p.image" :alt="p.name" loading="lazy" decoding="async" class="h-full w-full object-cover"></template>
                                <template x-if="!p.image"><span class="grid h-full w-full place-items-center bg-gradient-to-br text-4xl" :class="placeholder(p.group).bg" x-text="placeholder(p.group).emoji"></span></template>
                                <span x-show="!p.available" class="absolute inset-0 grid place-items-center bg-black/45 text-xs font-bold text-white">Habis</span>
                            </button>
                            <div class="flex min-w-0 flex-1 flex-col">
                                <button type="button" @click="openDetail(p)" :disabled="!p.available" class="text-left">
                                    <h3 class="text-[15px] leading-snug font-bold" x-text="p.name"></h3>
                                    <p class="mt-0.5 line-clamp-2 text-xs text-gray-500" x-text="p.description"></p>
                                </button>
                                <p x-show="p.available && p.left !== null && p.left <= {{ \App\Models\Product::LOW_SERVINGS }}" x-cloak
                                   class="mt-1 text-[11px] font-bold text-red-600" x-text="'Sisa ' + p.left + ' porsi'"></p>
                                <div class="mt-auto flex items-center justify-between gap-2 pt-2">
                                    <span class="font-bold" x-text="rupiah(p.price)"></span>
                                    <template x-if="p.available && !qtyOf(p.id)">
                                        <button type="button" @click="add(p.id)" aria-label="Tambah"
                                                class="grid h-9 w-9 place-items-center rounded-full bg-brand-500 text-white shadow-md shadow-brand-500/30 transition active:scale-90">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </template>
                                    <template x-if="p.available && qtyOf(p.id)">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="decrement(p.id)" aria-label="Kurangi"
                                                    class="grid h-8 w-8 place-items-center rounded-full border border-gray-300 text-gray-700 transition active:scale-90">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/></svg>
                                            </button>
                                            <span class="min-w-5 text-center font-bold tabular-nums" x-text="qtyOf(p.id)"></span>
                                            <button type="button" @click="add(p.id)" aria-label="Tambah satu lagi" :disabled="!canAdd(p.id)"
                                                    class="grid h-8 w-8 place-items-center rounded-full bg-brand-500 text-white transition active:scale-90 disabled:opacity-30">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <span x-show="!p.available" class="text-xs font-semibold text-gray-500">Habis</span>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </section>
        </template>

        <p x-show="search && !filteredMenu.length" class="py-16 text-center text-gray-500">Menu "<span x-text="search"></span>" tidak ditemukan.</p>
    </main>

    {{-- Bar keranjang + navigasi bawah (HP) --}}
    <div x-show="!cartOpen" class="fixed inset-x-0 bottom-0 z-30 lg:hidden">
        <div x-show="itemCount" x-cloak x-transition.opacity class="px-4 pb-3">
            <button type="button" @click="cartOpen = true" :class="bump && 'scale-[1.03]'"
                    class="flex w-full items-center justify-between rounded-2xl bg-brand-500 px-5 py-3.5 text-white shadow-xl shadow-brand-500/30 transition">
                <span class="flex items-center gap-3">
                    <span class="grid h-7 min-w-7 place-items-center rounded-full bg-white px-2 text-sm font-bold text-brand-600" x-text="itemCount"></span>
                    <span class="font-bold">Lihat keranjang</span>
                </span>
                <span class="font-bold" x-text="rupiah(total)"></span>
            </button>
        </div>
        <nav class="grid grid-cols-4 border-t border-gray-100 bg-white pb-[env(safe-area-inset-bottom)] text-[11px] font-semibold text-gray-500" aria-label="Navigasi">
            <button type="button" @click="search = ''; window.scrollTo({ top: 0, behavior: 'smooth' })" class="flex flex-col items-center gap-1 py-2.5 text-brand-500">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.3 3.3a1 1 0 0 1 1.4 0l8 7.6A1 1 0 0 1 20 12.6h-1V20a1 1 0 0 1-1 1h-4v-5h-4v5H6a1 1 0 0 1-1-1v-7.4H4a1 1 0 0 1-.7-1.7l8-7.6Z"/></svg>Menu
            </button>
            <button type="button" @click="focusSearch()" class="flex flex-col items-center gap-1 py-2.5">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>Cari
            </button>
            <button type="button" @click="cartOpen = true" class="relative flex flex-col items-center gap-1 py-2.5">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6.2M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>Keranjang
                <span x-show="itemCount" x-cloak x-text="itemCount" class="absolute top-1 left-1/2 ml-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-brand-500 px-1 text-[10px] text-white"></span>
            </button>
            <a href="{{ route('menu.history') }}" class="flex flex-col items-center gap-1 py-2.5">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/></svg>Pesanan saya
            </a>
        </nav>
    </div>

    {{-- Keranjang: panel kanan (desktop/tablet) / bottom sheet (HP) --}}
    <div x-show="cartOpen" x-cloak x-transition.opacity @click="cartOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>
    <aside class="fixed inset-x-0 bottom-0 z-50 flex max-h-[92vh] flex-col rounded-t-3xl bg-white shadow-2xl transition-transform duration-300 lg:inset-y-0 lg:right-0 lg:left-auto lg:max-h-none lg:w-[400px] lg:translate-y-0 lg:rounded-none lg:border-l lg:border-gray-100 lg:shadow-none"
           :class="cartOpen ? 'translate-y-0' : 'translate-y-full'" x-cloak>
        <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-gray-200 lg:hidden"></div>
        <div class="flex items-center justify-between px-6 pt-3 pb-3 lg:pt-6">
            <h2 class="text-xl font-bold">Keranjang Anda</h2>
            <div class="flex items-center gap-2">
                <button type="button" x-show="cart.length" @click="if (confirm('Kosongkan keranjang?')) clearCart()" class="grid h-9 w-9 place-items-center rounded-full text-gray-500 hover:bg-gray-100" aria-label="Kosongkan keranjang">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg>
                </button>
                <button type="button" @click="cartOpen = false" class="grid h-9 w-9 place-items-center rounded-full bg-gray-100 lg:hidden" aria-label="Tutup">✕</button>
            </div>
        </div>

        <form method="POST" action="{{ route('menu.store') }}" @submit="submitting = true" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                @if ($errors->any())
                    <div class="mb-3 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif

                <template x-if="!cart.length">
                    <div class="py-12 text-center text-gray-500">
                        <div class="text-5xl">🛒</div>
                        <p class="mt-3 font-bold text-gray-800">Keranjang masih kosong</p>
                        <p class="text-sm">Ketuk tombol + pada menu untuk menambah.</p>
                    </div>
                </template>

                <ul class="space-y-3">
                    <template x-for="(line, i) in cart" :key="i + '-' + line.id + line.note">
                        <li class="flex gap-3 rounded-2xl p-2 ring-1 ring-gray-100">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl">
                                <template x-if="product(line.id).image"><img loading="lazy" decoding="async" :src="product(line.id).image" class="h-full w-full object-cover" alt=""></template>
                                <template x-if="!product(line.id).image">
                                    <div class="grid h-full w-full place-items-center bg-gradient-to-br text-2xl" :class="placeholder(product(line.id).group).bg" x-text="placeholder(product(line.id).group).emoji"></div>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1 py-0.5">
                                <p class="truncate text-sm font-bold" x-text="product(line.id).name"></p>
                                <p x-show="line.note" class="line-clamp-2 text-xs text-gray-500" x-text="line.note"></p>
                                <p class="mt-0.5 text-sm font-semibold text-brand-600" x-text="rupiah(product(line.id).price * line.qty)"></p>
                            </div>
                            <div class="flex items-center gap-2 self-center pr-1">
                                <button type="button" @click="changeLine(i, -1)" aria-label="Kurangi" class="grid h-8 w-8 place-items-center rounded-full border border-gray-300 text-gray-700 active:scale-90">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="min-w-5 text-center text-sm font-bold tabular-nums" x-text="line.qty"></span>
                                <button type="button" @click="changeLine(i, 1)" aria-label="Tambah" :disabled="!canAdd(line.id)" class="grid h-8 w-8 place-items-center rounded-full bg-brand-500 text-white active:scale-90 disabled:opacity-30">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </div>
                            <input type="hidden" :name="`items[${i}][product_id]`" :value="line.id">
                            <input type="hidden" :name="`items[${i}][quantity]`" :value="line.qty">
                            <input type="hidden" :name="`items[${i}][note]`" :value="line.note">
                        </li>
                    </template>
                </ul>

                <button type="button" x-show="cart.length" @click="cartOpen = false" class="mt-3 flex w-full items-center justify-between rounded-2xl px-2 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 lg:hidden">
                    <span>＋ Tambah menu lain</span><span>→</span>
                </button>

                <div x-show="cart.length" class="space-y-4 py-4">
                    <div>
                        <label for="customer_name" class="mb-1 block text-sm font-semibold text-gray-700">Nama pemesan</label>
                        <input id="customer_name" name="customer_name" x-model="customerName" required maxlength="50" class="w-full rounded-xl border border-gray-200 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Supaya kami bisa memanggil Anda">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="table_number" class="mb-1 block text-sm font-semibold text-gray-700">No. meja</label>
                            <input id="table_number" name="table_number" x-model="tableNumber" maxlength="10" class="w-full rounded-xl border border-gray-200 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Kosong = bawa pulang">
                        </div>
                        <div>
                            <label for="member_phone" class="mb-1 block text-sm font-semibold text-gray-700">No. HP <span class="font-normal text-gray-400">(poin)</span></label>
                            <input id="member_phone" name="member_phone" x-model="memberPhone" inputmode="tel" maxlength="20" class="w-full rounded-xl border border-gray-200 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Opsional">
                        </div>
                    </div>
                    <fieldset>
                        <legend class="mb-1 block text-sm font-semibold text-gray-700">Cara pembayaran</legend>
                        <div class="grid gap-2" style="grid-template-columns: repeat({{ count($paymentMethods) }}, minmax(0, 1fr))">
                            @foreach (array_intersect_key([
                                'qris' => ['QRIS', 'Scan & bayar', 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 18h2v2h-2zM14 18h2'],
                                'transfer' => ['Transfer', 'Rekening bank', 'M3 10h18M5 10V8l7-4 7 4v2M6 10v8m4-8v8m4-8v8m4-8v8M3 20h18'],
                                'tunai' => ['Tunai', 'Bayar di kasir', 'M3 7h18v10H3zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z'],
                            ], $paymentMethods) as $value => [$title, $hint, $icon])
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="{{ $value }}" x-model="paymentMethod" required class="peer sr-only">
                                    <span class="flex h-full flex-col items-center gap-1 rounded-xl border-2 border-gray-200 px-2 py-3 text-center transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-200">
                                        <svg class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="{{ $icon }}"/></svg>
                                        <span class="text-sm font-bold">{{ $title }}</span>
                                        <span class="text-[11px] leading-tight text-gray-500">{{ $hint }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="paymentMethod === 'tunai'" x-cloak class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900">Pembayaran tunai hanya diterima langsung di kasir. Pesanan dibuat setelah Anda membayar.</p>
                        <p x-show="paymentMethod && paymentMethod !== 'tunai'" x-cloak class="mt-2 rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-600">Setelah mengirim pesanan, Anda akan melihat <span x-text="paymentMethod === 'qris' ? 'kode QRIS' : 'nomor rekening'"></span> dan bisa mengunggah bukti bayar.</p>
                    </fieldset>
                    <div>
                        <label for="voucher_code" class="mb-1 block text-sm font-semibold text-gray-700">Kode voucher</label>
                        <div class="flex gap-2">
                            <input id="voucher_code" name="voucher_code" x-model="voucherCode" @keydown.enter.prevent="applyVoucher()" maxlength="30" class="w-full rounded-xl border border-dashed border-gray-300 px-3.5 py-3 text-sm uppercase outline-none focus:border-brand-500" placeholder="Punya kode promo?">
                            <button type="button" @click="applyVoucher()" class="shrink-0 rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white">Pakai</button>
                        </div>
                        <p x-show="voucherError" x-text="voucherError" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 px-6 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                <div x-show="discount" class="mb-1 flex items-center justify-between text-sm text-green-700">
                    <span>🎉 <span x-text="appliedPromo?.title"></span></span>
                    <span x-text="'−' + rupiah(discount)"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Total</span>
                    <span class="text-2xl font-extrabold" x-text="rupiah(total)"></span>
                </div>
                <button type="submit" :disabled="!cart.length || !paymentMethod || submitting"
                        class="mt-3 w-full rounded-2xl bg-brand-500 py-4 font-bold text-white shadow-lg shadow-brand-500/30 transition hover:bg-brand-600 disabled:opacity-40">
                    <span x-text="submitting ? 'Mengirim…' : (paymentMethod ? 'Kirim pesanan & bayar' : 'Pilih cara pembayaran')"></span>
                </button>
                <p class="mt-2 text-center text-xs text-gray-400">Pesanan dibuat setelah pembayaran dikonfirmasi.</p>
            </div>
        </form>
    </aside>

    {{-- Kartu promo saat pertama membuka menu (sekali per kunjungan) --}}
    @if ($popup)
        <div x-data="{
                show: false,
                copied: false,
                init() {
                    let seen = null;
                    try { seen = sessionStorage.getItem('antara-promo') } catch (e) {}
                    if (seen !== @js($popup['key'])) setTimeout(() => this.show = true, 600);
                },
                close() {
                    this.show = false;
                    try { sessionStorage.setItem('antara-promo', @js($popup['key'])) } catch (e) {}
                },
             }"
             x-show="show" x-cloak @keydown.escape.window="close()"
             class="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="promo-title">
            <div x-show="show" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>
            <div x-show="show" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-8 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                 class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="relative {{ $popup['image'] ? 'aspect-[4/3]' : 'py-10' }} overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700">
                    @if ($popup['image'])
                        <img loading="lazy" decoding="async" src="{{ $popup['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    @else
                        <div class="pointer-events-none absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/15"></div>
                        <p class="relative text-center text-6xl drop-shadow-lg">🎁</p>
                    @endif
                    <span class="absolute top-4 left-4 rounded-md bg-white px-3 py-1 text-xs font-bold tracking-wide text-brand-600 uppercase shadow">Promo</span>
                    <button type="button" @click="close()" class="absolute top-3 right-3 grid h-9 w-9 place-items-center rounded-full bg-white/90 text-gray-900 shadow" aria-label="Tutup promo">✕</button>
                </div>
                <div class="p-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] text-center">
                    <h2 id="promo-title" class="text-2xl leading-tight font-extrabold">{{ $popup['title'] }}</h2>
                    @if ($popup['description'])
                        <p class="mt-2 text-sm text-gray-500">{{ $popup['description'] }}</p>
                    @endif
                    @if ($popup['summary'])
                        <p class="mt-3 inline-block rounded-md bg-brand-50 px-3 py-1 text-sm font-bold text-brand-700">{{ $popup['summary'] }}</p>
                    @endif

                    @if ($popup['voucher'])
                        <div class="mt-4 flex items-center justify-between gap-3 rounded-lg border-2 border-dashed border-brand-200 bg-brand-50 px-4 py-3">
                            <div class="text-left">
                                <p class="text-[11px] tracking-widest text-gray-500 uppercase">Kode voucher</p>
                                <p class="text-xl font-extrabold tracking-wider">{{ $popup['voucher'] }}</p>
                            </div>
                            <button type="button" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-bold text-white"
                                    @click="voucherCode = @js($popup['voucher']); applyVoucher(); copied = true; navigator.clipboard?.writeText(@js($popup['voucher'])).catch(() => {})"
                                    x-text="copied ? 'Terpasang ✓' : 'Pakai kode'">Pakai kode</button>
                        </div>
                    @elseif ($popup['summary'])
                        <p class="mt-3 text-xs text-gray-500">Diskon otomatis terpasang di keranjang Anda.</p>
                    @endif

                    @if ($popup['until'])
                        <p class="mt-3 text-xs text-gray-500">Berlaku sampai {{ $popup['until'] }}</p>
                    @endif

                    <button type="button" @click="close()" class="mt-5 w-full rounded-xl bg-gray-900 py-3.5 font-bold text-white transition hover:bg-gray-800">
                        Lihat menu
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail menu: catatan gratis dan tawaran minuman --}}
    <div x-show="detail" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center" @keydown.escape.window="detail = null">
        <div x-show="detail" x-transition.opacity class="absolute inset-0 bg-black/60" @click="detail = null"></div>
        <template x-if="detail">
            <div x-transition class="relative flex max-h-[94vh] w-full max-w-md flex-col overflow-hidden rounded-t-3xl bg-white sm:rounded-3xl">
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <div class="relative aspect-[4/3]">
                        <template x-if="detail.image"><img loading="lazy" decoding="async" :src="detail.image" :alt="detail.name" class="h-full w-full object-cover"></template>
                        <template x-if="!detail.image">
                            <div class="grid h-full w-full place-items-center bg-gradient-to-br text-8xl" :class="placeholder(detail.group).bg"><span class="drop-shadow-xl" x-text="placeholder(detail.group).emoji"></span></div>
                        </template>
                        <button type="button" @click="detail = null" class="absolute top-4 left-4 grid h-10 w-10 place-items-center rounded-full bg-white/95 text-gray-900 shadow" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                    </div>
                    <div class="relative -mt-6 rounded-t-3xl bg-white px-6 pt-7 pb-4">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="text-2xl leading-tight font-extrabold" x-text="detail.name"></h3>
                            <span class="shrink-0 pt-1 text-xl leading-tight font-extrabold text-brand-600" x-text="rupiah(detail.price)"></span>
                        </div>
                        <p class="mt-1 text-sm leading-snug text-gray-500" x-text="detail.description"></p>
                        <p x-show="detail.prep" class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">⏱ ± <span x-text="detail.prep"></span> menit dibuat</p>
                        <p x-show="detail.left !== null && detail.left <= {{ \App\Models\Product::LOW_SERVINGS }}" class="mt-2 text-sm font-bold text-red-600"
                           x-text="'Tinggal ' + detail.left + ' porsi hari ini' + (qtyOf(detail.id) ? ' (' + qtyOf(detail.id) + ' sudah di keranjang)' : '')"></p>

                        <h4 class="mt-5 text-sm font-bold">Sesuaikan pesanan <span class="font-medium text-green-700">· gratis</span></h4>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="option in optionsFor(detail.group)" :key="option">
                                <button type="button" @click="toggleOption(option)"
                                        class="rounded-full border px-3.5 py-1.5 text-sm font-semibold transition"
                                        :class="detailOptions.includes(option) ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                                        x-text="option"></button>
                            </template>
                        </div>
                        <input id="detail_note" x-model="detailNote" maxlength="80" aria-label="Catatan lain" class="mt-3 w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500" placeholder="Catatan lain (opsional)">

                        <template x-if="drinkPairings.length">
                            <div class="mt-5">
                                <h4 class="text-sm font-bold">Tambah minuman?</h4>
                                <div class="no-scrollbar -mx-6 mt-2 flex gap-3 overflow-x-auto px-6 pb-1">
                                    <template x-for="d in drinkPairings" :key="'d' + d.id">
                                        <div class="flex w-48 shrink-0 items-center gap-2 rounded-xl p-2 ring-1" :class="qtyOf(d.id) ? 'ring-brand-500 bg-brand-50' : 'ring-gray-200'">
                                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg">
                                                <template x-if="d.image"><img :src="d.image" alt="" loading="lazy" class="h-full w-full object-cover"></template>
                                                <template x-if="!d.image"><span class="grid h-full w-full place-items-center bg-gradient-to-br text-lg" :class="placeholder(d.group).bg" x-text="placeholder(d.group).emoji"></span></template>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-xs font-bold" x-text="d.name"></p>
                                                <p class="text-[11px] text-gray-500" x-text="'+' + rupiah(d.price)"></p>
                                            </div>
                                            <button type="button" @click="add(d.id)" :disabled="!canAdd(d.id)" :aria-label="'Tambah ' + d.name"
                                                    class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-500 text-xs font-bold text-white disabled:opacity-30"
                                                    x-text="qtyOf(d.id) || '+'"></button>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-1 text-[11px] text-gray-400">Minuman masuk keranjang sebagai pesanan terpisah.</p>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex items-center gap-3 border-t border-gray-100 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="detailQty = Math.max(1, detailQty - 1)" class="grid h-10 w-10 place-items-center rounded-full border border-gray-300 text-lg font-bold" aria-label="Kurangi">−</button>
                        <span class="min-w-6 text-center text-lg font-bold tabular-nums" x-text="detailQty"></span>
                        <button type="button" @click="detailQty++" :disabled="!canAdd(detail.id, detailQty + 1)" class="grid h-10 w-10 place-items-center rounded-full bg-brand-500 text-lg font-bold text-white disabled:opacity-30" aria-label="Tambah">+</button>
                    </div>
                    <button type="button" @click="addDetail()" :disabled="!canAdd(detail.id, detailQty)" class="flex-1 rounded-2xl bg-brand-500 py-3.5 font-bold text-white shadow-lg shadow-brand-500/30 hover:bg-brand-600 disabled:opacity-40">
                        Tambah · <span x-text="rupiah(detail.price * detailQty)"></span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
</body>
</html>
