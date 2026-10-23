@extends('layouts.admin')

@section('title', 'Pesanan')

@php
    $hints = [
        'masuk' => 'Pesanan baru dari pelanggan yang belum dibayar. Cek bukti bayar atau terima tunai, lalu konfirmasi agar diteruskan ke dapur.',
        'diproses' => 'Sudah dibayar dan sedang dibuat. Geser tombol hijau saat pesanan siap, pelanggan langsung dapat notifikasi.',
        'selesai' => 'Pesanan yang sudah diserahkan hari ini.',
        'batal' => 'Pesanan yang ditolak atau dibatalkan hari ini.',
    ];
    $tabStyle = [
        'masuk' => 'bg-amber-400 text-espresso-900',
        'diproses' => 'bg-blue-600 text-white',
        'selesai' => 'bg-green-600 text-white',
        'batal' => 'bg-espresso-500 text-white',
    ];
@endphp

@section('content')
    <div x-data="orderBoard(@js(route('admin.board.counts')), @js($counts))" class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <p class="max-w-2xl text-sm text-espresso-600">
                Halaman ini hanya berisi pesanan yang sedang berjalan. Riwayat penjualan dan pendapatan ada di
                <a href="{{ route('admin.orders.index') }}" class="font-semibold text-espresso-800 underline">Transaksi</a>.
            </p>
            <a href="{{ route('cashier.index') }}" class="btn-ghost">+ Pesanan baru di kasir</a>
        </div>

        {{-- Tab tahap pesanan --}}
        <nav class="grid grid-cols-4 gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-espresso-100" aria-label="Tahap pesanan">
            @foreach (\App\Http\Controllers\Admin\OrderBoardController::TABS as $key => $label)
                <a href="{{ route('admin.board.index', ['tab' => $key]) }}" @if ($tab === $key) aria-current="page" @endif
                   class="flex flex-col items-center gap-0.5 rounded-lg px-2 py-2 text-center text-sm font-semibold transition sm:flex-row sm:justify-center sm:gap-2 {{ $tab === $key ? 'bg-espresso-900 text-white' : 'text-espresso-700 hover:bg-espresso-50' }}">
                    <span>{{ $label }}</span>
                    <span class="min-w-6 rounded-md px-1.5 text-xs leading-5 tabular-nums {{ $counts[$key] ? $tabStyle[$key] : 'bg-espresso-100 text-espresso-600' }}">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </nav>

        <p class="text-sm text-espresso-600">{{ $hints[$tab] }}</p>

        @if ($orders->isEmpty())
            <div class="card p-10 text-center text-espresso-500">
                <p class="text-3xl">☕</p>
                <p class="mt-2 text-sm">Belum ada pesanan di sini.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($orders as $order)
                    @php
                        $since = $order->status === 'paid' ? $order->paid_at : $order->created_at;
                        $eta = $tab === 'diproses' ? $order->estimatedMinutes() : null;
                    @endphp
                    <article class="card flex flex-col gap-3 p-4 {{ $tab === 'masuk' ? 'border-l-4 border-l-amber-400' : ($tab === 'diproses' ? 'border-l-4 border-l-blue-600' : '') }}">
                        <header class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-lg font-semibold hover:underline">{{ $order->code }}</a>
                                <p class="truncate text-sm text-espresso-700">
                                    {{ $order->customer_name ?: 'Tanpa nama' }}@if ($order->table_number) · Meja {{ $order->table_number }}@endif
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $order->source === 'self_order' ? 'bg-espresso-100 text-espresso-800' : 'bg-espresso-800 text-white' }}">
                                    {{ $order->source === 'self_order' ? 'HP pelanggan' : 'Kasir' }}
                                </span>
                                @if (in_array($tab, ['masuk', 'diproses']))
                                    <span class="rounded-md px-2 py-0.5 text-xs font-bold tabular-nums" :class="waitClass(@js($since->toIso8601String()), @js($eta))">
                                        ⏱ <span x-text="waited(@js($since->toIso8601String()))">{{ (int) $since->diffInMinutes(now()) }}</span>{{ $eta ? ' / '.$eta : '' }} mnt
                                    </span>
                                @elseif ($tab === 'selesai')
                                    <span class="text-xs text-espresso-500">Siap {{ $order->completed_at->format('H:i') }}</span>
                                @else
                                    <span class="text-xs text-espresso-500">{{ $order->updated_at->format('H:i') }}</span>
                                @endif
                            </div>
                        </header>

                        <ul class="space-y-1 border-y border-espresso-100 py-2 text-sm">
                            @foreach ($order->items as $item)
                                <li>
                                    <span class="font-semibold tabular-nums">{{ $item->quantity }}×</span> {{ $item->product_name }}
                                    @if ($item->note)<span class="block pl-6 text-xs text-amber-700">“{{ $item->note }}”</span>@endif
                                </li>
                            @endforeach
                        </ul>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-espresso-600">{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? 'Belum pilih' }}</span>
                            <span class="font-semibold tabular-nums"><x-rupiah :value="$order->total" /></span>
                        </div>

                        @if ($tab === 'masuk')
                            @if ($order->payment_proof_path)
                                <a href="{{ route('cashier.proof', $order) }}" target="_blank" class="flex items-center gap-3 rounded-lg bg-green-50 p-2 text-sm font-semibold text-green-800 ring-1 ring-green-200">
                                    <img src="{{ route('cashier.proof', $order) }}" alt="" loading="lazy" class="h-12 w-12 rounded-md object-cover">
                                    Bukti bayar terkirim · lihat ↗
                                </a>
                            @elseif ($order->needsProof())
                                <p class="rounded-lg bg-amber-50 p-2 text-xs text-amber-800">Menunggu pelanggan mengirim bukti {{ $order->payment_method === 'qris' ? 'QRIS' : 'transfer' }}.</p>
                            @endif

                            <form method="POST" action="{{ route('cashier.pay', $order) }}" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="kembali" value="pesanan">
                                <input type="hidden" name="tab" value="masuk">
                                <select name="payment_method" class="input min-w-0 flex-1 py-2" aria-label="Metode bayar">
                                    @foreach (\App\Models\Order::PAYMENT_METHODS as $value => $label)
                                        <option value="{{ $value }}" @selected($order->payment_method === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn-primary shrink-0">✓ Terima</button>
                            </form>
                            <form method="POST" action="{{ route('cashier.cancel', $order) }}" onsubmit="return confirm('Tolak dan batalkan pesanan {{ $order->code }}?')">
                                @csrf
                                <input type="hidden" name="kembali" value="pesanan">
                                <input type="hidden" name="tab" value="masuk">
                                <button class="w-full text-center text-xs font-semibold text-red-700 hover:underline">Tolak pesanan</button>
                            </form>
                        @elseif ($tab === 'diproses')
                            <form method="POST" action="{{ route('cashier.complete', $order) }}">
                                @csrf
                                <input type="hidden" name="kembali" value="pesanan">
                                <input type="hidden" name="tab" value="diproses">
                                <x-swipe-confirm label="Geser jika pesanan siap" />
                            </form>
                        @elseif ($tab === 'selesai')
                            <a href="{{ route('cashier.receipt', $order) }}" target="_blank" class="text-center text-xs font-semibold text-espresso-700 hover:underline">🖨 Cetak struk</a>
                        @elseif ($order->void_reason)
                            <p class="text-xs text-red-700">Alasan: {{ $order->void_reason }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
