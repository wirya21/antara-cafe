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
            ['admin.products.index', 'Produk', 'admin.products.*'],
            ['admin.categories.index', 'Kategori', 'admin.categories.*'],
            ['admin.users.index', 'Akun', 'admin.users.*'],
            ['cashier.index', 'Buka kasir ↗', 'cashier.*'],
            ['menu', 'Lihat menu pelanggan ↗', 'menu'],
            ['display', 'Buka layar TV ↗', 'display'],
        ];
    @endphp
    <div class="flex min-h-screen flex-col md:flex-row">
        <aside class="bg-espresso-900 text-espresso-100 md:w-60 md:shrink-0">
            <div class="flex items-center justify-between px-5 py-4 md:block md:py-6">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold tracking-wide text-white">☕ Antara Cafe</a>
                <p class="hidden text-xs text-espresso-200 md:mt-1 md:block">Panel admin</p>
            </div>
            <nav class="flex gap-1 overflow-x-auto px-3 pb-3 md:flex-col md:pb-0">
                @foreach ($nav as [$route, $label, $pattern])
                    <a href="{{ route($route) }}"
                       class="whitespace-nowrap rounded-lg px-3 py-2 text-sm {{ request()->routeIs($pattern) ? 'bg-espresso-700 text-white' : 'hover:bg-espresso-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="flex-1">
            <header class="flex items-center justify-between border-b border-espresso-100 bg-white px-6 py-3">
                <h1 class="text-lg font-semibold">@yield('title')</h1>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-espresso-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-ghost py-1.5">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="p-6">
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
