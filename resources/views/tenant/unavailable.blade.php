<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-6 font-sans text-gray-900 antialiased">
    <div class="max-w-sm text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-600">
            <x-line-icon name="toko" class="h-7 w-7" />
        </span>
        <h1 class="mt-4 text-xl font-extrabold tracking-tight">{{ $title }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ $text }}</p>
        <a href="https://{{ config('kafehub.domain') }}" class="mt-6 inline-block text-sm font-semibold text-brand-600 hover:underline">Kenali kafehub →</a>
    </div>
</body>
</html>
