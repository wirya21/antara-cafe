@extends('layouts.admin')

@section('title', 'Transaksi '.$order->code)

@section('content')
    @php $cost = $order->items->sum(fn ($i) => $i->cost * $i->quantity); @endphp
    <div class="grid max-w-5xl gap-6 lg:grid-cols-[1fr_320px]">
        <div class="card p-4 sm:p-6">
            <table class="w-full text-sm">
                <thead class="border-b border-espresso-100 text-left text-espresso-600">
                    <tr>
                        <th class="pb-2 font-medium">Menu</th>
                        <th class="pb-2 text-right font-medium">Qty</th>
                        <th class="pb-2 text-right font-medium">Harga</th>
                        <th class="pb-2 text-right font-medium">HPP</th>
                        <th class="pb-2 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-espresso-50">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="py-2">{{ $item->product_name }} @if ($item->note)<span class="block text-xs text-espresso-500">{{ $item->note }}</span>@endif</td>
                            <td class="py-2 text-right">{{ $item->quantity }}</td>
                            <td class="py-2 text-right tabular-nums"><x-rupiah :value="$item->price" /></td>
                            <td class="py-2 text-right text-espresso-500 tabular-nums"><x-rupiah :value="$item->cost" /></td>
                            <td class="py-2 text-right font-semibold tabular-nums"><x-rupiah :value="$item->subtotal" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-espresso-100">
                    <tr><td colspan="4" class="pt-3 text-right text-espresso-600">Subtotal</td><td class="pt-3 text-right tabular-nums"><x-rupiah :value="$order->subtotal" /></td></tr>
                    @if ($order->discount)
                        <tr><td colspan="4" class="text-right text-green-700">Diskon {{ $order->promotion?->title }}</td><td class="text-right text-green-700 tabular-nums">−<x-rupiah :value="$order->discount" /></td></tr>
                    @endif
                    <tr class="text-lg font-semibold"><td colspan="4" class="pt-1 text-right">Total</td><td class="pt-1 text-right tabular-nums"><x-rupiah :value="$order->total" /></td></tr>
                    @if ($order->status === 'paid')
                        <tr class="text-espresso-600"><td colspan="4" class="text-right">HPP / laba kotor</td><td class="text-right tabular-nums"><x-rupiah :value="$cost" /> / <x-rupiah :value="$order->total - $cost" /></td></tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <div class="space-y-4">
            <div class="card space-y-2 p-5 text-sm">
                @php $stage = $order->stage(); @endphp
                <p><span class="text-espresso-600">Status:</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ ['pending' => 'bg-amber-100 text-amber-800', 'processing' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-gray-100 text-gray-600'][$stage] }}">{{ \App\Models\Order::STAGES[$stage] }}</span></p>
                @if ($order->status === 'pending' && $order->payment_method)
                    <p><span class="text-espresso-600">Pilihan bayar pelanggan:</span> <b>{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</b></p>
                @endif
                <p><span class="text-espresso-600">Dibuat:</span> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p><span class="text-espresso-600">Sumber:</span> {{ $order->source === 'self_order' ? 'Pelanggan (QR)' : 'Kasir' }}</p>
                <p><span class="text-espresso-600">Pelanggan:</span> {{ $order->customer_name ?: '-' }} @if ($order->table_number)· Meja {{ $order->table_number }}@endif</p>
                @if ($order->member)<p><span class="text-espresso-600">Member:</span> {{ $order->member->name }} ({{ $order->member->phone }})</p>@endif
                @if ($order->paid_at)
                    <p><span class="text-espresso-600">Dibayar:</span> {{ $order->paid_at->format('d/m/Y H:i') }} · {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</p>
                @endif
                <p><span class="text-espresso-600">Kasir:</span> {{ $order->cashier?->name ?? '-' }}</p>
                @if ($order->voided_at)
                    <p class="rounded-lg bg-red-50 p-2 text-red-800">Di-void {{ $order->voided_at->format('d/m/Y H:i') }} oleh {{ $voidedBy?->name }}: {{ $order->void_reason }}</p>
                @endif
            </div>

            @if ($order->payment_proof_path)
                <div class="card space-y-2 p-5">
                    <p class="text-sm font-semibold">Bukti pembayaran dari pelanggan</p>
                    <a href="{{ route('cashier.proof', $order) }}" target="_blank" class="block overflow-hidden rounded-lg ring-1 ring-espresso-100">
                        <img src="{{ route('cashier.proof', $order) }}" alt="Bukti pembayaran {{ $order->code }}" class="max-h-80 w-full object-contain bg-espresso-50">
                    </a>
                    <p class="text-xs text-espresso-600">Cocokkan nominal <b><x-rupiah :value="$order->total" /></b> dengan mutasi QRIS/rekening sebelum konfirmasi.</p>
                </div>
            @endif

            @if ($order->status === 'pending')
                <div class="card space-y-3 border-amber-200 bg-amber-50/60 p-5">
                    <p class="text-sm font-semibold">Pesanan ini belum dibayar</p>
                    <p class="text-xs text-espresso-600">Pesanan baru diteruskan ke dapur setelah pembayaran dikonfirmasi. Tunai hanya diterima langsung di kasir.</p>
                    <form method="POST" action="{{ route('cashier.pay', $order) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="kembali" value="admin">
                        <select name="payment_method" class="input">
                            @foreach (\App\Models\Order::PAYMENT_METHODS as $value => $label)
                                <option value="{{ $value }}" @selected($order->payment_method === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn-primary w-full py-2.5">✓ Konfirmasi pembayaran</button>
                    </form>
                    <form method="POST" action="{{ route('cashier.cancel', $order) }}" onsubmit="return confirm('Tolak dan batalkan pesanan ini?')">
                        @csrf
                        <input type="hidden" name="kembali" value="admin">
                        <button class="btn-danger w-full">Tolak / batalkan pesanan</button>
                    </form>
                </div>
            @endif

            @if ($order->stage() === 'processing')
                <form method="POST" action="{{ route('cashier.complete', $order) }}" class="card space-y-2 p-5">
                    @csrf
                        <input type="hidden" name="kembali" value="admin">
                    <p class="text-sm font-semibold">Sedang dibuat</p>
                    <button class="btn-primary w-full py-2.5">Tandai selesai / sudah diserahkan</button>
                </form>
            @endif

            @if ($order->status === 'paid')
                <a href="{{ route('cashier.receipt', $order) }}" target="_blank" class="btn-ghost w-full">🖨 Cetak ulang struk</a>
                <form method="POST" action="{{ route('admin.orders.void', $order) }}" class="card space-y-2 p-5" onsubmit="return confirm('Batalkan transaksi ini? Stok bahan akan dikembalikan.')">
                    @csrf
                    <p class="text-sm font-semibold">Batalkan transaksi (void)</p>
                    <input name="reason" required maxlength="150" class="input" placeholder="Alasan, mis. salah input">
                    @error('reason') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <button class="btn w-full bg-red-600 text-white hover:bg-red-700">Void transaksi</button>
                </form>
            @endif
            <a href="{{ route('admin.orders.index') }}" class="btn-ghost w-full">← Semua transaksi</a>
        </div>
    </div>
@endsection
