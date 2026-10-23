@extends('layouts.admin')

@section('title', 'Akun admin & kasir')

@section('content')
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.users.create') }}" class="btn-primary">+ Tambah akun</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-espresso-100 text-espresso-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Username</th>
                    <th class="px-4 py-3 font-medium">Peran</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-espresso-50">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 font-mono">{{ $user->username }}</td>
                        <td class="px-4 py-3">{{ \App\Models\User::ROLES[$user->role] ?? $user->role }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn-ghost py-1">Ubah</a>
                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-danger py-1">Hapus</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
