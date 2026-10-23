@extends('layouts.admin')

@section('title', $user->exists ? 'Ubah akun' : 'Tambah akun')

@section('content')
    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card max-w-xl space-y-4 p-4 sm:p-6">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div>
            <label for="name" class="label">Nama</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="input">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="username" class="label">Username (untuk login)</label>
            <input id="username" name="username" value="{{ old('username', $user->username) }}" required autocapitalize="none" class="input" placeholder="mis. kasirpagi">
            <p class="mt-1 text-xs text-espresso-600">Huruf kecil, angka, - atau _. Tanpa spasi.</p>
            @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="label">Email (opsional)</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="role" class="label">Peran</label>
            <select id="role" name="role" class="input">
                @foreach (\App\Models\User::ROLES as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-espresso-600">Kasir hanya bisa membuka halaman kasir. Admin bisa membuka semuanya.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password {{ $user->exists ? '(kosongkan jika tidak diganti)' : '' }}</label>
                <input id="password" type="password" name="password" autocomplete="new-password" class="input" @required(! $user->exists)>
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="input">
            </div>
        </div>

        <div class="flex gap-2">
            <button class="btn-primary">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>
@endsection
