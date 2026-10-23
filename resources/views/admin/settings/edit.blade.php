@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="grid max-w-5xl gap-6 lg:grid-cols-2">
        @csrf
        @method('PUT')

        <div class="card space-y-4 p-4 sm:p-6">
            <div>
                <h2 class="text-lg font-semibold">Pembayaran non-tunai</h2>
                <p class="text-sm text-espresso-600">Ditampilkan ke pelanggan yang memilih QRIS atau transfer. Pelanggan lalu mengunggah bukti bayar, dan staf menekan <b>Konfirmasi pembayaran</b>. Pilihan QRIS/transfer baru muncul di menu pelanggan setelah diisi.</p>
            </div>

            <div>
                <p class="label">Gambar QRIS kafe</p>
                <div class="max-w-xs"><x-image-input :url="$qrisUrl" aspect="aspect-square" hint="Unduh QRIS statis dari aplikasi bank/merchant Anda (JPG/PNG, maks 3 MB)." /></div>
                @if ($qrisUrl)
                    <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_image" value="1"> Hapus QRIS</label>
                @endif
            </div>

            <div>
                <label for="bank_accounts" class="label">Rekening transfer</label>
                <textarea id="bank_accounts" name="bank_accounts" rows="4" class="input" placeholder="BCA 1234567890 a.n. Antara Cafe&#10;Mandiri 0987654321 a.n. Antara Cafe">{{ old('bank_accounts', $bankAccounts) }}</textarea>
                <p class="mt-1 text-xs text-espresso-600">Satu rekening per baris. Kosongkan bila tidak menerima transfer.</p>
            </div>
        </div>

        <div class="card h-fit space-y-4 p-4 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Notifikasi WhatsApp</h2>
                    <p class="text-sm text-espresso-600">Kirim pesan ke WhatsApp setiap ada pesanan baru dan bukti bayar masuk.</p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $whatsappReady ? 'bg-green-100 text-green-800' : 'bg-espresso-100 text-espresso-700' }}">{{ $whatsappReady ? 'Aktif' : 'Belum aktif' }}</span>
            </div>

            <div>
                <label for="whatsapp_numbers" class="label">Nomor WhatsApp tujuan</label>
                <input id="whatsapp_numbers" name="whatsapp_numbers" value="{{ old('whatsapp_numbers', $whatsappNumbers) }}" class="input" placeholder="081234567890, 081298765432" inputmode="tel">
                <p class="mt-1 text-xs text-espresso-600">Boleh lebih dari satu, pisahkan dengan koma.</p>
                @error('whatsapp_numbers') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="fonnte_token" class="label">Token Fonnte</label>
                <input id="fonnte_token" name="fonnte_token" type="password" autocomplete="off" class="input" placeholder="{{ $hasToken ? '•••••••• tersimpan (isi untuk mengganti)' : 'Tempel token dari fonnte.com' }}">
                @if ($hasToken)
                    <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_token" value="1"> Hapus token</label>
                @endif
            </div>

            <details class="rounded-lg bg-espresso-50 p-3 text-sm">
                <summary class="cursor-pointer font-semibold">Cara mendapatkan token Fonnte</summary>
                <ol class="mt-2 list-decimal space-y-1 pl-5 text-espresso-700">
                    <li>Daftar di <a href="https://fonnte.com" target="_blank" rel="noopener" class="underline">fonnte.com</a>.</li>
                    <li>Menu <b>Device</b> → <b>Add device</b>, masukkan nomor WhatsApp pengirim (mis. nomor kafe).</li>
                    <li>Klik <b>Connect</b> lalu scan QR dari WhatsApp (Perangkat tertaut).</li>
                    <li>Salin <b>Token</b> device tersebut dan tempel di kolom di atas.</li>
                </ol>
            </details>

            <div class="flex flex-wrap gap-2 pt-2">
                <button class="btn-primary">Simpan pengaturan</button>
                <button form="tes-wa" class="btn-ghost" @disabled(! $whatsappReady)>Kirim pesan uji</button>
            </div>
        </div>
    </form>

    <form id="tes-wa" method="POST" action="{{ route('admin.settings.whatsapp-test') }}">@csrf</form>
@endsection
