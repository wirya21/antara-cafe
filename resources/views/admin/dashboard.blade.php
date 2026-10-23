@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $rp = fn ($v) => 'Rp'.number_format($v, 0, ',', '.');
        $weekBars = $week->values()->map(fn ($d) => [
            'label' => $d['date']->translatedFormat('D'),
            'value' => $d['revenue'],
            'tooltip' => $d['date']->translatedFormat('D, j M').' · '.$rp($d['revenue']).' · '.$d['count'].' trx',
        ])->all();
    @endphp

    @if ($pendingCount)
        <a href="{{ route('cashier.index') }}" class="mb-4 flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
            <span>🧾 <b>{{ $pendingCount }}</b> pesanan pelanggan menunggu dibayar di kasir.</span>
            <span class="font-semibold">Buka kasir →</span>
        </a>
    @endif

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Pendapatan hari ini</p>
            <p class="mt-2 text-2xl font-semibold">{{ $rp($today['net']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Laba kotor hari ini</p>
            <p class="mt-2 text-2xl font-semibold">{{ $rp($today['profit']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Transaksi hari ini</p>
            <p class="mt-2 text-2xl font-semibold">{{ $today['count'] }}</p>
            <p class="text-xs text-espresso-500">rata-rata {{ $rp($today['average']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Promo aktif</p>
            <p class="mt-2 text-2xl font-semibold">{{ $activePromotionCount }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Pendapatan 7 hari terakhir</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-sm text-espresso-700 hover:underline">Laporan lengkap →</a>
            </div>
            <x-bar-chart class="mt-6" :bars="$weekBars" />
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Stok menipis</h2>
                <a href="{{ route('admin.ingredients.index') }}" class="text-sm text-espresso-700 hover:underline">Stok →</a>
            </div>
            <ul class="mt-3 divide-y divide-espresso-50 text-sm">
                @forelse ($lowStock as $ingredient)
                    <li class="flex items-center justify-between py-2">
                        <a href="{{ route('admin.ingredients.show', $ingredient) }}" class="hover:underline">{{ $ingredient->name }}</a>
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $ingredient->stock <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">
                            <x-qty :value="$ingredient->stock" /> {{ $ingredient->unit }}
                        </span>
                    </li>
                @empty
                    <li class="py-6 text-center text-espresso-500">✅ Semua stok aman.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="card mt-6 p-6">
        <h2 class="font-semibold">Terlaris hari ini</h2>
        <ul class="mt-3 divide-y divide-espresso-50 text-sm">
            @forelse ($topToday as $product)
                <li class="flex justify-between py-2"><span>{{ $product['name'] }}</span><span class="tabular-nums">{{ $product['quantity'] }} terjual · {{ $rp($product['revenue']) }}</span></li>
            @empty
                <li class="py-4 text-espresso-500">Belum ada penjualan hari ini.</li>
            @endforelse
        </ul>
    </div>
@endsection
