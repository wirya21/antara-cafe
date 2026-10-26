<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ $cafe?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-style />
</head>
<body class="flex min-h-screen items-center justify-center bg-espresso-900 bg-[radial-gradient(circle_at_top,var(--color-espresso-700),var(--color-espresso-900)_60%)] px-4 font-sans antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center text-espresso-50">
            <div class="text-4xl">☕</div>
            <h1 class="mt-2 text-2xl font-semibold">{{ $cafe?->name }}</h1>
            <p class="text-sm text-espresso-200">Masuk sebagai admin atau kasir</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="card space-y-4 p-6">
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

            <label class="flex items-center gap-2 text-sm text-espresso-700">
                <input type="checkbox" name="remember" class="rounded border-espresso-200"> Ingat saya
            </label>

            <button class="btn-primary w-full">Masuk</button>
        </form>
    </div>
</body>
</html>
