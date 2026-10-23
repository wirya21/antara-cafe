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

    <div class="card overflow-x-auto" x-data="{ open: null, type: 'purchase' }">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Bahan</th>
                    <th class="px-4 py-3 text-right font-medium">Stok</th>
                    <th class="px-4 py-3 text-right font-medium">Minimum</th>
                    <th class="px-4 py-3 text-right font-medium">Harga / satuan</th>
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
                        <td class="px-4 py-3 text-right text-espresso-600 tabular-nums"><x-qty :value="$ingredient->min_stock" /></td>
                        <td class="px-4 py-3 text-right tabular-nums">Rp{{ number_format($ingredient->cost_per_unit, $ingredient->cost_per_unit < 100 ? 2 : 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-espresso-600">{{ $ingredient->products_count }} menu</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <button type="button" @click="open = open === {{ $ingredient->id }} ? null : {{ $ingredient->id }}; type = 'purchase'" class="btn-primary py-1">Catat stok</button>
                            <a href="{{ route('admin.ingredients.edit', $ingredient) }}" class="btn-ghost py-1">Ubah</a>
                        </td>
                    </tr>
                    <tr x-show="open === {{ $ingredient->id }}" x-cloak>
                        <td colspan="6" class="bg-espresso-50 px-4 py-4">
                            <form method="POST" action="{{ route('admin.ingredients.move', $ingredient) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <div>
                                    <label class="label">Jenis</label>
                                    <select name="type" x-model="type" class="input">
                                        <option value="purchase">Stok masuk (beli)</option>
                                        <option value="adjustment">Opname (hitung fisik)</option>
                                        <option value="waste">Terbuang / rusak</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label" x-text="type === 'adjustment' ? 'Stok fisik sekarang ({{ $ingredient->unit }})' : 'Jumlah ({{ $ingredient->unit }})'"></label>
                                    <input type="number" step="0.01" min="0" name="quantity" required class="input w-40">
                                </div>
                                <div x-show="type === 'purchase'">
                                    <label class="label">Total harga beli (Rp)</label>
                                    <input type="number" min="0" name="total_cost" class="input w-40" :required="type === 'purchase'">
                                </div>
                                <div class="min-w-48 flex-1">
                                    <label class="label">Catatan</label>
                                    <input name="note" maxlength="150" class="input" placeholder="mis. beli di supplier A">
                                </div>
                                <button class="btn-primary">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-espresso-600">Belum ada bahan. Tambahkan bahan seperti biji kopi, susu, gula, lalu pasang di resep produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
