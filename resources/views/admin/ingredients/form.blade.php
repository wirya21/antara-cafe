@extends('layouts.admin')

@section('title', $ingredient->exists ? 'Ubah bahan' : 'Tambah bahan')

@section('content')
    <form method="POST" action="{{ $ingredient->exists ? route('admin.ingredients.update', $ingredient) : route('admin.ingredients.store') }}" class="card max-w-xl space-y-4 p-4 sm:p-6">
        @csrf
        @if ($ingredient->exists) @method('PUT') @endif

        <div>
            <label for="name" class="label">Nama bahan</label>
            <input id="name" name="name" value="{{ old('name', $ingredient->name) }}" required class="input" placeholder="mis. Biji kopi arabika">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="unit" class="label">Satuan</label>
                <input id="unit" name="unit" list="units" value="{{ old('unit', $ingredient->unit) }}" required class="input">
                <datalist id="units">
                    @foreach (\App\Models\Ingredient::UNITS as $unit)<option value="{{ $unit }}">@endforeach
                </datalist>
                <p class="mt-1 text-xs text-espresso-600">Pakai satuan terkecil yang dipakai di resep, mis. gram atau ml.</p>
            </div>
            <div>
                <label for="min_stock" class="label">Stok minimum</label>
                <input id="min_stock" type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock', $ingredient->min_stock ?? 0) }}" class="input">
                <p class="mt-1 text-xs text-espresso-600">Muncul peringatan jika stok di bawah angka ini.</p>
            </div>
        </div>

        @unless ($ingredient->exists)
            <div class="grid gap-4 rounded-xl bg-espresso-50 p-4 sm:grid-cols-2">
                <div>
                    <label for="initial_stock" class="label">Stok awal</label>
                    <input id="initial_stock" type="number" step="0.01" min="0" name="initial_stock" value="{{ old('initial_stock') }}" class="input">
                </div>
                <div>
                    <label for="initial_cost" class="label">Total harga stok awal (Rp)</label>
                    <input id="initial_cost" type="number" min="0" name="initial_cost" value="{{ old('initial_cost') }}" class="input" placeholder="mis. 250000 untuk 1000 gram">
                </div>
            </div>
        @endunless

        <div class="flex items-center gap-2">
            <button class="btn-primary">Simpan</button>
            <a href="{{ route('admin.ingredients.index') }}" class="btn-ghost">Batal</a>
            @if ($ingredient->exists)
                <button form="delete-ingredient" class="btn-danger ml-auto">Hapus</button>
            @endif
        </div>
    </form>
    @if ($ingredient->exists)
        <form id="delete-ingredient" method="POST" action="{{ route('admin.ingredients.destroy', $ingredient) }}" onsubmit="return confirm('Hapus bahan ini?')">
            @csrf @method('DELETE')
        </form>
    @endif
@endsection
