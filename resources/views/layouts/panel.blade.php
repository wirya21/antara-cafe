<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel') · kafehub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-panel-style />
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    @php
        $nav = [
            ['panel.dashboard', 'Ringkasan', 'grafik', 'panel.dashboard'],
            ['panel.cafes.index', 'Kafe', 'toko', 'panel.cafes.*'],
            ['panel.landing.edit', 'Halaman kafehub.id', 'halaman', 'panel.landing.*'],
            ['panel.account', 'Akun', 'kunci', 'panel.account'],
        ];
    @endphp
    <div class="lg:flex">
        {{-- Samping (layar lebar) --}}
        <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
            <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2 px-6 py-5 text-lg font-extrabold tracking-tight">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500 text-sm shadow-md shadow-brand-500/30">☕</span>
                <span>kafe<span class="text-brand-500">hub</span></span>
                <span class="ml-1 rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-500">Superadmin</span>
            </a>
            <nav class="flex-1 space-y-1 px-3">
                @foreach ($nav as [$route, $label, $icon, $pattern])
                    <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($pattern) ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-line-icon :name="$icon" class="h-5 w-5" />
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-gray-100 p-4">
                <p class="truncate text-sm font-semibold">{{ auth('kafehub')->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ auth('kafehub')->user()->username }}</p>
                <form method="POST" action="{{ route('panel.logout') }}" class="mt-3">
                    @csrf
                    <button class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            {{-- Atas (HP dan tablet) --}}
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur lg:hidden">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500 text-sm">☕</span>
                        <span>kafe<span class="text-brand-500">hub</span></span>
                    </a>
                    <form method="POST" action="{{ route('panel.logout') }}">
                        @csrf
                        <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700">Keluar</button>
                    </form>
                </div>
                <nav class="flex gap-1 overflow-x-auto px-3 pb-2">
                    @foreach ($nav as [$route, $label, $icon, $pattern])
                        <a href="{{ route($route) }}" class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-brand-50 text-brand-700' : 'text-gray-600' }}">
                            <x-line-icon :name="$icon" class="h-4 w-4" /> {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-6 sm:px-8 sm:py-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-extrabold tracking-tight">@yield('title')</h1>
                    @yield('actions')
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-600/10">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
