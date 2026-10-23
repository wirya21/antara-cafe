@extends('layouts.admin')

@section('title', 'Stok bahan')

@section('content')
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Jumlah bahan</p>
            <p class="mt-1 text-2xl font-semibold">{{ $ingredients->count() }}</p>
        </div>
        <a href="{{ route('admin.ingredients.index', ['status' => 'menipis']) }}" class="card p-5 {{ $lowCount ? 'ring-2 ring-amber-300' : '' }}">
            <p class="text-sm text-espresso-600">Stok menipis / habis</p>
            <p class="mt-1 text-2xl font-semibold {{ $lowCount ? 'text-amber-700' : '' }}">{{ $lowCount }}</p>
        </a>
        <div class="card p-5">
            <p class="text-sm text-espresso-600">Nilai stok (perkiraan)</p>
            <p class="mt-1 text-2xl font-semibold"><x-rupiah :value="$stockValue" /></p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari bahan…" class="input w-48">
            @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <button class="btn-ghost">Cari</button>
            @if (request()->hasAny(['q', 'status']))<a href="{{ route('admin.ingredients.index') }}" class="btn-ghost">Semua</a>@endif
        </form>
        <a href="{{ route('admin.ingredients.create') }}" class="btn-primary">+ Tambah bahan</a>
    </div>

    <details class="card mb-4 p-4 text-sm" open>
        <summary class="cursor-pointer font-semibold">Cara membaca tabel ini</summary>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-espresso-700">
            <li><b>Stok sekarang</b>: jumlah bahan yang ada. Berkurang otomatis setiap menu yang memakainya terjual.</li>
            <li><b>Batas peringatan</b>: bila stok sama atau di bawah angka ini, bahan ditandai <i>menipis</i> supaya Anda ingat belanja.</li>
            <li><b>Harga per satuan</b>: harga rata-rata untuk 1 satuan bahan (mis. per 1 gram atau per 1 ml), dihitung otomatis dari pembelian Anda. Dipakai untuk menghitung HPP menu.</li>
            <li><b>Dipakai di</b>: menu yang membutuhkan bahan ini. Lihat semuanya sekaligus di <a href="{{ route('admin.recipes.index') }}" class="font-semibold underline">Resep &amp; stok</a>.</li>
        </ul>
    </details>

    <div class="card overflow-x-auto" x-data="{ open: null }">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Bahan</th>
                    <th class="px-4 py-3 text-right font-medium">Stok sekarang</th>
                    <th class="px-4 py-3 text-right font-medium">Batas peringatan</th>
                    <th class="px-4 py-3 text-right font-medium">Harga per satuan</th>
                    <th class="px-4 py-3 font-medium">Dipakai di</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-espresso-50">
                @forelse ($ingredients as $ingredient)
                    @php $status = $ingredient->status(); @endphp
                    <tr class="{{ $status !== 'aman' ? 'bg-amber-50/50' : '' }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.ingredients.show', $ingredient) }}" class="font-medium hover:underline">{{ $ingredient->name }}</a>
                            @if ($status !== 'aman')
                                <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $status === 'habis' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums"><x-qty :value="$ingredient->stock" /> {{ $ingredient->unit }}</td>
                        <td class="px-4 py-3 text-right text-espresso-600 tabular-nums"><x-qty :value="$ingredient->min_stock" /> {{ $ingredient->unit }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">Rp{{ number_format($ingredient->cost_per_unit, $ingredient->cost_per_unit < 100 ? 2 : 0, ',', '.') }}<span class="text-espresso-500">/{{ $ingredient->unit }}</span></td>
                        <td class="px-4 py-3 text-espresso-600">{{ $ingredient->products->pluck('name')->join(', ') ?: '–' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <button type="button" @click="open = open === {{ $ingredient->id }} ? null : {{ $ingredient->id }}" class="btn-primary py-1">Catat stok</button>
                            <a href="{{ route('admin.ingredients.edit', $ingredient) }}" class="btn-ghost py-1">Ubah</a>
                        </td>
                    </tr>
                    <tr x-show="open === {{ $ingredient->id }}" x-cloak>
                        <td colspan="6" class="bg-espresso-50 px-4 py-4">
                            <x-stock-move-form :ingredient="$ingredient" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-espresso-600">Belum ada bahan. Tambahkan bahan seperti biji kopi, susu, gula, lalu pasang di resep produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
