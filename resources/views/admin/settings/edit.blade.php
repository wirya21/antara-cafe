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

            <button form="tes-wa" class="btn-ghost" @disabled(! $whatsappReady)>Kirim pesan uji</button>
        </div>

        {{-- Biaya operasional: dibebankan ke harga pokok tiap porsi --}}
        <div class="card space-y-4 p-4 sm:p-6" x-data="{ costs: @js($overheads), servings: {{ $monthlyServings }},
                get total() { return Object.values(this.costs).reduce((s, v) => s + (Number(v) || 0), 0) },
                get perServing() { return this.servings > 0 ? Math.round(this.total / this.servings) : 0 } }">
            <div>
                <h2 class="text-lg font-semibold">Biaya operasional bulanan</h2>
                <p class="text-sm text-espresso-600">Biaya di luar bahan. Dibagi ke setiap porsi supaya harga pokok di form produk lebih realistis, dan dipakai menghitung <b>laba bersih</b> di Laporan.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (\App\Support\Costing::OVERHEADS as $key => $label)
                    <div>
                        <label for="{{ $key }}" class="label">{{ $label }} (Rp/bulan)</label>
                        <input id="{{ $key }}" type="number" min="0" step="1000" name="overheads[{{ $key }}]" x-model.number="costs.{{ $key }}" class="input" placeholder="0">
                    </div>
                @endforeach
                <div>
                    <label for="op_monthly_servings" class="label">Perkiraan porsi terjual / bulan</label>
                    <input id="op_monthly_servings" type="number" min="0" name="op_monthly_servings" x-model.number="servings" class="input" placeholder="mis. 1500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 rounded-xl bg-espresso-50 p-3 text-sm">
                <div><p class="text-espresso-600">Total per bulan</p><p class="text-lg font-semibold" x-text="'Rp' + total.toLocaleString('id-ID')"></p></div>
                <div><p class="text-espresso-600">Beban per porsi</p><p class="text-lg font-semibold" x-text="servings > 0 ? 'Rp' + perServing.toLocaleString('id-ID') : 'isi perkiraan porsi'"></p></div>
            </div>
        </div>

        {{-- Mode ramai --}}
        <div class="card h-fit space-y-4 p-4 sm:p-6" x-data="{ on: @js($busy) }">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Mode ramai</h2>
                    <p class="text-sm text-espresso-600">Saat dapur penuh pesanan: pelanggan melihat pemberitahuan di menu dan halaman pesanan, dan perkiraan waktu siap ditambah. Bisa juga dinyalakan dari layar kasir.</p>
                </div>
                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                    <input type="checkbox" name="busy_mode" value="1" x-model="on" class="peer sr-only">
                    <span class="h-7 w-12 rounded-full bg-espresso-200 transition peer-checked:bg-amber-500"></span>
                    <span class="absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                </label>
            </div>
            <div>
                <label for="busy_message" class="label">Pesan untuk pelanggan</label>
                <input id="busy_message" name="busy_message" value="{{ old('busy_message', $busyMessage) }}" maxlength="200" class="input" placeholder="{{ \App\Support\Busy::DEFAULT_MESSAGE }}">
            </div>
            <div>
                <label for="busy_extra_minutes" class="label">Tambahan waktu tunggu (menit)</label>
                <input id="busy_extra_minutes" type="number" min="0" max="120" name="busy_extra_minutes" value="{{ old('busy_extra_minutes', $busyExtra) }}" class="input w-32">
            </div>
        </div>

        {{-- Tema warna --}}
        <div class="card space-y-4 p-4 sm:p-6 lg:col-span-2" x-data="{ theme: @js($theme), color: @js($themeColor), presets: @js(\App\Support\Theme::PRESETS) }">
            <div>
                <h2 class="text-lg font-semibold">Tema warna</h2>
                <p class="text-sm text-espresso-600">Berlaku di menu pelanggan, kasir, layar TV, dan panel admin.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach (\App\Support\Theme::PRESETS as $key => $preset)
                    <label class="cursor-pointer">
                        <input type="radio" name="theme" value="{{ $key }}" x-model="theme" class="peer sr-only">
                        <span class="flex items-center gap-3 rounded-xl border-2 border-espresso-100 p-3 text-sm font-semibold transition peer-checked:border-espresso-800 peer-checked:bg-espresso-50">
                            <span class="h-8 w-8 shrink-0 rounded-full ring-2 ring-white" style="background: {{ $preset['color'] }}"></span>{{ $preset['label'] }}
                        </span>
                    </label>
                @endforeach
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="custom" x-model="theme" class="peer sr-only">
                    <span class="flex items-center gap-3 rounded-xl border-2 border-espresso-100 p-3 text-sm font-semibold transition peer-checked:border-espresso-800 peer-checked:bg-espresso-50">
                        <input type="color" name="theme_color" x-model="color" @click="theme = 'custom'" class="h-8 w-8 shrink-0 cursor-pointer rounded-full border-0 bg-transparent p-0">Warna sendiri
                    </span>
                </label>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-espresso-600">
                Pratinjau:
                <template x-for="mix in [8, 20, 40, 70, 100]" :key="mix">
                    <span class="h-6 w-10 rounded" :style="`background: color-mix(in oklab, ${theme === 'custom' ? color : presets[theme].color} ${mix}%, white)`"></span>
                </template>
                <template x-for="mix in [80, 55, 30]" :key="'d' + mix">
                    <span class="h-6 w-10 rounded" :style="`background: color-mix(in oklab, ${theme === 'custom' ? color : presets[theme].color} ${mix}%, black)`"></span>
                </template>
            </div>
        </div>

        {{-- Tombol simpan selalu terlihat, juga di HP --}}
        <div class="sticky bottom-0 z-10 -mx-4 border-t border-espresso-100 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <p class="hidden text-sm text-espresso-600 sm:block">Perubahan baru berlaku setelah disimpan.</p>
                <button class="btn-primary w-full sm:w-auto">Simpan pengaturan</button>
            </div>
        </div>
    </form>

    <form id="tes-wa" method="POST" action="{{ route('admin.settings.whatsapp-test') }}">@csrf</form>
@endsection
