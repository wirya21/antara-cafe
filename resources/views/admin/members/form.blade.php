@extends('layouts.admin')

@section('title', 'Ubah member')

@section('content')
    <form method="POST" action="{{ route('admin.members.update', $member) }}" class="card max-w-xl space-y-4 p-4 sm:p-6">
        @csrf @method('PUT')
        <div>
            <label for="name" class="label">Nama</label>
            <input id="name" name="name" value="{{ old('name', $member->name) }}" required class="input">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="phone" class="label">No. HP</label>
            <input id="phone" name="phone" value="{{ old('phone', $member->phone) }}" required class="input">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="points" class="label">Poin</label>
            <input id="points" type="number" min="0" name="points" value="{{ old('points', $member->points) }}" required class="input w-40">
            <p class="mt-1 text-xs text-espresso-600">Kurangi poin di sini saat member menukar hadiah.</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="btn-primary">Simpan</button>
            <a href="{{ route('admin.members.index') }}" class="btn-ghost">Batal</a>
            <button form="delete-member" class="btn-danger ml-auto">Hapus</button>
        </div>
    </form>
    <form id="delete-member" method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Hapus member ini?')">@csrf @method('DELETE')</form>
@endsection
