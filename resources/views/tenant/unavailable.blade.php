<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }}</title>
    <x-kafehub-icons />
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-6 font-sans text-gray-900 antialiased">
    <div class="max-w-sm text-center">
        <img src="{{ asset('images/kafehub/mark.png') }}" alt="KafeHub.id" class="mx-auto h-14 w-auto">
        <h1 class="mt-4 text-xl font-extrabold tracking-tight">{{ $title }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ $text }}</p>
        <a href="https://{{ config('kafehub.domain') }}" class="mt-6 inline-block text-sm font-semibold text-brand-600 hover:underline">Kenali KafeHub.id →</a>
    </div>
</body>
</html>
