@props(['cafe'])
@php [$label, $style] = \App\Models\Cafe::STATUSES[$cafe->status()]; @endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {$style}"]) }}>{{ $label }}</span>
