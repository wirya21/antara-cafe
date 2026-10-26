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
    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-4xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <a href="{{ route('panel.edit') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500 text-sm shadow-md shadow-brand-500/30">☕</span>
                <span>kafe<span class="text-brand-500">hub</span></span>
                <span class="ml-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Panel</span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ url('/') }}" target="_blank" class="hidden rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-600 hover:bg-gray-100 sm:block">Lihat halaman ↗</a>
                <form method="POST" action="{{ route('panel.logout') }}">
                    @csrf
                    <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-6 sm:px-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
