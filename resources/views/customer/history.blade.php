<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2b1b0e">
    <title>Pesanan saya · Antara Cafe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-style />
</head>
<body class="min-h-screen bg-[#f7f1ea] font-sans text-espresso-900 antialiased">
<div x-data="orderHistory(@js(route('menu.history.data')))" class="mx-auto max-w-md px-4 pt-[max(1.5rem,env(safe-area-inset-top))] pb-10">
    <div class="flex items-center justify-between">
        <a href="{{ route('menu') }}" class="grid h-10 w-10 place-items-center rounded-full bg-white shadow-sm ring-1 ring-espresso-100" aria-label="Kembali ke menu">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="font-display text-2xl font-semibold">Pesanan saya</h1>
        <span class="w-10"></span>
    </div>
    <p class="mt-2 text-center text-sm text-espresso-600">Tersimpan di HP ini, tanpa perlu daftar member.</p>

    <p x-show="loading" class="py-16 text-center text-espresso-500">Memuat…</p>

    <div x-show="!loading && !orders.length" x-cloak class="py-16 text-center">
        <div class="text-5xl">🧾</div>
        <p class="mt-3 font-semibold">Belum ada pesanan di HP ini</p>
        <a href="{{ route('menu') }}" class="mt-5 inline-block rounded-2xl bg-espresso-800 px-6 py-3 font-semibold text-white">Lihat menu</a>
    </div>

    <template x-if="active.length">
        <section class="mt-6">
            <h2 class="text-xs font-semibold tracking-widest text-espresso-500 uppercase">Sedang berjalan</h2>
            <div class="mt-2 space-y-3">
                <template x-for="o in active" :key="o.code">
                    <a :href="o.url" class="block rounded-3xl bg-white p-4 shadow-sm ring-2 ring-amber-300">
                        <div class="flex items-center justify-between">
                            <span class="font-display text-xl font-semibold" x-text="o.code"></span>
                            <span class="animate-pulse rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800" x-text="o.stage_label"></span>
                        </div>
                        <p class="mt-1 line-clamp-2 text-sm text-espresso-700" x-text="o.items"></p>
                        <p class="mt-2 flex justify-between text-xs text-espresso-500"><span x-text="o.date"></span><span class="font-semibold text-espresso-800" x-text="'Rp' + Number(o.total).toLocaleString('id-ID')"></span></p>
                    </a>
                </template>
            </div>
        </section>
    </template>

    <template x-if="past.length">
        <section class="mt-6">
            <h2 class="text-xs font-semibold tracking-widest text-espresso-500 uppercase">Riwayat</h2>
            <div class="mt-2 space-y-3">
                <template x-for="o in past" :key="o.code">
                    <div class="flex items-start gap-3 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-espresso-100">
                        <a :href="o.url" class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-display text-lg font-semibold" x-text="o.code"></span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                      :class="o.stage === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" x-text="o.stage_label"></span>
                            </div>
                            <p class="mt-1 truncate text-sm text-espresso-700" x-text="o.items"></p>
                            <p class="mt-1 flex justify-between text-xs text-espresso-500"><span x-text="o.date"></span><span class="font-semibold text-espresso-800" x-text="'Rp' + Number(o.total).toLocaleString('id-ID')"></span></p>
                        </a>
                        <button type="button" @click="forget(o.code)" class="text-espresso-300 hover:text-red-600" aria-label="Hapus dari riwayat">✕</button>
                    </div>
                </template>
            </div>
        </section>
    </template>
</div>
</body>
</html>
