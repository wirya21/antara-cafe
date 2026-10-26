<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layar Menu · {{ $cafe?->name }}</title>
    <x-kafehub-icons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-style />
</head>
<body class="h-screen overflow-hidden bg-espresso-900 font-sans text-espresso-50 antialiased">
<div x-data="displayApp(@js($slides), @js($menuUrl))" class="grid h-full grid-cols-[1.6fr_1fr] gap-[1.5vw] p-[1.5vw]">

    {{-- Sorotan: promo & menu unggulan bergantian --}}
    <section class="relative overflow-hidden rounded-[2vw] bg-espresso-800">
        <template x-for="(slide, i) in slides" :key="i">
            <div x-show="current === i" x-transition:enter="transition duration-1000" x-transition:enter-start="opacity-0 scale-105"
                 x-transition:leave="transition duration-700" x-transition:leave-end="opacity-0" class="absolute inset-0">
                <template x-if="slide.image">
                    <img :src="slide.image" alt="" class="h-full w-full object-cover">
                </template>
                <template x-if="!slide.image">
                    <div class="grid h-full w-full place-items-center bg-gradient-to-br" :class="placeholder(slide.group).bg">
                        <span class="text-[18vw] drop-shadow-2xl" x-text="placeholder(slide.group).emoji"></span>
                    </div>
                </template>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/50 to-transparent p-[3vw] pt-[8vw]">
                    <span class="rounded-full bg-amber-400 px-[1vw] py-[.3vw] text-[1vw] font-bold tracking-wide text-espresso-900 uppercase"
                          x-text="slide.type === 'promo' ? 'Promo' : 'Favorit'"></span>
                    <h2 class="mt-[1vw] font-display text-[4.5vw] leading-none font-semibold" x-text="slide.title"></h2>
                    <p class="mt-[.8vw] max-w-[70%] text-[1.4vw] text-white/80" x-text="slide.text"></p>
                    <p x-show="slide.price" class="mt-[1vw] font-display text-[3vw] font-semibold text-amber-300" x-text="rupiah(slide.price)"></p>
                </div>
            </div>
        </template>

        <template x-if="!slides.length">
            <div class="grid h-full place-items-center text-center">
                <div>
                    <div class="text-[10vw]">☕</div>
                    <p class="font-display text-[4vw] font-semibold">{{ $cafe?->name }}</p>
                </div>
            </div>
        </template>

        <div class="absolute top-[2vw] left-[2vw] flex items-center gap-[.8vw] rounded-full bg-black/30 px-[1.2vw] py-[.6vw] backdrop-blur">
            <span class="text-[1.6vw]">☕</span>
            <span class="font-display text-[1.6vw] font-semibold">{{ $cafe?->name }}</span>
        </div>
        <div class="absolute top-[2vw] right-[2vw] flex gap-[.5vw]">
            <template x-for="(slide, i) in slides" :key="'dot' + i">
                <span class="h-[.5vw] rounded-full bg-white transition-all" :class="current === i ? 'w-[2.5vw]' : 'w-[.5vw] opacity-40'"></span>
            </template>
        </div>
    </section>

    {{-- Papan menu --}}
    <section class="flex min-h-0 flex-col rounded-[2vw] bg-[#f7f1ea] p-[2vw] text-espresso-900">
        <div class="flex items-baseline justify-between">
            <h1 class="font-display text-[2.4vw] font-semibold">Menu</h1>
            <span class="text-[1.3vw] font-semibold text-espresso-600" x-text="clock"></span>
        </div>

        <div class="mt-[1vw] min-h-0 flex-1 columns-2 gap-[2vw] overflow-hidden">
            @foreach ($categories as $category)
                <div class="mb-[1.4vw] break-inside-avoid">
                    <h3 class="mb-[.4vw] border-b-2 border-espresso-800 pb-[.2vw] text-[1.1vw] font-bold tracking-wider text-espresso-700 uppercase">{{ $category->name }}</h3>
                    @foreach ($category->products as $product)
                        <div class="flex items-baseline gap-[.4vw] py-[.15vw] text-[1.1vw] {{ $product->isOrderable() ? '' : 'opacity-40' }}">
                            <span class="font-medium {{ $product->isOrderable() ? '' : 'line-through' }}">{{ $product->name }}</span>
                            @unless ($product->isOrderable())<span class="rounded bg-espresso-800 px-[.4vw] text-[.7vw] font-bold text-white no-underline">HABIS</span>@endunless
                            <span class="flex-1 border-b border-dotted border-espresso-200"></span>
                            <span class="font-semibold tabular-nums">{{ number_format($product->price / 1000, 0, ',', '.') }}k</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="mt-[1vw] flex items-center gap-[1.2vw] rounded-[1.4vw] bg-espresso-900 p-[1vw] text-espresso-50">
            <img x-show="qr" :src="qr" alt="QR menu" class="h-[7vw] w-[7vw] rounded-[.6vw] bg-white">
            <div>
                <p class="font-display text-[1.6vw] leading-tight font-semibold">Scan &amp; pesan dari meja</p>
                <p class="text-[1vw] text-espresso-200">Tanpa antre, tanpa login. Bayar di kasir.</p>
            </div>
        </div>
    </section>
</div>
</body>
</html>
