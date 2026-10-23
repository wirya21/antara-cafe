@extends('layouts.admin')

@section('title', 'Kartu stok: '.$ingredient->name)

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-4">
        <div class="card px-5 py-4">
            <p class="text-sm text-espresso-600">Stok sekarang</p>
            <p class="text-2xl font-semibold"><x-qty :value="$ingredient->stock" /> {{ $ingredient->unit }}</p>
        </div>
        <div class="card px-5 py-4">
            <p class="text-sm text-espresso-600">Harga rata-rata</p>
            <p class="text-2xl font-semibold">Rp{{ number_format($ingredient->cost_per_unit, 2, ',', '.') }}<span class="text-sm font-normal">/{{ $ingredient->unit }}</span></p>
        </div>
        <div class="text-sm text-espresso-600">
            Dipakai di: {{ $ingredient->products->pluck('name')->join(', ') ?: '-' }}
        </div>
        <a href="{{ route('admin.ingredients.index') }}" class="btn-ghost ml-auto">← Semua bahan</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Waktu</th>
                    <th class="px-4 py-3 font-medium">Jenis</th>
                    <th class="px-4 py-3 text-right font-medium">Perubahan</th>
                    <th class="px-4 py-3 text-right font-medium">Sisa</th>
                    <th class="px-4 py-3 font-medium">Keterangan</th>
                    <th class="px-4 py-3 font-medium">Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-espresso-50">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}</td>
                        <td class="px-4 py-2 text-right font-semibold tabular-nums {{ $movement->quantity < 0 ? 'text-red-700' : 'text-green-700' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}<x-qty :value="$movement->quantity" />
                        </td>
                        <td class="px-4 py-2 text-right tabular-nums"><x-qty :value="$movement->stock_after" /></td>
                        <td class="px-4 py-2 text-espresso-700">
                            @if ($movement->order)
                                <a href="{{ route('admin.orders.show', $movement->order) }}" class="font-mono hover:underline">{{ $movement->order->code }}</a> ·
                            @endif
                            {{ $movement->note }}
                            @if ($movement->total_cost) · <x-rupiah :value="$movement->total_cost" /> @endif
                        </td>
                        <td class="px-4 py-2 text-espresso-600">{{ $movement->user?->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-espresso-600">Belum ada pergerakan stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>
@endsection
