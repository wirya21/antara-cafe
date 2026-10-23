<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti password · Antara Cafe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-style />
</head>
<body class="flex min-h-screen items-center justify-center bg-espresso-50 px-4 font-sans text-espresso-900 antialiased">
    <form method="POST" action="{{ route('password.update') }}" class="card w-full max-w-sm space-y-4 p-6">
        @csrf
        @method('PUT')
        <div>
            <h1 class="font-display text-xl font-semibold">Ganti password</h1>
            <p class="text-sm text-espresso-600">{{ auth()->user()->name }} ({{ auth()->user()->username }})</p>
        </div>
        <div>
            <label for="current_password" class="label">Password lama</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="input">
            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">Password baru (min. 8 karakter)</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="input">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Ulangi password baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
        </div>
        <div class="flex gap-2">
            <button class="btn-primary flex-1">Simpan</button>
            <a href="{{ auth()->user()->homeRoute() }}" class="btn-ghost">Batal</a>
        </div>
    </form>
</body>
</html>
