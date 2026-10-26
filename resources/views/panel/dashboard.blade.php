@extends('layouts.panel')

@section('title', 'Ringkasan')

@section('actions')
    <a href="{{ route('panel.cafes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-brand-500/30 transition hover:bg-brand-600">+ Tambah kafe</a>
@endsection

@php
    $tiles = [
        ['Kafe aktif', $active, 'dari '.$total.' kafe terdaftar', 'toko'],
        ['Masa coba', $trial, 'kafe sedang mencoba', 'jam'],
        ['Langganan per bulan', 'Rp'.number_format($mrr, 0, ',', '.'), 'dari kafe aktif berbayar', 'dompet'],
        ['Pesanan hari ini', number_format($ordersToday, 0, ',', '.'), 'Rp'.number_format($revenue30, 0, ',', '.').' omzet 30 hari', 'struk'],
    ];
    $max = max(1, $chart->max('count'));
@endphp

@section('content')
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        @foreach ($tiles as [$label, $value, $hint, $icon])
            <div class="card p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                    <span class="hidden h-9 w-9 shrink-0 place-items-center sm:grid rounded-lg border border-brand-100 bg-brand-50/60 text-brand-600"><x-line-icon :name="$icon" class="h-5 w-5" /></span>
                </div>
                <p class="mt-3 text-xl font-extrabold tracking-tight tabular-nums sm:text-3xl">{{ $value }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Pesanan dibayar per hari, semua kafe --}}
        <section class="card p-5 lg:col-span-2">
            <div class="flex items-baseline justify-between">
                <h2 class="font-semibold">Pesanan dibayar, 14 hari terakhir</h2>
                <p class="text-xs text-gray-500">Semua kafe</p>
            </div>
            <div class="mt-6 flex h-48 items-end gap-1.5 border-b border-gray-200 sm:gap-2" role="img" aria-label="Grafik pesanan per hari">
                @foreach ($chart as $day)
                    <div class="group relative flex h-full flex-1 flex-col justify-end">
                        <span class="pointer-events-none absolute -top-1 left-1/2 z-10 hidden -translate-x-1/2 -translate-y-full rounded-md bg-gray-900 px-2 py-1 text-xs whitespace-nowrap text-white group-hover:block">
                            {{ $day['date']->translatedFormat('j M') }} · {{ $day['count'] }} pesanan
                        </span>
                        <div class="mx-auto w-full max-w-7 rounded-t {{ $day['date']->isToday() ? 'bg-brand-500' : 'bg-brand-300 group-hover:bg-brand-400' }}" style="height: {{ $day['count'] ? max(2, round($day['count'] / $max * 100)) : 0 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-1.5 text-center text-[11px] text-gray-400 sm:gap-2">
                @foreach ($chart as $day)
                    <span class="flex-1">{{ $loop->index % 2 === 1 || $loop->last ? $day['date']->format('j') : '' }}</span>
                @endforeach
            </div>
        </section>

        {{-- Perlu perhatian --}}
        <section class="card p-5">
            <h2 class="font-semibold">Perlu perhatian</h2>
            <p class="text-xs text-gray-500">Langganan atau masa coba yang segera habis.</p>
            <ul class="mt-4 divide-y divide-gray-100">
                @forelse ($attention as $cafe)
                    <li class="flex items-center justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <a href="{{ route('panel.cafes.edit', $cafe) }}" class="block truncate text-sm font-semibold hover:text-brand-600">{{ $cafe->name }}</a>
                            <p class="text-xs text-gray-500">
                                @if ($cafe->daysLeft() < 0) Berakhir {{ abs($cafe->daysLeft()) }} hari lalu
                                @elseif ($cafe->daysLeft() === 0) Berakhir hari ini
                                @else {{ $cafe->daysLeft() }} hari lagi @endif
                            </p>
                        </div>
                        <x-cafe-status :cafe="$cafe" />
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-500">Semua langganan aman.</li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Kafe dan omzetnya --}}
    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="font-semibold">Kafe, 30 hari terakhir</h2>
            <a href="{{ route('panel.cafes.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">Semua kafe →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-2.5">Kafe</th>
                        <th class="px-5 py-2.5">Status</th>
                        <th class="px-5 py-2.5 text-right">Pesanan</th>
                        <th class="px-5 py-2.5 text-right">Omzet</th>
                        <th class="px-5 py-2.5">Pesanan terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($top->take(8) as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('panel.cafes.edit', $row['cafe']) }}" class="font-semibold hover:text-brand-600">{{ $row['cafe']->name }}</a>
                                <p class="text-xs text-gray-500">{{ $row['cafe']->slug }}.{{ config('kafehub.domain') }}</p>
                            </td>
                            <td class="px-5 py-3"><x-cafe-status :cafe="$row['cafe']" /></td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ number_format($row['orders'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-semibold tabular-nums"><x-rupiah :value="$row['revenue']" /></td>
                            <td class="px-5 py-3 text-gray-500">{{ $row['last']?->diffForHumans() ?? 'Belum ada' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada kafe. <a href="{{ route('panel.cafes.create') }}" class="font-semibold text-brand-600">Tambah kafe pertama</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
