<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk panel · KafeHub.id</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-panel-style />
    <x-kafehub-icons />
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 bg-[radial-gradient(circle_at_top,#ffe9dc,transparent_55%)] px-4 font-sans text-gray-900 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <h1><img src="{{ asset('images/kafehub/logo.png') }}" alt="KafeHub.id" class="mx-auto h-16 w-auto" width="304" height="160"></h1>
            <p class="mt-3 text-sm text-gray-500">Panel pengelola kafehub.id</p>
        </div>

        <form method="POST" action="{{ route('panel.login') }}" class="card space-y-4 p-6">
            @csrf

            <div>
                <label for="username" class="label">Username</label>
                <input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" autocapitalize="none" class="input">
                @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="input">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300"> Ingat saya
            </label>

            <button class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-brand-500/30 transition hover:bg-brand-600">Masuk</button>
        </form>
    </div>
</body>
</html>
