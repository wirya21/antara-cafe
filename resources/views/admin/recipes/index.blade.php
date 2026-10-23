@extends('layouts.admin')

@section('title', 'Resep & stok')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="max-w-2xl text-sm text-espresso-600">Setiap menu dan bahan pembuatnya. <b>Cukup untuk</b> menunjukkan berapa porsi lagi yang bisa dibuat dari stok bahan itu. Bahan dengan angka terkecil menentukan sisa porsi menu. Klik <b>Ubah stok</b> untuk menambah atau mengoreksi stok bahan langsung dari sini.</p>
        <form method="GET">
            <select name="category" class="input w-48" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid gap-4 xl:grid-cols-2" x-data="{ open: null }">
        @forelse ($products as $product)
            @php
                $left = $product->servingsLeft();
                $limiting = $product->ingredients->count() > 1 ? $product->limitingIngredient() : null;
            @endphp
            <div class="card overflow-x-auto">
                <div class="flex flex-wrap items-start justify-between gap-2 border-b border-espresso-100 p-4">
                    <div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold hover:underline">{{ $product->name }}</a>
                        <p class="text-xs text-espresso-600">{{ $product->category->name }} · <x-rupiah :value="$product->price" />
                            · HPP bahan <x-rupiah :value="$product->estimatedCost()" />
                            @if ($product->prep_minutes) · ⏱ {{ $product->prep_minutes }} mnt @endif
                        </p>
                    </div>
                    @if (! $product->is_available)
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Tidak dijual</span>
                    @elseif ($left === null)
                        <span class="rounded-full bg-espresso-100 px-2.5 py-1 text-xs font-semibold text-espresso-700">Tanpa resep · selalu tersedia</span>
                    @elseif ($left === 0)
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">Habis · stok {{ $product->limitingIngredient()?->name }}</span>
                    @else
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $left <= \App\Models\Product::LOW_SERVINGS ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">Bisa dibuat {{ $left }} porsi</span>
                    @endif
                </div>

                @if ($product->ingredients->isEmpty())
                    <p class="p-4 text-sm text-espresso-600">Belum ada resep. <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold underline">Atur bahan per porsi</a> supaya stok terhitung otomatis.</p>
                @else
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-espresso-600">
                            <tr>
                                <th class="px-4 py-2 font-medium">Bahan</th>
                                <th class="px-2 py-2 text-right font-medium whitespace-nowrap">Butuh / porsi</th>
                                <th class="px-2 py-2 text-right font-medium whitespace-nowrap">Stok sekarang</th>
                                <th class="px-2 py-2 text-right font-medium whitespace-nowrap">Cukup untuk</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-espresso-50">
                            @foreach ($product->ingredients as $ingredient)
                                @php
                                    $portions = (int) floor(max($ingredient->stock, 0) / $ingredient->pivot->quantity + 1e-9);
                                    $key = $product->id.'-'.$ingredient->id;
                                @endphp
                                <tr class="{{ $limiting?->is($ingredient) ? 'bg-amber-50/60' : '' }}">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('admin.ingredients.show', $ingredient) }}" class="hover:underline">{{ $ingredient->name }}</a>
                                        @if ($limiting?->is($ingredient))<span class="ml-1 text-xs text-amber-800">← paling sedikit</span>@endif
                                    </td>
                                    <td class="px-2 py-2 text-right whitespace-nowrap tabular-nums"><x-qty :value="$ingredient->pivot->quantity" /> {{ $ingredient->unit }}</td>
                                    <td class="px-2 py-2 text-right whitespace-nowrap tabular-nums {{ $ingredient->stock <= $ingredient->min_stock ? 'font-semibold text-amber-800' : '' }}"><x-qty :value="$ingredient->stock" /> {{ $ingredient->unit }}</td>
                                    <td class="px-2 py-2 text-right font-semibold whitespace-nowrap tabular-nums {{ $portions === 0 ? 'text-red-700' : '' }}">{{ $portions }} porsi</td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button" @click="open = open === '{{ $key }}' ? null : '{{ $key }}'" class="text-xs font-semibold whitespace-nowrap text-espresso-700 hover:underline">Ubah stok</button>
                                    </td>
                                </tr>
                                <tr x-show="open === '{{ $key }}'" x-cloak>
                                    <td colspan="5" class="bg-espresso-50 px-4 py-3">
                                        <x-stock-move-form :ingredient="$ingredient" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @empty
            <div class="card col-span-full p-8 text-center text-espresso-600">Belum ada produk.</div>
        @endforelse
    </div>

    @if ($unused->isNotEmpty())
        <div class="card mt-6 p-4 text-sm">
            <p class="font-semibold">Bahan yang belum dipakai di resep mana pun</p>
            <p class="mt-1 text-espresso-600">{{ $unused->pluck('name')->join(', ') }}. Stoknya tidak akan berkurang sampai dipasang di resep menu.</p>
        </div>
    @endif
@endsection
