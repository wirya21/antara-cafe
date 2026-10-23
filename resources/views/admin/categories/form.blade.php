@extends('layouts.admin')

@section('title', $category->exists ? 'Ubah kategori' : 'Tambah kategori')

@section('content')
    <form method="POST"
          action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="card max-w-xl space-y-4 p-4 sm:p-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div>
            <label for="name" class="label">Nama kategori</label>
            <input id="name" name="name" value="{{ old('name', $category->name) }}" required class="input" placeholder="mis. Espresso Based">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="group" class="label">Kelompok</label>
            <select id="group" name="group" class="input">
                @foreach (\App\Models\Category::GROUPS as $value => $label)
                    <option value="{{ $value }}" @selected(old('group', $category->group) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('group') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sort_order" class="label">Urutan tampil</label>
            <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="input">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Tampilkan di menu
        </label>

        <div class="flex gap-2">
            <button class="btn-primary">Simpan</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>
@endsection
