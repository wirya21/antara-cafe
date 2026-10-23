<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Antara Cafe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-espresso-50 font-sans text-espresso-900 antialiased">
    @php
        $nav = [
            ['admin.dashboard', 'Dashboard', 'admin.dashboard'],
            ['admin.reports.index', 'Laporan', 'admin.reports.*'],
            ['admin.orders.index', 'Transaksi', 'admin.orders.*'],
            ['admin.products.index', 'Produk', 'admin.products.*'],
            ['admin.ingredients.index', 'Stok bahan', 'admin.ingredients.*'],
            ['admin.categories.index', 'Kategori', 'admin.categories.*'],
            ['admin.promotions.index', 'Promo & voucher', 'admin.promotions.*'],
            ['admin.members.index', 'Member', 'admin.members.*'],
            ['admin.users.index', 'Akun', 'admin.users.*'],
            ['admin.settings.edit', 'Pengaturan', 'admin.settings.*'],
            ['cashier.index', 'Buka kasir ↗', 'cashier.*'],
            ['menu', 'Lihat menu pelanggan ↗', 'menu'],
            ['display', 'Buka layar TV ↗', 'display'],
        ];
    @endphp
    <div x-data="{ navOpen: false }" class="flex min-h-screen flex-col md:flex-row">
        {{-- Bar atas HP: logo, judul halaman, tombol menu --}}
        <div class="print:hidden sticky top-0 z-40 flex items-center gap-3 bg-espresso-900 px-4 py-3 text-white shadow-md md:hidden">
            <button type="button" @click="navOpen = true" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 transition hover:bg-white/20" aria-label="Buka menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <p class="text-xs leading-none text-espresso-200">☕ Antara Cafe</p>
                <p class="mt-1 truncate text-base leading-tight font-semibold">@yield('title')</p>
            </div>
            <x-order-alerts :href="route('admin.orders.index', ['status' => 'pending', 'from' => now()->subDays(2)->toDateString()])" dark />
        </div>

        <div x-show="navOpen" x-cloak x-transition.opacity @click="navOpen = false" class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

        <aside class="print:hidden fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col bg-espresso-900 text-espresso-100 shadow-2xl transition-transform duration-200 md:static md:z-auto md:w-60 md:max-w-none md:shrink-0 md:translate-x-0 md:shadow-none"
               :class="navOpen && 'translate-x-0'" @keydown.escape.window="navOpen = false">
            <div class="flex items-center justify-between px-5 pt-[max(1.25rem,env(safe-area-inset-top))] pb-4 md:py-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold tracking-wide text-white">☕ Antara Cafe</a>
                    <p class="mt-1 text-xs text-espresso-200">Panel admin</p>
                </div>
                <button type="button" @click="navOpen = false" class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 md:hidden" aria-label="Tutup menu">✕</button>
            </div>
            <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 pb-4">
                @foreach ($nav as [$route, $label, $pattern])
                    <a href="{{ route($route) }}"
                       class="rounded-lg px-3 py-2.5 text-sm md:py-2 {{ request()->routeIs($pattern) ? 'bg-espresso-700 font-semibold text-white' : 'hover:bg-espresso-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-white/10 px-5 py-4 pb-[max(1rem,env(safe-area-inset-bottom))] md:hidden">
                <a href="{{ route('password.edit') }}" class="block truncate text-sm text-espresso-200 hover:text-white">{{ auth()->user()->name }} · ganti password</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button class="w-full rounded-lg bg-white/10 px-3 py-2.5 text-sm font-semibold text-white hover:bg-white/20">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="print:hidden hidden items-center justify-between border-b border-espresso-100 bg-white px-6 py-3 md:flex">
                <h1 class="text-lg font-semibold">@yield('title')</h1>
                <div class="flex items-center gap-3 text-sm">
                    <x-order-alerts :href="route('admin.orders.index', ['status' => 'pending', 'from' => now()->subDays(2)->toDateString()])" />
                    <a href="{{ route('password.edit') }}" class="text-espresso-600 hover:underline" title="Ganti password">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-ghost py-1.5">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
