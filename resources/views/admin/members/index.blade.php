@extends('layouts.admin')

@section('title', 'Member')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / no. HP" class="input w-56">
            <button class="btn-ghost">Cari</button>
        </form>
        <p class="text-sm text-espresso-600">Member terdaftar otomatis saat pelanggan atau kasir mengisi no. HP. 1 poin setiap Rp{{ number_format(\App\Models\Member::RUPIAH_PER_POINT, 0, ',', '.') }}.</p>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">No. HP</th>
                    <th class="px-4 py-3 text-right font-medium">Poin</th>
                    <th class="px-4 py-3 text-right font-medium">Kunjungan</th>
                    <th class="px-4 py-3 text-right font-medium">Total belanja</th>
                    <th class="px-4 py-3 font-medium">Bergabung</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-espresso-50">
                @forelse ($members as $member)
                    <tr>
                        <td class="px-4 py-2.5 font-medium">{{ $member->name }}</td>
                        <td class="px-4 py-2.5 font-mono">{{ $member->phone }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold">{{ number_format($member->points, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $member->orders_count }}</td>
                        <td class="px-4 py-2.5 text-right tabular-nums"><x-rupiah :value="$member->orders_sum_total ?? 0" /></td>
                        <td class="px-4 py-2.5">{{ $member->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-right"><a href="{{ route('admin.members.edit', $member) }}" class="btn-ghost py-1">Ubah</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-espresso-600">Belum ada member.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $members->links() }}</div>
@endsection
