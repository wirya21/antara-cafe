@extends('layouts.admin')

@section('title', 'Laporan keuangan')

@section('content')
    @php
        $rp = fn ($v) => 'Rp'.number_format($v, 0, ',', '.');
        $dailyBars = $daily->values()->map(fn ($d) => [
            'label' => $d['date']->format('j'),
            'value' => $d['revenue'],
            'tooltip' => $d['date']->translatedFormat('D, j M').' · '.$rp($d['revenue']).' · '.$d['count'].' trx',
        ])->all();
        $openHours = $hourly->filter(fn ($h) => $h['count'] > 0);
        $hourRange = $openHours->isEmpty() ? $hourly->slice(7, 16) : $hourly->whereBetween('hour', [min(7, $openHours->min('hour')), max(22, $openHours->max('hour'))]);
        $hourlyBars = $hourRange->values()->map(fn ($h) => [
            'label' => sprintf('%02d', $h['hour']),
            'value' => $h['count'],
            'tooltip' => sprintf('%02d.00–%02d.59', $h['hour'], $h['hour']).' · '.$h['count'].' trx · '.$rp($h['revenue']),
        ])->all();
        $margin = $totals['net'] ? round($totals['profit'] / $totals['net'] * 100) : 0;
    @endphp

    <div class="mb-6 flex flex-wrap items-end gap-3 print:hidden">
        <div class="flex flex-wrap gap-1 rounded-xl bg-white p-1 ring-1 ring-espresso-100">
            @foreach (\App\Http\Controllers\Admin\ReportController::PRESETS as $key => $label)
                <a href="{{ route('admin.reports.index', ['periode' => $key]) }}"
                   class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $preset === $key ? 'bg-espresso-800 text-white' : 'text-espresso-700 hover:bg-espresso-50' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" class="flex items-end gap-2">
            <input type="date" name="from" value="{{ $report->from->toDateString() }}" class="input">
            <span class="pb-2 text-espresso-500">–</span>
            <input type="date" name="to" value="{{ $report->to->toDateString() }}" class="input">
            <button class="btn-ghost">Terapkan</button>
        </form>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="btn-ghost">⬇ Excel (CSV)</a>
            <button type="button" onclick="window.print()" class="btn-ghost">🖨 Cetak / PDF</button>
        </div>
    </div>

    <p class="mb-3 text-sm text-espresso-600">
        Periode {{ $report->from->translatedFormat('j M Y') }} – {{ $report->to->translatedFormat('j M Y') }} · hanya transaksi yang sudah dibayar
    </p>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Pendapatan bersih</p>
            <p class="mt-1 text-2xl font-semibold">{{ $rp($totals['net']) }}</p>
            @if ($totals['discount'])<p class="mt-1 text-xs text-espresso-500">Kotor {{ $rp($totals['gross']) }} · diskon {{ $rp($totals['discount']) }}</p>@endif
        </div>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Laba kotor</p>
            <p class="mt-1 text-2xl font-semibold">{{ $rp($totals['profit']) }}</p>
            <p class="mt-1 text-xs text-espresso-500">HPP {{ $rp($totals['cost']) }} · margin {{ $margin }}%</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Transaksi</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format($totals['count'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-espresso-500">{{ number_format($totals['items'], 0, ',', '.') }} item terjual</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Rata-rata per transaksi</p>
            <p class="mt-1 text-2xl font-semibold">{{ $rp($totals['average']) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
        <div class="card p-6">
            <h2 class="font-semibold">Pendapatan per hari</h2>
            <x-bar-chart class="mt-6" :bars="$dailyBars" :label-every="count($dailyBars) > 16 ? 3 : 1" />
        </div>
        <div class="card p-6">
            <h2 class="font-semibold">Jam ramai</h2>
            <p class="text-xs text-espresso-500">Jumlah transaksi per jam</p>
            <x-bar-chart class="mt-6" :bars="$hourlyBars" height="h-40" :label-every="2" />
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach ([['Metode pembayaran', $payments, 'trx'], ['Per kategori', $categories, 'item']] as [$title, $rows, $unit])
            <div class="card p-6">
                <h2 class="font-semibold">{{ $title }}</h2>
                @php $rowMax = max(1, $rows->max('revenue')); @endphp
                <div class="mt-4 space-y-3">
                    @forelse ($rows as $row)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium">{{ $row['label'] }}</span>
                                <span class="tabular-nums">{{ $rp($row['revenue']) }} <span class="text-espresso-500">· {{ $row['count'] }} {{ $unit }}</span></span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-espresso-50">
                                <div class="h-2 rounded-full bg-espresso-600" style="width: {{ round($row['revenue'] / $rowMax * 100, 1) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-espresso-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mt-6 overflow-x-auto p-6">
        <h2 class="font-semibold">Produk terlaris</h2>
        <table class="mt-4 w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="py-2 font-medium">#</th>
                    <th class="py-2 font-medium">Produk</th>
                    <th class="py-2 text-right font-medium">Terjual</th>
                    <th class="py-2 text-right font-medium">Pendapatan</th>
                    <th class="py-2 text-right font-medium">Laba kotor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-espresso-50">
                @forelse ($topProducts as $i => $product)
                    <tr>
                        <td class="py-2 text-espresso-500">{{ $i + 1 }}</td>
                        <td class="py-2"><span class="font-medium">{{ $product['name'] }}</span> <span class="text-xs text-espresso-500">{{ $product['category'] }}</span></td>
                        <td class="py-2 text-right tabular-nums">{{ $product['quantity'] }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $rp($product['revenue']) }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $rp($product['profit']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-espresso-500">Belum ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-4 text-xs text-espresso-500">HPP dihitung dari resep produk dan harga rata-rata bahan saat transaksi dibayar. Produk tanpa resep dianggap HPP Rp0.</p>
@endsection
