@props(['href', 'dark' => false])

{{-- Lencana pesanan menunggu pembayaran + tombol aktifkan bunyi/notifikasi. --}}
<div x-data="orderAlerts(@js(route('cashier.alerts')), @js($href))" class="flex items-center gap-2">
    <a href="{{ $href }}"
       class="relative inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold transition {{ $dark ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-espresso-50 text-espresso-800 hover:bg-espresso-100' }}"
       :class="pending && '!bg-amber-400 !text-espresso-900'" title="Pesanan menunggu pembayaran">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0"/></svg>
        <span x-text="pending" x-show="pending" x-cloak></span>
        <span class="hidden sm:inline" x-text="pending ? 'menunggu bayar' : 'Pesanan'"></span>
        <span x-show="pending" x-cloak class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 animate-ping rounded-full bg-red-500"></span>
    </a>
    <button type="button" x-show="!soundOn" x-cloak @click="enable()"
            class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $dark ? 'bg-white/10 text-white hover:bg-white/20' : 'border border-espresso-200 text-espresso-700 hover:bg-espresso-50' }}">
        🔈<span class="hidden sm:inline"> Aktifkan bunyi</span>
    </button>
    <button type="button" x-show="soundOn" x-cloak @click="disable()" title="Matikan bunyi"
            class="rounded-full px-2 py-1.5 text-xs {{ $dark ? 'text-white/70 hover:text-white' : 'text-espresso-500 hover:text-espresso-800' }}">🔔</button>
</div>
