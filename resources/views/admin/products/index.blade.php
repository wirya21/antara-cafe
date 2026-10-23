@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari produk…" class="input w-48">
            <select name="category" class="input w-44" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button class="btn-ghost">Cari</button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">+ Tambah produk</a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($products as $product)
            <div class="card overflow-hidden">
                <div class="aspect-[4/3] bg-espresso-100">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-4xl text-espresso-200">☕</div>
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold">{{ $product->name }}</p>
                            <p class="text-xs text-espresso-600">{{ $product->category->name }}</p>
                        </div>
                        <p class="font-semibold whitespace-nowrap text-espresso-700"><x-rupiah :value="$product->price" /></p>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1 text-xs">
                        @php $left = $product->servingsLeft(); @endphp
                        @unless ($product->is_available)
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">Tidak dijual</span>
                        @else
                            @if ($left === 0)
                                <span class="rounded-full bg-red-100 px-2 py-0.5 font-semibold text-red-700" title="Stok {{ $product->limitingIngredient()?->name }} habis">Habis · stok {{ $product->limitingIngredient()?->name }}</span>
                            @elseif ($left !== null && $left <= \App\Models\Product::LOW_SERVINGS)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">Sisa {{ $left }} porsi</span>
                            @elseif ($left !== null)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-green-700">± {{ $left }} porsi</span>
                            @endif
                        @endunless
                        @if ($product->is_featured)
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700">Unggulan</span>
                        @endif
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn-ghost flex-1 py-1.5">Ubah</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                            @csrf @method('DELETE')
                            <button class="btn-danger py-1.5">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card col-span-full p-8 text-center text-espresso-600">
                Belum ada produk.
                @if ($categories->isEmpty())
                    Buat <a href="{{ route('admin.categories.create') }}" class="underline">kategori</a> dulu.
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
