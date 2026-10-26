<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>Pesanan {{ $order->code }} · {{ $cafe?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $stage = $order->stage();
    $method = $order->payment_method;
@endphp
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <script>try { localStorage.removeItem('antara-cart'); } catch (e) {}</script>

    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-md items-center gap-3 px-4 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3">
            <a href="{{ route('menu', array_filter(['meja' => $order->table_number])) }}" class="grid h-10 w-10 place-items-center rounded-full border border-gray-200" aria-label="Kembali ke menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
            <div class="min-w-0">
                <p class="text-lg leading-tight font-bold">Status pesanan</p>
                <p class="text-xs text-gray-500">{{ $cafe?->name }} · {{ $order->code }}</p>
            </div>
        </div>
    </header>

    {{-- Cek status berkala; bunyi, getar, dan notifikasi saat pembayaran dikonfirmasi atau pesanan siap. --}}
    <div class="mx-auto max-w-md px-4 pt-5 pb-10"
         x-data="orderTracker(@js([
             'code' => $order->code,
             'stage' => $stage,
             'hasProof' => filled($order->payment_proof_path),
             'since' => ($order->paid_at ?? $order->created_at)->toIso8601String(),
             'statusUrl' => route('menu.order.status', $order),
         ]))">

        {{-- Kabar terbaru muncul di atas sebelum halaman dimuat ulang. --}}
        <div x-show="flash" x-cloak x-transition class="fixed inset-x-4 top-[max(1rem,env(safe-area-inset-top))] z-50 mx-auto max-w-md rounded-2xl bg-green-600 p-4 text-center text-white shadow-2xl">
            <p class="text-xl font-extrabold" x-text="flash?.title"></p>
            <p class="text-sm" x-text="flash?.body"></p>
        </div>

        <div class="rounded-3xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-100">
            <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-brand-50 text-4xl ring-8 ring-brand-50/50">
                {{ ['pending' => '🧾', 'processing' => '👨‍🍳', 'completed' => '✅', 'cancelled' => '✖️'][$stage] }}
            </div>
            <h1 class="mt-4 text-2xl font-extrabold">
                {{ ['pending' => 'Selesaikan pembayaran', 'processing' => 'Pesanan sedang dibuat', 'completed' => 'Pesanan siap! 🎉', 'cancelled' => 'Pesanan dibatalkan'][$stage] }}
            </h1>
            <p class="mx-auto mt-2 max-w-xs text-sm text-gray-500">
                @switch($stage)
                    @case('pending')
                        @if ($method === 'tunai')
                            Tunjukkan kode ini dan bayar tunai di kasir. Pesanan dibuat setelah dibayar.
                        @elseif ($order->payment_proof_path)
                            Bukti bayar sudah kami terima. Mohon tunggu, staf sedang mengecek.
                        @else
                            Bayar sesuai total di bawah, lalu unggah bukti pembayaran. Pesanan dibuat setelah dikonfirmasi.
                        @endif
                        @break
                    @case('processing') Pembayaran sudah diterima. Biarkan halaman ini terbuka, HP Anda akan berbunyi saat pesanan siap. @break
                    @case('completed')
                        @if ($order->table_number)
                            Pesanan untuk meja {{ $order->table_number }} sudah siap. Selamat menikmati!
                        @else
                            Silakan ambil di kasir dengan menyebut kode {{ $order->code }}. Terima kasih!
                        @endif
                        @break
                    @default Silakan hubungi kasir bila ada pertanyaan.
                @endswitch
            </p>

        @if (in_array($stage, ['pending', 'processing', 'completed']))
            @php $step = ['pending' => 0, 'processing' => 1, 'completed' => 2][$stage]; @endphp
            <ol class="mt-6 grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                @foreach (['Bayar', 'Dibuat', 'Siap'] as $i => $label)
                    <li class="space-y-1.5">
                        <div class="h-1.5 rounded-full {{ $i < $step || $step === 2 ? 'bg-green-500' : ($i === $step ? 'animate-pulse bg-brand-500' : 'bg-gray-200') }}"></div>
                        <span class="{{ $i <= $step ? 'text-gray-800' : 'text-gray-400' }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        @endif

        @if (in_array($stage, ['pending', 'processing']))
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-sm">
                <span class="rounded-full bg-gray-100 px-3 py-1.5 font-semibold text-gray-800 tabular-nums">
                    ⏱ <span x-text="waited">0</span> mnt {{ $stage === 'processing' ? 'sejak dibayar' : 'sejak memesan' }}
                </span>
                @if ($estimate)
                    <span class="rounded-full bg-brand-50 px-3 py-1.5 font-semibold text-brand-700">
                        @if ($stage === 'processing')
                            Perkiraan siap ± {{ $estimate }} mnt <span class="font-normal text-brand-600">(± {{ $order->paid_at->copy()->addMinutes($estimate)->format('H:i') }})</span>
                        @else
                            Dibuat ± {{ $estimate }} mnt setelah dibayar
                        @endif
                    </span>
                @endif
            </div>
            @if ($busyMessage)
                <p class="mx-auto mt-3 max-w-sm rounded-2xl bg-amber-50 px-4 py-2.5 text-center text-sm font-semibold text-amber-900">⏳ {{ $busyMessage }}</p>
            @endif
            <div class="mt-3 flex flex-wrap items-center justify-center gap-2 text-sm">
                <button type="button" x-show="permission === 'default'" @click="enableAlerts()"
                        class="rounded-full bg-brand-500 px-4 py-2 font-semibold text-white shadow-md shadow-brand-500/30">🔔 Kabari saya saat siap</button>
                <span x-show="permission === 'granted'" x-cloak class="rounded-full bg-green-50 px-3 py-1.5 font-semibold text-green-700">🔔 Notifikasi aktif</span>
            </div>
        @endif
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl bg-green-600 px-4 py-3 text-center text-sm font-semibold text-white">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-6 rounded-2xl bg-red-600 px-4 py-3 text-center text-sm font-semibold text-white">{{ $errors->first() }}</div>
        @endif

        {{-- Langkah pembayaran --}}
        @if ($stage === 'pending')
            <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <p class="text-xs tracking-widest text-gray-500 uppercase">Total bayar</p>
                        <p class="text-3xl font-extrabold"><x-rupiah :value="$order->total" /></p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ \App\Models\Order::SELF_PAYMENT_METHODS[$method] ?? 'Belum dipilih' }}</span>
                </div>

                <div class="space-y-4 p-5">
                    @if ($method === 'qris')
                        @if ($qrisUrl)
                            <div class="mx-auto max-w-[260px] overflow-hidden rounded-2xl bg-white p-2 ring-1 ring-gray-100">
                                <img src="{{ $qrisUrl }}" alt="QRIS {{ $cafe?->name }}" class="w-full">
                            </div>
                            <p class="text-center text-sm text-gray-500">Scan dengan aplikasi bank atau e-wallet, masukkan nominal <b><x-rupiah :value="$order->total" /></b>, lalu screenshot bukti bayarnya.</p>
                            <a href="{{ $qrisUrl }}" download class="block w-full rounded-xl border border-gray-200 py-2.5 text-center text-sm font-semibold text-gray-700">Simpan gambar QRIS</a>
                        @else
                            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900">QRIS belum tersedia. Silakan pilih cara bayar lain di bawah atau bayar di kasir.</p>
                        @endif
                    @elseif ($method === 'transfer')
                        @if (filled($bankAccounts))
                            <div class="space-y-2">
                                @foreach (preg_split('/\r\n|\r|\n/', trim($bankAccounts)) as $account)
                                    @continue(blank($account))
                                    <div x-data="{ copied: false }" class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                                        <span class="text-sm font-semibold">{{ $account }}</span>
                                        <button type="button" class="shrink-0 text-xs font-semibold text-gray-700 underline"
                                                @click="navigator.clipboard?.writeText(@js(preg_replace('/\D/', '', $account) ?: $account)).catch(() => {}); copied = true; setTimeout(() => copied = false, 1500)"
                                                x-text="copied ? 'Tersalin ✓' : 'Salin'">Salin</button>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-500">Transfer tepat <b><x-rupiah :value="$order->total" /></b>, lalu unggah bukti transfernya.</p>
                        @else
                            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900">Rekening transfer belum tersedia. Silakan pilih cara bayar lain di bawah atau bayar di kasir.</p>
                        @endif
                    @else
                        <div class="rounded-2xl bg-gray-50 p-4 text-center">
                            <p class="text-sm text-gray-700">Sebutkan kode pesanan ini di kasir:</p>
                            <p class="mt-1 text-4xl font-extrabold tracking-wide">{{ $order->code }}</p>
                        </div>
                    @endif

                    @if ($order->needsProof() && array_key_exists($method, $paymentMethods))
                        <form method="POST" action="{{ route('menu.order.proof', $order) }}" enctype="multipart/form-data"
                              x-data="{ name: '', sending: false }" @submit="sending = true" class="space-y-2">
                            @csrf
                            @if ($order->payment_proof_path)
                                <p class="rounded-xl bg-green-50 px-3 py-2 text-sm text-green-800">✓ Bukti bayar terkirim. Salah kirim? Unggah ulang di bawah.</p>
                            @endif
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-gray-300 px-4 py-4 text-sm font-semibold text-gray-700 transition hover:border-brand-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 16V4m0 0-4 4m4-4 4 4M4 20h16"/></svg>
                                <span x-text="name || 'Pilih foto / screenshot bukti bayar'"></span>
                                <input type="file" name="proof" accept="image/*" required class="sr-only" @change="name = $event.target.files[0]?.name || ''">
                            </label>
                            <button class="w-full rounded-2xl bg-brand-500 py-3.5 font-bold text-white shadow-lg shadow-brand-500/30 transition hover:bg-brand-600 disabled:opacity-50" :disabled="!name || sending">
                                <span x-text="sending ? 'Mengirim…' : 'Kirim bukti pembayaran'"></span>
                            </button>
                        </form>
                    @endif

                    <details class="text-sm">
                        <summary class="cursor-pointer text-center text-gray-500 underline">Ganti cara pembayaran</summary>
                        <form method="POST" action="{{ route('menu.order.method', $order) }}" class="mt-3 grid gap-2" style="grid-template-columns: repeat({{ count($paymentMethods) }}, minmax(0, 1fr))">
                            @csrf
                            @foreach ($paymentMethods as $value => $label)
                                <button name="payment_method" value="{{ $value }}" @disabled($value === $method)
                                        class="rounded-xl border border-gray-200 px-2 py-2 text-xs font-semibold disabled:border-brand-500 disabled:bg-brand-500 disabled:text-white">{{ $label }}</button>
                            @endforeach
                        </form>
                    </details>
                </div>
            </div>
        @endif

        <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-dashed border-gray-200 p-6 text-center">
                <p class="text-xs tracking-widest text-gray-500 uppercase">Kode pesanan</p>
                <p class="mt-1 text-4xl font-extrabold tracking-wide">{{ $order->code }}</p>
                <p class="mt-2 text-sm text-gray-500">
                    a.n. <b>{{ $order->customer_name }}</b>
                    @if ($order->table_number) · Meja <b>{{ $order->table_number }}</b> @else · Dibawa pulang @endif
                </p>
                <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-semibold
                    {{ ['pending' => 'bg-amber-100 text-amber-800', 'processing' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-gray-100 text-gray-600'][$stage] }}">
                    {{ \App\Models\Order::CUSTOMER_STAGES[$stage] }}
                </span>
            </div>

            <ul class="space-y-3 p-6">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 text-sm">
                        <div>
                            <p><b>{{ $item->quantity }}×</b> {{ $item->product_name }}</p>
                            @if ($item->note)<p class="text-xs text-gray-500">{{ $item->note }}</p>@endif
                        </div>
                        <x-rupiah :value="$item->subtotal" />
                    </li>
                @endforeach
                @if ($order->discount)
                    <li class="flex justify-between text-sm text-green-700"><span>Diskon</span><span>−<x-rupiah :value="$order->discount" /></span></li>
                @endif
                <li class="flex justify-between border-t border-gray-100 pt-3 text-xl font-extrabold">
                    <span>Total</span><x-rupiah :value="$order->total" />
                </li>
            </ul>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">Pesanan ini tersimpan di HP Anda. Buka lagi kapan saja lewat <a href="{{ route('menu.history') }}" class="font-semibold underline">Pesanan saya</a>.</p>

        <a href="{{ route('menu', array_filter(['meja' => $order->table_number])) }}" class="mt-4 block rounded-2xl bg-gray-900 py-3.5 text-center font-bold text-white hover:bg-gray-800">
            Pesan lagi
        </a>
    </div>
</body>
</html>
