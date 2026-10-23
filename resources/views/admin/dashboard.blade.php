@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $stats = [
            ['Pendapatan hari ini', 'Rp'.number_format($revenueToday, 0, ',', '.')],
            ['Transaksi hari ini', $ordersToday],
            ['Produk', $productCount],
            ['Kategori', $categoryCount],
            ['Promo aktif', $activePromotionCount],
        ];
    @endphp
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach ($stats as [$label, $value])
            <div class="card p-5">
                <p class="text-sm text-espresso-600">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="card mt-6 p-6">
        <h2 class="font-semibold">Mulai dari sini</h2>
        <p class="mt-1 text-sm text-espresso-600">Tambahkan kategori menu, lalu isi produk beserta fotonya. Layar menu, pemesanan, dan kasir akan memakai data ini.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.categories.create') }}" class="btn-ghost">+ Kategori</a>
            <a href="{{ route('admin.products.create') }}" class="btn-primary">+ Produk</a>
        </div>
    </div>
@endsection
