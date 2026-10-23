<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2b1b0e">
    @if ($order->status === 'pending')
        <meta http-equiv="refresh" content="20">
    @endif
    <title>Pesanan {{ $order->code }} · Antara Cafe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-espresso-900 bg-[radial-gradient(circle_at_top,var(--color-espresso-700),var(--color-espresso-900)_55%)] px-4 py-10 font-sans text-espresso-900 antialiased">
    <script>try { localStorage.removeItem('antara-cart'); } catch (e) {}</script>

    <div class="mx-auto max-w-md">
        <div class="text-center text-espresso-50">
            <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-espresso-50 text-4xl shadow-xl">
                {{ $order->status === 'paid' ? '✅' : ($order->status === 'cancelled' ? '✖️' : '🧾') }}
            </div>
            <h1 class="mt-5 font-display text-3xl font-semibold">
                @switch($order->status)
                    @case('paid') Terima kasih! @break
                    @case('cancelled') Pesanan dibatalkan @break
                    @default Pesanan terkirim!
                @endswitch
            </h1>
            <p class="mt-2 text-espresso-200">
                @if ($order->status === 'pending')
                    Tunjukkan kode ini ke kasir untuk membayar.
                @elseif ($order->status === 'paid')
                    Pembayaran diterima. Pesanan Anda sedang disiapkan.
                @else
                    Silakan hubungi kasir bila ada pertanyaan.
                @endif
            </p>
        </div>

        <div class="mt-8 overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="border-b border-dashed border-espresso-200 p-6 text-center">
                <p class="text-xs tracking-widest text-espresso-500 uppercase">Kode pesanan</p>
                <p class="mt-1 font-display text-4xl font-semibold tracking-wide">{{ $order->code }}</p>
                <p class="mt-2 text-sm text-espresso-600">
                    a.n. <b>{{ $order->customer_name }}</b>
                    @if ($order->table_number) · Meja <b>{{ $order->table_number }}</b> @else · Dibawa pulang @endif
                </p>
                <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-semibold
                    {{ ['pending' => 'bg-amber-100 text-amber-800', 'paid' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-gray-100 text-gray-600'][$order->status] }}">
                    {{ \App\Models\Order::STATUSES[$order->status] }}
                </span>
            </div>

            <ul class="space-y-3 p-6">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 text-sm">
                        <div>
                            <p><b>{{ $item->quantity }}×</b> {{ $item->product_name }}</p>
                            @if ($item->note)<p class="text-xs text-espresso-500">{{ $item->note }}</p>@endif
                        </div>
                        <x-rupiah :value="$item->subtotal" />
                    </li>
                @endforeach
                @if ($order->discount)
                    <li class="flex justify-between text-sm text-green-700"><span>Diskon</span><span>−<x-rupiah :value="$order->discount" /></span></li>
                @endif
                <li class="flex justify-between border-t border-espresso-100 pt-3 font-display text-xl font-semibold">
                    <span>Total</span><x-rupiah :value="$order->total" />
                </li>
            </ul>
        </div>

        <a href="{{ route('menu', array_filter(['meja' => $order->table_number])) }}" class="mt-6 block rounded-2xl border border-espresso-50/30 py-3.5 text-center font-semibold text-espresso-50 hover:bg-espresso-50/10">
            Pesan lagi
        </a>
    </div>
</body>
</html>
