@extends('layouts.panel')

@section('title', 'Akun')

@section('content')
    <div class="card mb-6 max-w-2xl p-4 sm:p-6">
        <p class="text-sm text-gray-500">Masuk sebagai</p>
        <p class="text-lg font-semibold">{{ auth('kafehub')->user()->name }} <span class="font-normal text-gray-500">· {{ auth('kafehub')->user()->username }}</span></p>
    </div>
    <form method="POST" action="{{ route('panel.password') }}" class="card max-w-2xl space-y-4 p-4 sm:p-6">
        @csrf
        @method('PUT')
        <h2 class="text-lg font-semibold">Ganti password panel</h2>
        @if ($errors->password->any())
            <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->password->first() }}</div>
        @endif
        <div class="grid gap-3">
            <div>
                <label for="current_password" class="label">Password lama</label>
                <input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="input">
            </div>
            <div>
                <label for="new_password" class="label">Password baru</label>
                <input id="new_password" type="password" name="password" required minlength="8" autocomplete="new-password" class="input">
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi password baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
            </div>
        </div>
        <button class="btn-primary">Ganti password</button>
    </form>
@endsection
