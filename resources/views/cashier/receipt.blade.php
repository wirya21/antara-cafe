<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $order->code }}</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        body { width: 54mm; margin: 0 auto; padding: 4mm 0; font: 11px/1.4 ui-monospace, monospace; color: #000; }
        .c { text-align: center; } .r { text-align: right; }
        hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; } td { vertical-align: top; }
        .big { font-size: 14px; font-weight: bold; }
        .qr { display: block; width: 30mm; height: 30mm; margin: 4px auto 0; }
    </style>
    @vite('resources/js/receipt.js')
</head>
<body data-track-url="{{ route('menu.order', $order) }}">
    <div class="c big">ANTARA CAFE</div>
    <div class="c">antara.kafehub.id</div>
    <hr>
    <div>No : {{ $order->code }}</div>
    <div>Tgl: {{ $order->paid_at->format('d/m/Y H:i') }}</div>
    @if ($order->customer_name)<div>Nama: {{ $order->customer_name }}</div>@endif
    @if ($order->table_number)<div>Meja: {{ $order->table_number }}</div>@endif
    <div>Kasir: {{ $order->cashier?->name }}</div>
    <hr>
    <table>
        @foreach ($order->items as $item)
            <tr><td colspan="2">{{ $item->product_name }}</td></tr>
            <tr><td>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</td><td class="r">{{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>
        @endforeach
    </table>
    <hr>
    <table>
        @if ($order->discount)
            <tr><td>Diskon</td><td class="r">-{{ number_format($order->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="big"><td>TOTAL</td><td class="r">{{ number_format($order->total, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar</td><td class="r">{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</td></tr>
    </table>
    @if ($order->member)
        <hr>
        <div>Member: {{ $order->member->name }}</div>
        <div>Poin: +{{ intdiv($order->total, \App\Models\Member::RUPIAH_PER_POINT) }} (total {{ $order->member->points }})</div>
    @endif
    <hr>
    <div class="c">Scan untuk cek status pesanan:</div>
    <img class="qr" id="track-qr" alt="">
    <hr>
    <div class="c">Terima kasih sudah mampir!</div>
</body>
</html>
