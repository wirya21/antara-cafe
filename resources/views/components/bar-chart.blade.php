@props(['bars', 'height' => 'h-48', 'labelEvery' => 1])
{{-- $bars: list of ['label' => string, 'value' => int, 'tooltip' => string] — satu seri, satu warna. --}}
@php $max = max(1, collect($bars)->max('value')); @endphp
<div {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="pointer-events-none absolute inset-x-0 top-0 border-t border-dashed border-espresso-100"></div>
    <div class="pointer-events-none absolute inset-x-0 top-1/2 border-t border-dashed border-espresso-100"></div>
    <div class="flex {{ $height }} items-end gap-[2px] border-b border-espresso-200">
        @foreach ($bars as $bar)
            <div class="group relative flex h-full flex-1 items-end justify-center">
                <div class="w-full max-w-10 rounded-t-[4px] bg-espresso-600 transition-colors group-hover:bg-espresso-800"
                     style="height: {{ $bar['value'] > 0 ? max(2, round($bar['value'] / $max * 100, 2)) : 0 }}%"></div>
                <div class="pointer-events-none absolute bottom-full z-10 mb-1 hidden rounded-lg bg-espresso-900 px-2.5 py-1.5 text-xs whitespace-nowrap text-white shadow-lg group-hover:block">
                    {{ $bar['tooltip'] }}
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-1 flex gap-[2px] text-[10px] text-espresso-500">
        @foreach ($bars as $i => $bar)
            <div class="flex-1 text-center">{{ $i % $labelEvery === 0 ? $bar['label'] : '' }}</div>
        @endforeach
    </div>
</div>
