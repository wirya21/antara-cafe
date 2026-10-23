@extends('layouts.admin')

@section('title', 'Transaksi')

@section('content')
    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div>
            <label class="label">Dari</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="input">
        </div>
        <div>
            <label class="label">Sampai</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="input">
        </div>
        <div>
            <label class="label">Status</label>
            <select name="status" class="input">
                <option value="">Semua</option>
                @foreach (\App\Models\Order::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Sumber</label>
            <select name="source" class="input">
                <option value="">Semua</option>
                <option value="self_order" @selected(request('source') === 'self_order')>Pelanggan (QR)</option>
                <option value="kasir" @selected(request('source') === 'kasir')>Kasir</option>
            </select>
        </div>
        <div>
            <label class="label">Pembayaran</label>
            <select name="payment" class="input">
                <option value="">Semua</option>
                @foreach (\App\Models\Order::PAYMENT_METHODS as $value => $label)
                    <option value="{{ $value }}" @selected(request('payment') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Cari</label>
            <input name="q" value="{{ request('q') }}" placeholder="Kode / nama" class="input w-40">
        </div>
        <button class="btn-primary">Tampilkan</button>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn-ghost">⬇ Excel (CSV)</a>
    </form>

    <div class="mb-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-4"><p class="text-sm text-espresso-600">Transaksi dibayar</p><p class="mt-1 text-xl font-semibold">{{ $summary['count'] }}</p></div>
        <div class="card p-4"><p class="text-sm text-espresso-600">Pendapatan</p><p class="mt-1 text-xl font-semibold"><x-rupiah :value="$summary['revenue']" /></p></div>
        <div class="card p-4"><p class="text-sm text-espresso-600">Menunggu kasir</p><p class="mt-1 text-xl font-semibold">{{ $summary['pending'] }}</p></div>
        <div class="card p-4"><p class="text-sm text-espresso-600">Dibatalkan</p><p class="mt-1 text-xl font-semibold">{{ $summary['cancelled'] }}</p></div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Kode</th>
                    <th class="px-4 py-3 font-medium">Waktu</th>
                    <th class="px-4 py-3 font-medium">Pelanggan</th>
                    <th class="px-4 py-3 font-medium">Sumber</th>
                    <th class="px-4 py-3 font-medium">Item</th>
                    <th class="px-4 py-3 text-right font-medium">Total</th>
                    <th class="px-4 py-3 font-medium">Bayar</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-espresso-50">
                @forelse ($orders as $order)
                    <tr class="hover:bg-espresso-50/60">
                        <td class="px-4 py-2.5"><a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-semibold hover:underline">{{ $order->code }}</a></td>
                        <td class="px-4 py-2.5 whitespace-nowrap">{{ $order->created_at->format('d/m H:i') }}</td>
                        <td class="px-4 py-2.5">{{ $order->customer_name ?: '-' }}@if ($order->table_number) <span class="text-espresso-500">· Meja {{ $order->table_number }}</span>@endif</td>
                        <td class="px-4 py-2.5">{{ $order->source === 'self_order' ? 'Pelanggan' : 'Kasir' }}</td>
                        <td class="px-4 py-2.5">{{ $order->items_count }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold tabular-nums"><x-rupiah :value="$order->total" /></td>
                        <td class="px-4 py-2.5">{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? '-' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ ['pending' => 'bg-amber-100 text-amber-800', 'paid' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-gray-100 text-gray-600'][$order->status] ?? '' }}">
                                {{ \App\Models\Order::STATUSES[$order->status] ?? $order->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-espresso-600">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
