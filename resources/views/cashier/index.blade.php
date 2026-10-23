<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasir · Antara Cafe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-espresso-50 font-sans text-espresso-900 antialiased">
<div x-data="cashierApp(@js($menu), @js($pending), @js(route('cashier.pending')))" class="flex h-screen flex-col">

    <header class="flex items-center justify-between gap-4 bg-espresso-900 px-5 py-3 text-espresso-50">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-espresso-50 text-lg">☕</span>
            <div>
                <p class="font-display text-lg leading-none font-semibold">Antara Cafe</p>
                <p class="text-xs text-espresso-200">Kasir · {{ auth()->user()->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm">
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-1.5 hover:bg-espresso-800">Panel admin</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg border border-espresso-50/30 px-3 py-1.5 hover:bg-espresso-800">Keluar</button>
            </form>
        </div>
    </header>

    @if (session('status'))
        <div class="flex items-center justify-between bg-green-600 px-5 py-2 text-sm text-white">
            <span>{{ session('status') }}</span>
            @if (session('receipt'))
                <a href="{{ route('cashier.receipt', session('receipt')) }}" target="_blank" class="rounded-md bg-white/20 px-3 py-1 font-semibold hover:bg-white/30">🖨 Cetak struk</a>
            @endif
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-600 px-5 py-2 text-sm text-white">{{ $errors->first() }}</div>
    @endif

    <div class="flex min-h-0 flex-1">
        {{-- Produk --}}
        <section class="flex min-w-0 flex-1 flex-col">
            <div class="space-y-3 border-b border-espresso-100 bg-white px-5 py-3">
                <input x-model="search" type="search" placeholder="Cari menu…" class="input">
                <div class="no-scrollbar flex gap-2 overflow-x-auto">
                    <button type="button" @click="activeCategory = null" class="shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold"
                            :class="activeCategory === null ? 'bg-espresso-800 text-white' : 'bg-espresso-50 text-espresso-700'">Semua</button>
                    <template x-for="c in menu" :key="c.id">
                        <button type="button" @click="activeCategory = c.id" class="shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold"
                                :class="activeCategory === c.id ? 'bg-espresso-800 text-white' : 'bg-espresso-50 text-espresso-700'" x-text="c.name"></button>
                    </template>
                </div>
            </div>
            <div class="grid min-h-0 flex-1 auto-rows-min grid-cols-2 gap-3 overflow-y-auto p-5 md:grid-cols-3 xl:grid-cols-4">
                <template x-for="p in products" :key="p.id">
                    <button type="button" @click="add(p.id)" class="relative overflow-hidden rounded-2xl bg-white text-left shadow-sm ring-1 ring-espresso-100 transition hover:ring-espresso-500 active:scale-[.98]">
                        <div class="aspect-[4/3] bg-espresso-100">
                            <template x-if="p.image"><img :src="p.image" alt="" class="h-full w-full object-cover"></template>
                            <template x-if="!p.image"><div class="grid h-full place-items-center text-4xl" x-text="placeholder(p.group)"></div></template>
                        </div>
                        <div class="p-3">
                            <p class="truncate text-sm font-semibold" x-text="p.name"></p>
                            <p class="text-sm font-bold text-espresso-700" x-text="rupiah(p.price)"></p>
                        </div>
                        <span x-show="qtyOf(p.id)" x-text="qtyOf(p.id)" class="absolute top-2 right-2 grid h-7 min-w-7 place-items-center rounded-full bg-amber-400 px-2 text-sm font-bold"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- Panel kanan --}}
        <aside class="flex w-[380px] shrink-0 flex-col border-l border-espresso-100 bg-white xl:w-[420px]">
            <div class="grid grid-cols-2 border-b border-espresso-100 text-sm font-semibold">
                <button type="button" @click="tab = 'baru'" class="py-3" :class="tab === 'baru' ? 'border-b-2 border-espresso-800 text-espresso-900' : 'text-espresso-500'">Pesanan baru</button>
                <button type="button" @click="tab = 'masuk'; newArrivals = false" class="relative py-3" :class="tab === 'masuk' ? 'border-b-2 border-espresso-800 text-espresso-900' : 'text-espresso-500'">
                    Pesanan masuk
                    <span x-show="pending.length" x-text="pending.length" class="ml-1 rounded-full bg-amber-400 px-2 py-0.5 text-xs text-espresso-900" :class="newArrivals && 'animate-pulse'"></span>
                </button>
            </div>

            {{-- Pesanan baru (input kasir) --}}
            <form x-show="tab === 'baru'" method="POST" action="{{ route('cashier.store') }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <div class="min-h-0 flex-1 overflow-y-auto px-5">
                    <p x-show="!cart.length" class="py-16 text-center text-sm text-espresso-500">Ketuk menu di kiri untuk menambah pesanan.</p>
                    <ul class="divide-y divide-espresso-100">
                        <template x-for="(line, i) in cart" :key="line.id">
                            <li class="py-3">
                                <div class="flex items-center gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold" x-text="product(line.id).name"></p>
                                        <p class="text-sm text-espresso-600" x-text="rupiah(product(line.id).price * line.qty)"></p>
                                    </div>
                                    <button type="button" @click="change(i, -1)" class="grid h-8 w-8 place-items-center rounded-full bg-espresso-100 font-bold">−</button>
                                    <span class="w-5 text-center font-bold" x-text="line.qty"></span>
                                    <button type="button" @click="change(i, 1)" class="grid h-8 w-8 place-items-center rounded-full bg-espresso-800 font-bold text-white">+</button>
                                </div>
                                <input x-model="line.note" :name="`items[${i}][note]`" maxlength="120" placeholder="Catatan (opsional)" class="mt-2 w-full rounded-md border border-espresso-100 px-2 py-1 text-xs">
                                <input type="hidden" :name="`items[${i}][product_id]`" :value="line.id">
                                <input type="hidden" :name="`items[${i}][quantity]`" :value="line.qty">
                            </li>
                        </template>
                    </ul>
                </div>
                <div x-show="cart.length" class="space-y-3 border-t border-espresso-100 p-5">
                    <div class="grid grid-cols-2 gap-2">
                        <input name="customer_name" maxlength="50" placeholder="Nama (opsional)" class="input">
                        <input name="table_number" maxlength="10" placeholder="Meja" class="input">
                    </div>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach ($paymentMethods as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="{{ $value }}" class="peer sr-only" @checked($loop->first)>
                                <span class="block rounded-lg border border-espresso-200 py-2 text-center text-xs font-semibold peer-checked:border-espresso-800 peer-checked:bg-espresso-800 peer-checked:text-white">{{ \Illuminate\Support\Str::before($label, ' ') }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-espresso-600">Total</span>
                        <span class="font-display text-2xl font-semibold" x-text="rupiah(total)"></span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="cart = []" class="btn-ghost">Kosongkan</button>
                        <button class="btn-primary flex-1 py-3 text-base">Bayar</button>
                    </div>
                </div>
            </form>

            {{-- Pesanan masuk dari pelanggan --}}
            <div x-show="tab === 'masuk'" class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-espresso-50 p-4">
                <p x-show="!pending.length" class="py-16 text-center text-sm text-espresso-500">Belum ada pesanan dari pelanggan.</p>
                <template x-for="o in pending" :key="o.code">
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-espresso-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-display text-xl font-semibold" x-text="o.code"></p>
                                <p class="text-sm text-espresso-600">
                                    <span x-text="o.customer_name"></span> ·
                                    <span x-text="o.table_number ? 'Meja ' + o.table_number : 'Bawa pulang'"></span> ·
                                    <span x-text="o.time"></span>
                                </p>
                            </div>
                            <p class="font-bold" x-text="rupiah(o.total)"></p>
                        </div>
                        <ul class="mt-2 space-y-0.5 text-sm">
                            <template x-for="item in o.items">
                                <li><b x-text="item.quantity + '×'"></b> <span x-text="item.name"></span>
                                    <span x-show="item.note" class="text-xs text-espresso-500" x-text="'(' + item.note + ')'"></span></li>
                            </template>
                        </ul>
                        <form method="POST" :action="`{{ url('kasir/pesanan') }}/${o.code}/bayar`" class="mt-3 flex gap-2">
                            @csrf
                            <select name="payment_method" class="input py-1.5">
                                @foreach ($paymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn-primary">Bayar</button>
                        </form>
                        <form method="POST" :action="`{{ url('kasir/pesanan') }}/${o.code}/batal`" onsubmit="return confirm('Batalkan pesanan ini?')" class="mt-1 text-right">
                            @csrf
                            <button class="text-xs text-red-700 hover:underline">Batalkan</button>
                        </form>
                    </div>
                </template>
            </div>
        </aside>
    </div>
</div>
</body>
</html>
