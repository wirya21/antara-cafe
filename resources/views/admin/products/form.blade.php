@extends('layouts.admin')

@section('title', $product->exists ? 'Ubah produk' : 'Tambah produk')

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          class="grid max-w-4xl gap-6 lg:grid-cols-[1fr_280px]">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="card space-y-4 p-4 sm:p-6">
            <div>
                <label for="name" class="label">Nama produk</label>
                <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="input" placeholder="mis. Caffe Latte">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category_id" class="label">Kategori</label>
                    <select id="category_id" name="category_id" required class="input">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="prep_minutes" class="label">Perkiraan waktu buat (menit)</label>
                    <input id="prep_minutes" type="number" min="1" max="180" name="prep_minutes" value="{{ old('prep_minutes', $product->prep_minutes) }}" class="input" placeholder="mis. 5">
                    <p class="mt-1 text-xs text-espresso-600">Ditampilkan ke pelanggan sebagai perkiraan pesanan siap.</p>
                    @error('prep_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="label">Deskripsi</label>
                <textarea id="description" name="description" rows="3" class="input" placeholder="Espresso dengan susu segar dan sedikit foam">{{ old('description', $product->description) }}</textarea>
            </div>

            <div>
                <label for="sort_order" class="label">Urutan tampil</label>
                <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="input w-32">
            </div>

            <div class="flex flex-wrap gap-4 text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available))> Tersedia
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> Unggulan (tampil menonjol di layar)
                </label>
            </div>

            @php
                $recipeRows = old('recipe', $product->ingredients->map(fn ($i) => ['ingredient_id' => $i->id, 'quantity' => $i->pivot->quantity])->values()->all());
                $ingredientData = $ingredients->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit, 'cost' => (float) $i->cost_per_unit, 'stock' => (float) $i->stock])->values();
            @endphp
            <div class="space-y-4"
                 x-data="{
                     rows: @js(array_values($recipeRows)),
                     ingredients: @js($ingredientData),
                     price: {{ (int) old('price', $product->price) }},
                     manualCost: {{ (int) old('manual_cost', $product->manual_cost) }},
                     overhead: {{ $overheadPerServing }},
                     margin: 0,
                     info(id) { return this.ingredients.find(i => i.id == id) },
                     rowCost(r) { return (Number(r.quantity) || 0) * (this.info(r.ingredient_id)?.cost ?? 0) },
                     portions(r) { const q = Number(r.quantity) || 0, i = this.info(r.ingredient_id); return i && q > 0 ? Math.floor(Math.max(i.stock, 0) / q + 1e-9) : null },
                     get hasRecipe() { return this.rows.some(r => r.ingredient_id && Number(r.quantity) > 0) },
                     get materialCost() { return this.hasRecipe ? Math.round(this.rows.reduce((s, r) => s + this.rowCost(r), 0)) : (Number(this.manualCost) || 0) },
                     get totalCost() { return this.materialCost + this.overhead },
                     get profit() { return this.price - this.totalCost },
                     get currentMargin() { return this.price > 0 ? Math.round(this.profit / this.price * 100) : 0 },
                     applyMargin() { const m = Math.min(Number(this.margin) || 0, 95); this.price = Math.ceil(this.totalCost / (1 - m / 100) / 500) * 500 },
                     rp(v) { return 'Rp' + Math.round(v).toLocaleString('id-ID') },
                 }"
                 x-init="margin = currentMargin">

                {{-- Resep --}}
                <div class="rounded-xl border border-espresso-100 p-4">
                    <div class="flex items-center justify-between">
                        <p class="label mb-0">1. Bahan per porsi (resep)</p>
                        <button type="button" @click="rows.push({ ingredient_id: '', quantity: '' })" class="text-sm font-semibold text-espresso-700 hover:underline">+ Tambah bahan</button>
                    </div>
                    <p class="mt-1 text-xs text-espresso-600">Tulis berapa banyak tiap bahan untuk <b>satu porsi</b>. Stok bahan berkurang otomatis setiap porsi terjual, dan menu otomatis "Habis" bila salah satu bahan tidak cukup.
                        @if ($ingredients->isEmpty()) Belum ada bahan, <a href="{{ route('admin.ingredients.create') }}" class="underline">tambahkan dulu di Stok bahan</a>. @endif
                    </p>
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="mt-3 rounded-lg bg-espresso-50 p-2">
                            <div class="flex items-center gap-2">
                                <select :name="`recipe[${i}][ingredient_id]`" x-model="row.ingredient_id" class="input">
                                    <option value="">Pilih bahan</option>
                                    <template x-for="ing in ingredients" :key="ing.id">
                                        <option :value="ing.id" x-text="ing.name" :selected="ing.id == row.ingredient_id"></option>
                                    </template>
                                </select>
                                <input type="number" step="0.01" min="0" :name="`recipe[${i}][quantity]`" x-model="row.quantity" class="input w-24" placeholder="Jumlah">
                                <span class="w-12 shrink-0 text-sm text-espresso-600" x-text="info(row.ingredient_id)?.unit ?? ''"></span>
                                <button type="button" @click="rows.splice(i, 1)" class="shrink-0 px-1 text-red-700" aria-label="Hapus">✕</button>
                            </div>
                            <p x-show="info(row.ingredient_id) && Number(row.quantity) > 0" class="mt-1.5 flex flex-wrap gap-x-3 px-1 text-xs text-espresso-600">
                                <span>Biaya <b x-text="rp(rowCost(row))"></b></span>
                                <span>Stok <b x-text="(info(row.ingredient_id)?.stock ?? 0).toLocaleString('id-ID') + ' ' + (info(row.ingredient_id)?.unit ?? '')"></b></span>
                                <span :class="portions(row) === 0 ? 'font-semibold text-red-700' : ''">cukup untuk <b x-text="portions(row)"></b> porsi</span>
                            </p>
                        </div>
                    </template>
                    <div x-show="!hasRecipe" class="mt-3">
                        <label for="manual_cost" class="label">Atau: harga pokok manual per porsi (Rp)</label>
                        <input id="manual_cost" type="number" min="0" step="100" name="manual_cost" x-model.number="manualCost" class="input w-48" placeholder="mis. 12000">
                        <p class="mt-1 text-xs text-espresso-600">Untuk menu tanpa resep, misalnya kue yang dibeli jadi.</p>
                    </div>
                </div>

                {{-- Harga pokok & harga jual --}}
                <div class="rounded-xl border border-espresso-100 p-4">
                    <p class="label mb-2">2. Harga pokok & harga jual</p>
                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between"><dt class="text-espresso-600">HPP bahan</dt><dd class="tabular-nums" x-text="rp(materialCost)"></dd></div>
                        <div class="flex justify-between">
                            <dt class="text-espresso-600">Biaya operasional per porsi
                                <a href="{{ route('admin.settings.edit') }}" class="text-xs underline">{{ $overheadPerServing ? 'ubah' : 'isi di Pengaturan' }}</a></dt>
                            <dd class="tabular-nums" x-text="rp(overhead)"></dd>
                        </div>
                        <div class="flex justify-between border-t border-espresso-100 pt-1.5 font-semibold"><dt>Harga pokok total</dt><dd class="tabular-nums" x-text="rp(totalCost)"></dd></div>
                    </dl>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="price" class="label">Harga jual (Rp)</label>
                            <input id="price" type="number" min="0" step="500" name="price" x-model.number="price" @input="margin = currentMargin" required class="input" placeholder="25000">
                            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="margin" class="label">atau target margin (%)</label>
                            <div class="flex gap-2">
                                <input id="margin" type="number" min="0" max="95" x-model.number="margin" @keydown.enter.prevent="applyMargin()" class="input w-24">
                                <button type="button" @click="applyMargin()" class="btn-ghost whitespace-nowrap">Hitung harga</button>
                            </div>
                            <p class="mt-1 text-xs text-espresso-600">Harga dibulatkan ke atas per Rp500.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 rounded-lg p-3 text-sm" :class="profit < 0 ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-900'">
                        <div><p>Untung per porsi</p><p class="text-lg font-semibold tabular-nums" x-text="rp(profit)"></p></div>
                        <div><p>Margin</p><p class="text-lg font-semibold tabular-nums" x-text="currentMargin + '%'"></p></div>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="btn-primary">Simpan</button>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost">Batal</a>
            </div>
        </div>

        <div class="card order-first h-fit space-y-3 p-4 sm:p-6 lg:order-none">
            <p class="label">Foto produk</p>
            <x-image-input :url="$product->image_url" hint="JPG, PNG, atau WebP. Maksimal 3 MB." />
            @if ($product->image_path)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_image" value="1"> Hapus foto
                </label>
            @endif
        </div>
    </form>
@endsection
