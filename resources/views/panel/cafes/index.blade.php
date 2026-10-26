@extends('layouts.panel')

@section('title', 'Kafe')

@section('actions')
    <a href="{{ route('panel.cafes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-brand-500/30 transition hover:bg-brand-600">+ Tambah kafe</a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="cari" value="{{ $search }}" placeholder="Cari nama, subdomain, pemilik, kota" class="input max-w-xs">
        <select name="status" class="input w-auto" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (\App\Models\Cafe::STATUSES as $key => [$label])
                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn-ghost">Cari</button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-2.5">Kafe</th>
                        <th class="px-5 py-2.5">Pemilik</th>
                        <th class="px-5 py-2.5">Status</th>
                        <th class="px-5 py-2.5">Aktif sampai</th>
                        <th class="px-5 py-2.5 text-right">Biaya/bulan</th>
                        <th class="px-5 py-2.5 text-right">Pesanan 30 hari</th>
                        <th class="px-5 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($cafes as $cafe)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('panel.cafes.edit', $cafe) }}" class="font-semibold hover:text-brand-600">{{ $cafe->name }}</a>
                                <p class="text-xs text-gray-500">{{ $cafe->slug }}.{{ config('kafehub.domain') }}@if ($cafe->city) · {{ $cafe->city }}@endif</p>
                            </td>
                            <td class="px-5 py-3">
                                {{ $cafe->owner_name ?: '–' }}
                                @if ($cafe->owner_phone)<p class="text-xs text-gray-500">{{ $cafe->owner_phone }}</p>@endif
                            </td>
                            <td class="px-5 py-3"><x-cafe-status :cafe="$cafe" /></td>
                            <td class="px-5 py-3 text-gray-600">{{ $cafe->active_until?->translatedFormat('j M Y') ?? 'Tanpa batas' }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ $cafe->monthly_fee ? 'Rp'.number_format($cafe->monthly_fee, 0, ',', '.') : '–' }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ number_format($orders[$cafe->id] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ $cafe->url() }}" target="_blank" class="text-xs font-semibold text-gray-500 hover:text-gray-900">Buka ↗</a>
                                <a href="{{ route('panel.cafes.edit', $cafe) }}" class="ml-3 text-xs font-semibold text-brand-600 hover:underline">Kelola</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">Tidak ada kafe yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
