{{-- Watermark "SOLD" di atas foto menu yang stoknya habis. --}}
@props(['size' => 'text-sm'])
<span {{ $attributes->merge(['class' => 'pointer-events-none absolute inset-0 grid place-items-center bg-white/35']) }} aria-label="Habis">
    <span class="{{ $size }} -rotate-[18deg] rounded-md border-[3px] border-gray-800/80 bg-white/60 px-2 py-0.5 font-black tracking-[.25em] text-gray-800/90 shadow-sm backdrop-blur-[1px]">SOLD</span>
</span>
