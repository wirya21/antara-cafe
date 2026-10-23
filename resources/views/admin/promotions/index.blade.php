@extends('layouts.admin')

@section('title', 'Promo & voucher')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="max-w-2xl text-sm text-espresso-600">Promo dengan foto tampil sebagai banner di menu pelanggan dan layar TV. Promo diskon tanpa kode voucher berlaku otomatis; yang memakai kode harus diketik pelanggan atau kasir.</p>
        <a href="{{ route('admin.promotions.create') }}" class="btn-primary">+ Tambah promo</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($promotions as $promotion)
            <div class="card overflow-hidden">
                <div class="aspect-[21/9] bg-espresso-100">
                    @if ($promotion->imageUrl())
                        <img src="{{ $promotion->imageUrl() }}" alt="" class="h-full w-full object-cover">
                    @else
                        <div class="grid h-full place-items-center text-sm text-espresso-500">Tanpa banner</div>
                    @endif
                </div>
                <div class="space-y-2 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-semibold">{{ $promotion->title }}</p>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs {{ $promotion->isRunning() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $promotion->isRunning() ? 'Berjalan' : 'Tidak aktif' }}</span>
                    </div>
                    @if ($promotion->discount_type)
                        <p class="text-sm">{{ $promotion->summary() }}</p>
                        <p class="text-sm">
                            @if ($promotion->voucher_code)
                                Kode: <span class="rounded bg-espresso-100 px-1.5 py-0.5 font-mono font-semibold">{{ $promotion->voucher_code }}</span>
                            @else
                                <span class="text-espresso-600">Otomatis tanpa kode</span>
                            @endif
                        </p>
                    @endif
                    <p class="text-xs text-espresso-500">
                        {{ $promotion->starts_at?->format('d/m/Y') ?? 'Mulai sekarang' }} – {{ $promotion->ends_at?->format('d/m/Y') ?? 'tanpa batas' }} · dipakai {{ $promotion->orders_count }}×
                    </p>
                    <div class="flex gap-2 pt-1">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn-ghost flex-1 py-1.5">Ubah</a>
                        <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Hapus promo ini?')">
                            @csrf @method('DELETE')
                            <button class="btn-danger py-1.5">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card col-span-full p-8 text-center text-espresso-600">Belum ada promo.</div>
        @endforelse
    </div>
@endsection
