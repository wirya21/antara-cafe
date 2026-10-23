@props(['label' => 'Geser jika pesanan siap'])

{{-- Taruh di dalam <form>. Formulir dikirim saat knop digeser sampai ujung. --}}
<div x-data="swipeConfirm" x-ref="track" {{ $attributes->merge(['class' => 'relative h-14 w-full touch-none select-none overflow-hidden rounded-full bg-green-600 shadow-inner']) }}>
    <div class="absolute inset-y-0 left-0 rounded-full bg-green-700" :style="`width: ${x + 56}px`"></div>
    <span class="pointer-events-none absolute inset-0 flex items-center justify-center pl-12 text-sm font-semibold text-white transition-opacity"
          :style="`opacity: ${1 - progress * 1.4}`">{{ $label }} ➜</span>
    <span x-show="done" x-cloak class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm font-semibold text-white">Menyimpan…</span>
    <button type="button" x-ref="knob" aria-label="{{ $label }}"
            @pointerdown="start($event)" @pointermove="move($event)" @pointerup="end()" @pointercancel="end()"
            @keydown.enter.prevent="confirm()" @keydown.space.prevent="confirm()"
            class="absolute top-1 left-1 grid h-12 w-12 cursor-grab place-items-center rounded-full bg-white text-green-700 shadow-md active:cursor-grabbing"
            :class="dragging ? '' : 'transition-transform duration-200'"
            :style="`transform: translateX(${x}px)`">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
    </button>
</div>
