@extends('layouts.admin')

@section('title', $product->exists ? 'Ubah produk' : 'Tambah produk')

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          class="grid max-w-4xl gap-6 lg:grid-cols-[1fr_280px]">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="card space-y-4 p-6">
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

            <div class="flex gap-2">
                <button class="btn-primary">Simpan</button>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost">Batal</a>
            </div>
        </div>

        <div class="card h-fit space-y-3 p-6">
            <p class="label">Foto produk</p>
            <div class="aspect-[4/3] overflow-hidden rounded-lg bg-espresso-100">
                <img id="preview" src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover {{ $product->image_url ? '' : 'hidden' }}">
            </div>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm"
                   onchange="const f=this.files[0]; if(f){const p=document.getElementById('preview'); p.src=URL.createObjectURL(f); p.classList.remove('hidden');}">
            <p class="text-xs text-espresso-600">JPG, PNG, atau WebP. Maksimal 3 MB.</p>
            @error('image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            @if ($product->image_path)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_image" value="1"> Hapus foto
                </label>
            @endif
        </div>
    </form>
@endsection
