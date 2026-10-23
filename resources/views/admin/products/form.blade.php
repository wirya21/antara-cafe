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
                    <label for="price" class="label">Harga (Rp)</label>
                    <input id="price" type="number" min="0" step="500" name="price" value="{{ old('price', $product->price) }}" required class="input" placeholder="25000">
                    @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                $ingredientData = $ingredients->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit, 'cost' => $i->cost_per_unit])->values();
            @endphp
            <div class="rounded-xl border border-espresso-100 p-4"
                 x-data="{ rows: @js(array_values($recipeRows)), ingredients: @js($ingredientData), price: {{ (int) old('price', $product->price) }},
                           info(id) { return this.ingredients.find(i => i.id == id) },
                           get cost() { return Math.round(this.rows.reduce((s, r) => s + (Number(r.quantity) || 0) * (this.info(r.ingredient_id)?.cost ?? 0), 0)) } }"
                 @input.window="if ($event.target.id === 'price') price = Number($event.target.value) || 0">
                <div class="flex items-center justify-between">
                    <p class="label mb-0">Resep / bahan per porsi</p>
                    <button type="button" @click="rows.push({ ingredient_id: '', quantity: '' })" class="text-sm font-semibold text-espresso-700 hover:underline">+ Tambah bahan</button>
                </div>
                <p class="mt-1 text-xs text-espresso-600">Stok bahan ini berkurang otomatis setiap porsi terjual.
                    @if ($ingredients->isEmpty()) Belum ada bahan, <a href="{{ route('admin.ingredients.create') }}" class="underline">tambahkan dulu di Stok bahan</a>. @endif
                </p>
                <template x-for="(row, i) in rows" :key="i">
                    <div class="mt-2 flex items-center gap-2">
                        <select :name="`recipe[${i}][ingredient_id]`" x-model="row.ingredient_id" class="input">
                            <option value="">Pilih bahan</option>
                            <template x-for="ing in ingredients" :key="ing.id">
                                <option :value="ing.id" x-text="ing.name" :selected="ing.id == row.ingredient_id"></option>
                            </template>
                        </select>
                        <input type="number" step="0.01" min="0" :name="`recipe[${i}][quantity]`" x-model="row.quantity" class="input w-28" placeholder="Jumlah">
                        <span class="w-12 text-sm text-espresso-600" x-text="info(row.ingredient_id)?.unit ?? ''"></span>
                        <button type="button" @click="rows.splice(i, 1)" class="text-red-700" aria-label="Hapus">✕</button>
                    </div>
                </template>
                <p x-show="rows.length" class="mt-3 text-sm">
                    HPP perkiraan: <b x-text="'Rp' + cost.toLocaleString('id-ID')"></b>
                    <span x-show="price > 0" class="text-espresso-600">· margin <b x-text="Math.round((price - cost) / price * 100) + '%'"></b></span>
                </p>
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
