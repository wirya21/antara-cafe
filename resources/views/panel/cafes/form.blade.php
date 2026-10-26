@extends('layouts.panel')

@section('title', $cafe->exists ? $cafe->name : 'Tambah kafe')

@section('actions')
    @if ($cafe->exists)
        <div class="flex items-center gap-2">
            <x-cafe-status :cafe="$cafe" />
            <a href="{{ $cafe->url() }}" target="_blank" class="btn-ghost">Buka kafe ↗</a>
        </div>
    @endif
@endsection

@section('content')
    @if (session('credentials'))
        <div class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 p-5">
            <p class="font-semibold text-brand-800">Login admin kafe</p>
            <p class="mt-1 text-sm text-brand-800/80">Kirim ke pemilik kafe sekarang. Password ini tidak ditampilkan lagi; minta pemilik menggantinya setelah masuk.</p>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-[2fr_1fr_1fr]">
                <div><dt class="text-xs text-gray-500">Alamat login</dt><dd class="font-mono font-semibold break-all">{{ $cafe->url('login') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Username</dt><dd class="font-mono font-semibold">{{ session('credentials.username') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Password</dt><dd class="font-mono font-semibold">{{ session('credentials.password') }}</dd></div>
            </dl>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ $cafe->exists ? route('panel.cafes.update', $cafe) : route('panel.cafes.store') }}" class="card space-y-5 p-5 sm:p-6 lg:col-span-2"
              x-data="{ name: @js(old('name', $cafe->name)), slug: @js(old('slug', $cafe->slug)), touched: @js($cafe->exists), trial: @js((bool) old('is_trial', $cafe->is_trial)) }">
            @csrf
            @if ($cafe->exists) @method('PUT') @endif

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Nama kafe</label>
                    <input id="name" name="name" x-model="name" @input="if (!touched) slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 30)" required maxlength="80" class="input">
                </div>
                <div>
                    <label for="slug" class="label">Alamat (subdomain)</label>
                    <div class="flex items-stretch overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100">
                        <input id="slug" name="slug" x-model="slug" @input="touched = true" required maxlength="30" class="min-w-0 flex-1 border-0 px-3 py-2 text-sm focus:ring-0 focus:outline-none" autocapitalize="none">
                        <span class="grid place-items-center bg-gray-50 px-3 text-sm text-gray-500">.{{ config('kafehub.domain') }}</span>
                    </div>
                    @if ($cafe->exists)<p class="mt-1 text-xs text-amber-700">Mengubah alamat membuat QR meja dan tautan lama tidak berlaku.</p>@endif
                </div>
                <div>
                    <label for="owner_name" class="label">Nama pemilik</label>
                    <input id="owner_name" name="owner_name" value="{{ old('owner_name', $cafe->owner_name) }}" maxlength="80" class="input">
                </div>
                <div>
                    <label for="owner_phone" class="label">WhatsApp pemilik</label>
                    <input id="owner_phone" name="owner_phone" value="{{ old('owner_phone', $cafe->owner_phone) }}" inputmode="tel" placeholder="08xxxxxxxxxx" class="input">
                </div>
                <div>
                    <label for="city" class="label">Kota</label>
                    <input id="city" name="city" value="{{ old('city', $cafe->city) }}" maxlength="60" class="input">
                </div>
                @unless ($cafe->exists)
                    <div>
                        <label for="admin_username" class="label">Username admin kafe</label>
                        <input id="admin_username" name="admin_username" value="{{ old('admin_username', 'admin') }}" required maxlength="30" autocapitalize="none" class="input">
                        <p class="mt-1 text-xs text-gray-500">Password dibuat otomatis dan ditampilkan sekali setelah disimpan.</p>
                    </div>
                @endunless
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h2 class="font-semibold">Langganan</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="active_until" class="label">Aktif sampai</label>
                        <input id="active_until" type="date" name="active_until" value="{{ old('active_until', $cafe->active_until?->toDateString()) }}" class="input">
                        <p class="mt-1 text-xs text-gray-500">Kosongkan untuk tanpa batas waktu.</p>
                    </div>
                    <div>
                        <label for="monthly_fee" class="label">Biaya per bulan (Rp)</label>
                        <input id="monthly_fee" type="number" min="0" step="1000" name="monthly_fee" value="{{ old('monthly_fee', $cafe->monthly_fee ?: '') }}" class="input">
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_trial" value="0">
                        <input type="checkbox" name="is_trial" value="1" x-model="trial" class="rounded border-gray-300 text-brand-500"> Masih masa coba
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $cafe->is_active)) class="rounded border-gray-300 text-brand-500"> Aktif <span class="text-gray-500">(matikan untuk menghentikan kafe ini sementara)</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="notes" class="label">Catatan</label>
                <textarea id="notes" name="notes" rows="2" maxlength="1000" class="input">{{ old('notes', $cafe->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-5">
                <a href="{{ route('panel.cafes.index') }}" class="btn-ghost">Batal</a>
                <button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-brand-500/30 transition hover:bg-brand-600">{{ $cafe->exists ? 'Simpan' : 'Daftarkan kafe' }}</button>
            </div>
        </form>

        @if ($cafe->exists)
            <div class="space-y-6">
                <section class="card p-5">
                    <h2 class="font-semibold">Aktivitas</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Total pesanan dibayar</dt><dd class="font-semibold tabular-nums">{{ number_format($stats->orders, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Total omzet</dt><dd class="font-semibold tabular-nums"><x-rupiah :value="$stats->revenue" /></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Pesanan terakhir</dt><dd>{{ $stats->last_paid ? \Illuminate\Support\Carbon::parse($stats->last_paid)->diffForHumans() : 'Belum ada' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Produk di menu</dt><dd class="tabular-nums">{{ $productCount }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Terdaftar</dt><dd>{{ $cafe->created_at->translatedFormat('j M Y') }}</dd></div>
                    </dl>
                    <form method="POST" action="{{ route('panel.cafes.extend', $cafe) }}" class="mt-4" onsubmit="return confirm('Perpanjang langganan {{ $cafe->name }} satu bulan?')">
                        @csrf
                        <button class="w-full rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100">Perpanjang 1 bulan</button>
                    </form>
                </section>

                <section class="card p-5">
                    <h2 class="font-semibold">Akun staf</h2>
                    <ul class="mt-3 divide-y divide-gray-100">
                        @forelse ($admins as $user)
                            <li class="flex items-center justify-between gap-2 py-2 text-sm">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold">{{ $user->username }}</p>
                                    <p class="text-xs text-gray-500">{{ \App\Models\User::ROLES[$user->role] ?? $user->role }} · {{ $user->name }}</p>
                                </div>
                                <form method="POST" action="{{ route('panel.cafes.reset-password', [$cafe, $user->id]) }}" onsubmit="return confirm('Buat password baru untuk {{ $user->username }}?')">
                                    @csrf
                                    <button class="text-xs font-semibold text-brand-600 hover:underline">Atur ulang password</button>
                                </form>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500">Belum ada akun.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        @endif
    </div>
@endsection
