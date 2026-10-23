<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2b1b0e">
    <title>Menu · Antara Cafe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f1ea] font-sans text-espresso-900 antialiased">
<div x-data="menuApp(@js($menu), @js([
        'tableNumber' => old('table_number', $table),
        'customerName' => old('customer_name', ''),
        'memberPhone' => old('member_phone', ''),
        'voucherCode' => old('voucher_code', ''),
        'openCart' => $errors->any(),
        'autoPromos' => $autoPromos,
        'voucherUrl' => route('menu.voucher'),
    ]))" class="min-h-screen lg:pr-[400px]">

    {{-- Hero --}}
    <header class="relative overflow-hidden bg-espresso-900 text-espresso-50">
        <div class="pointer-events-none absolute -top-24 -right-16 h-72 w-72 rounded-full bg-espresso-600/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-10 h-72 w-72 rounded-full bg-amber-500/20 blur-3xl"></div>

        <div class="relative mx-auto max-w-6xl px-4 pt-[max(1.25rem,env(safe-area-inset-top))] pb-7 sm:px-6 sm:pt-8 sm:pb-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-espresso-50 text-xl">☕</span>
                    <span class="font-display text-xl font-semibold tracking-tight">Antara Cafe</span>
                </div>
                <template x-if="tableNumber">
                    <span class="rounded-full border border-espresso-50/30 bg-espresso-50/10 px-3 py-1 text-sm backdrop-blur">Meja <b x-text="tableNumber"></b></span>
                </template>
            </div>

            <h1 class="mt-6 max-w-md font-display text-3xl leading-tight font-semibold sm:text-4xl">
                Mau ngopi apa hari ini?
            </h1>
            <p class="mt-2 text-sm text-espresso-200">Pilih menu, kirim pesanan, bayar di kasir. Tanpa daftar, tanpa login.</p>

            <label class="mt-6 flex items-center gap-3 rounded-2xl bg-white px-4 py-3 text-espresso-900 shadow-lg shadow-black/20">
                <svg class="h-5 w-5 text-espresso-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input x-model="search" type="search" placeholder="Cari latte, matcha, nasi goreng…" class="w-full bg-transparent text-sm outline-none placeholder:text-espresso-500/70">
            </label>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pb-32 sm:px-6 lg:pb-12">

        {{-- Banner promo --}}
        @if ($banners->isNotEmpty())
            <section x-show="!search" class="no-scrollbar -mx-4 mt-6 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 sm:-mx-6 sm:scroll-px-6 sm:px-6">
                @foreach ($banners as $banner)
                    <div class="relative aspect-[21/9] w-[88%] shrink-0 snap-center overflow-hidden rounded-3xl bg-espresso-800 sm:w-[60%]">
                        <img src="{{ Storage::disk('public')->url($banner->image_path) }}" alt="{{ $banner->title }}" class="h-full w-full object-cover">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4 text-white">
                            <p class="font-display text-lg font-semibold">{{ $banner->title }}</p>
                            @if ($banner->description)<p class="text-sm text-white/80">{{ $banner->description }}</p>@endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Favorit --}}
        <section x-show="!search && featured.length" class="mt-8">
            <div class="flex items-end justify-between">
                <h2 class="font-display text-xl font-semibold">Favorit pelanggan ⭐</h2>
            </div>
            <div class="no-scrollbar -mx-4 mt-3 flex snap-x scroll-px-4 gap-4 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:scroll-px-6 sm:px-6">
                <template x-for="p in featured" :key="'f' + p.id">
                    <button type="button" @click="openDetail(p)" class="group relative h-56 w-44 shrink-0 snap-start overflow-hidden rounded-3xl text-left shadow-md shadow-espresso-900/10 sm:w-52">
                        <template x-if="p.image">
                            <img :src="p.image" :alt="p.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        </template>
                        <template x-if="!p.image">
                            <div class="absolute inset-0 grid place-items-center bg-gradient-to-br text-6xl" :class="placeholder(p.group).bg">
                                <span class="drop-shadow-lg transition duration-500 group-hover:scale-110" x-text="placeholder(p.group).emoji"></span>
                            </div>
                        </template>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 pt-10 text-white">
                            <p class="font-semibold leading-tight" x-text="p.name"></p>
                            <p class="mt-0.5 text-sm text-amber-200" x-text="rupiah(p.price)"></p>
                        </div>
                        <span x-show="qtyOf(p.id)" x-text="qtyOf(p.id)" class="absolute top-3 right-3 grid h-7 min-w-7 place-items-center rounded-full bg-amber-400 px-2 text-sm font-bold text-espresso-900"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- Kategori --}}
        <nav class="no-scrollbar sticky top-0 z-20 -mx-4 mt-6 flex gap-2 overflow-x-auto bg-[#f7f1ea]/90 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
            <template x-for="c in filteredMenu" :key="'c' + c.id">
                <button type="button" @click="scrollToCategory(c.id)"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                        :class="activeCategory === c.id ? 'bg-espresso-800 text-white shadow-md shadow-espresso-900/20' : 'bg-white text-espresso-700 hover:bg-espresso-100'">
                    <span x-text="placeholder(c.group).emoji"></span> <span x-text="c.name"></span>
                </button>
            </template>
        </nav>

        {{-- Produk --}}
        <template x-for="c in filteredMenu" :key="'s' + c.id">
            <section :id="'kategori-' + c.id" class="scroll-mt-20 pt-4"
                     x-intersect:enter.margin.-40%.0px.-55%.0px="activeCategory = c.id">
                <h2 class="font-display text-xl font-semibold" x-text="c.name"></h2>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 xl:grid-cols-4">
                    <template x-for="p in c.products" :key="p.id">
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-espresso-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-espresso-900/10"
                                 :class="!p.available && 'opacity-60'">
                            <button type="button" @click="openDetail(p)" class="relative aspect-square overflow-hidden" :disabled="!p.available">
                                <template x-if="p.image">
                                    <img :src="p.image" :alt="p.name" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                </template>
                                <template x-if="!p.image">
                                    <div class="grid h-full w-full place-items-center bg-gradient-to-br text-5xl" :class="placeholder(p.group).bg">
                                        <span class="drop-shadow-lg transition duration-500 group-hover:scale-110" x-text="placeholder(p.group).emoji"></span>
                                    </div>
                                </template>
                                <span x-show="!p.available" class="absolute inset-0 grid place-items-center bg-black/40 text-sm font-semibold text-white">Habis</span>
                            </button>
                            <div class="flex flex-1 flex-col p-3 sm:p-4">
                                <h3 class="text-sm leading-tight font-semibold sm:text-base" x-text="p.name"></h3>
                                <p class="mt-1 line-clamp-2 text-xs text-espresso-600" x-text="p.description"></p>
                                <p class="mt-auto pt-2 font-bold text-espresso-700" x-text="rupiah(p.price)"></p>
                                <div class="pt-2">
                                    <template x-if="p.available && !qtyOf(p.id)">
                                        <button type="button" @click="add(p.id)" aria-label="Tambah"
                                                class="flex h-10 w-full items-center justify-center gap-1.5 rounded-full bg-espresso-800 text-sm font-semibold text-white shadow-md shadow-espresso-900/20 transition hover:bg-espresso-700 active:scale-95">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                            Tambah
                                        </button>
                                    </template>
                                    <template x-if="p.available && qtyOf(p.id)">
                                        <div class="flex h-10 w-full items-center justify-between rounded-full bg-espresso-100 p-1">
                                            <button type="button" @click="decrement(p.id)" aria-label="Kurangi"
                                                    class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-espresso-800 shadow-sm transition active:scale-90">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/></svg>
                                            </button>
                                            <span class="min-w-6 text-center text-base font-bold tabular-nums text-espresso-900" x-text="qtyOf(p.id)"></span>
                                            <button type="button" @click="add(p.id)" aria-label="Tambah satu lagi"
                                                    class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-espresso-800 text-white shadow-sm transition active:scale-90">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <p x-show="!p.available" class="flex h-10 items-center justify-center rounded-full bg-espresso-50 text-sm font-semibold text-espresso-500">Habis</p>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </section>
        </template>

        <p x-show="search && !filteredMenu.length" class="py-16 text-center text-espresso-600">Menu "<span x-text="search"></span>" tidak ditemukan.</p>
    </main>

    {{-- Bar keranjang (HP) --}}
    <div x-show="itemCount && !cartOpen" x-cloak x-transition.opacity class="fixed inset-x-0 bottom-0 z-30 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] lg:hidden">
        <button type="button" @click="cartOpen = true" :class="bump && 'scale-[1.03]'"
                class="flex w-full items-center justify-between rounded-2xl bg-espresso-900 px-5 py-4 text-white shadow-2xl shadow-espresso-900/40 transition">
            <span class="flex items-center gap-3">
                <span class="grid h-8 min-w-8 place-items-center rounded-full bg-amber-400 px-2 text-sm font-bold text-espresso-900" x-text="itemCount"></span>
                <span class="font-semibold">Lihat pesanan</span>
            </span>
            <span class="font-bold" x-text="rupiah(total)"></span>
        </button>
    </div>

    {{-- Keranjang: panel kanan (desktop/tablet) / bottom sheet (HP) --}}
    <div x-show="cartOpen" x-cloak x-transition.opacity @click="cartOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>
    <aside class="fixed inset-x-0 bottom-0 z-50 flex max-h-[90vh] flex-col rounded-t-3xl bg-white shadow-2xl transition-transform duration-300 lg:inset-y-0 lg:right-0 lg:left-auto lg:max-h-none lg:w-[400px] lg:translate-y-0 lg:rounded-none lg:border-l lg:border-espresso-100 lg:shadow-none"
           :class="cartOpen ? 'translate-y-0' : 'translate-y-full'" x-cloak>
        <div class="flex items-center justify-between px-6 pt-5 pb-3">
            <h2 class="font-display text-xl font-semibold">Pesanan Anda</h2>
            <button type="button" @click="cartOpen = false" class="grid h-9 w-9 place-items-center rounded-full bg-espresso-50 lg:hidden" aria-label="Tutup">✕</button>
        </div>

        <form method="POST" action="{{ route('menu.store') }}" @submit="submitting = true" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                @if ($errors->any())
                    <div class="mb-3 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif

                <template x-if="!cart.length">
                    <div class="py-12 text-center text-espresso-600">
                        <div class="text-5xl">🛒</div>
                        <p class="mt-3 font-semibold text-espresso-800">Keranjang masih kosong</p>
                        <p class="text-sm">Ketuk tombol + pada menu untuk menambah.</p>
                    </div>
                </template>

                <ul class="divide-y divide-espresso-100">
                    <template x-for="(line, i) in cart" :key="i + '-' + line.id + line.note">
                        <li class="flex gap-3 py-3">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl">
                                <template x-if="product(line.id).image"><img :src="product(line.id).image" class="h-full w-full object-cover" alt=""></template>
                                <template x-if="!product(line.id).image">
                                    <div class="grid h-full w-full place-items-center bg-gradient-to-br text-2xl" :class="placeholder(product(line.id).group).bg" x-text="placeholder(product(line.id).group).emoji"></div>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold" x-text="product(line.id).name"></p>
                                <p x-show="line.note" class="truncate text-xs text-espresso-600" x-text="'Catatan: ' + line.note"></p>
                                <p class="text-sm font-semibold text-espresso-700" x-text="rupiah(product(line.id).price * line.qty)"></p>
                            </div>
                            <div class="flex items-center gap-2 self-center">
                                <button type="button" @click="changeLine(i, -1)" aria-label="Kurangi" class="grid h-8 w-8 place-items-center rounded-full bg-espresso-100 text-espresso-800 active:scale-90">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="min-w-6 text-center text-sm font-bold tabular-nums" x-text="line.qty"></span>
                                <button type="button" @click="changeLine(i, 1)" aria-label="Tambah" class="grid h-8 w-8 place-items-center rounded-full bg-espresso-800 text-white active:scale-90">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </div>
                            <input type="hidden" :name="`items[${i}][product_id]`" :value="line.id">
                            <input type="hidden" :name="`items[${i}][quantity]`" :value="line.qty">
                            <input type="hidden" :name="`items[${i}][note]`" :value="line.note">
                        </li>
                    </template>
                </ul>

                <div x-show="cart.length" class="space-y-3 py-4">
                    <div>
                        <label for="customer_name" class="label">Nama pemesan</label>
                        <input id="customer_name" name="customer_name" x-model="customerName" required maxlength="50" class="input py-2.5" placeholder="Supaya kami bisa memanggil Anda">
                    </div>
                    <div>
                        <label for="table_number" class="label">Nomor meja <span class="font-normal text-espresso-500">(kosongkan jika dibawa pulang)</span></label>
                        <input id="table_number" name="table_number" x-model="tableNumber" maxlength="10" class="input py-2.5" placeholder="mis. 5">
                    </div>
                    <div>
                        <label for="member_phone" class="label">No. HP <span class="font-normal text-espresso-500">(opsional, untuk kumpulkan poin member)</span></label>
                        <input id="member_phone" name="member_phone" x-model="memberPhone" inputmode="tel" maxlength="20" class="input py-2.5" placeholder="08…">
                    </div>
                    <div>
                        <label for="voucher_code" class="label">Kode voucher</label>
                        <div class="flex gap-2">
                            <input id="voucher_code" name="voucher_code" x-model="voucherCode" @keydown.enter.prevent="applyVoucher()" maxlength="30" class="input py-2.5 uppercase" placeholder="Punya kode promo?">
                            <button type="button" @click="applyVoucher()" class="btn-ghost">Pakai</button>
                        </div>
                        <p x-show="voucherError" x-text="voucherError" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="border-t border-espresso-100 px-6 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                <div x-show="discount" class="mb-1 flex items-center justify-between text-sm text-green-700">
                    <span>🎉 <span x-text="appliedPromo?.title"></span></span>
                    <span x-text="'−' + rupiah(discount)"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-espresso-600">Total</span>
                    <span class="font-display text-2xl font-semibold" x-text="rupiah(total)"></span>
                </div>
                <button type="submit" :disabled="!cart.length || submitting"
                        class="mt-3 w-full rounded-2xl bg-espresso-800 py-4 font-semibold text-white shadow-lg shadow-espresso-900/20 transition hover:bg-espresso-700 disabled:opacity-40">
                    <span x-text="submitting ? 'Mengirim…' : 'Kirim pesanan'"></span>
                </button>
                <p class="mt-2 text-center text-xs text-espresso-500">Pembayaran dilakukan di kasir.</p>
            </div>
        </form>
    </aside>

    {{-- Detail produk --}}
    <div x-show="detail" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center" @keydown.escape.window="detail = null">
        <div x-show="detail" x-transition.opacity class="absolute inset-0 bg-black/60" @click="detail = null"></div>
        <template x-if="detail">
            <div x-transition class="relative w-full max-w-md overflow-hidden rounded-t-3xl bg-white sm:rounded-3xl">
                <div class="relative aspect-[4/3]">
                    <template x-if="detail.image"><img :src="detail.image" :alt="detail.name" class="h-full w-full object-cover"></template>
                    <template x-if="!detail.image">
                        <div class="grid h-full w-full place-items-center bg-gradient-to-br text-8xl" :class="placeholder(detail.group).bg"><span class="drop-shadow-xl" x-text="placeholder(detail.group).emoji"></span></div>
                    </template>
                    <button type="button" @click="detail = null" class="absolute top-4 right-4 grid h-9 w-9 place-items-center rounded-full bg-white/90 shadow" aria-label="Tutup">✕</button>
                </div>
                <div class="p-6 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="font-display text-2xl font-semibold" x-text="detail.name"></h3>
                        <span class="text-lg font-bold text-espresso-700" x-text="rupiah(detail.price)"></span>
                    </div>
                    <p class="mt-2 text-sm text-espresso-600" x-text="detail.description"></p>
                    <label class="label mt-4" for="detail_note">Catatan <span class="font-normal text-espresso-500">(opsional)</span></label>
                    <input id="detail_note" x-model="detailNote" maxlength="120" class="input" placeholder="mis. less sugar, tanpa es">
                    <div class="mt-5 flex items-center gap-4">
                        <div class="flex items-center gap-3 rounded-full bg-espresso-100 p-1.5">
                            <button type="button" @click="detailQty = Math.max(1, detailQty - 1)" class="grid h-9 w-9 place-items-center rounded-full bg-white text-lg font-bold">−</button>
                            <span class="min-w-6 text-center font-bold tabular-nums" x-text="detailQty"></span>
                            <button type="button" @click="detailQty++" class="grid h-9 w-9 place-items-center rounded-full bg-espresso-800 text-lg font-bold text-white">+</button>
                        </div>
                        <button type="button" @click="addDetail()" class="flex-1 rounded-2xl bg-espresso-800 py-3.5 font-semibold text-white hover:bg-espresso-700">
                            Tambah · <span x-text="rupiah(detail.price * detailQty)"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
</body>
</html>
