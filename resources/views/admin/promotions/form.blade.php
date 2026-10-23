@extends('layouts.admin')

@section('title', $promotion->exists ? 'Ubah promo' : 'Tambah promo')

@section('content')
    <form method="POST" enctype="multipart/form-data" x-data="{ type: @js(old('discount_type', $promotion->discount_type ?? '')) }"
          action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}"
          class="grid max-w-4xl gap-6 lg:grid-cols-[1fr_300px]">
        @csrf
        @if ($promotion->exists) @method('PUT') @endif

        <div class="card space-y-4 p-6">
            <div>
                <label for="title" class="label">Judul promo</label>
                <input id="title" name="title" value="{{ old('title', $promotion->title) }}" required class="input" placeholder="mis. Happy Hour 15.00–17.00">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description" class="label">Keterangan singkat</label>
                <input id="description" name="description" value="{{ old('description', $promotion->description) }}" maxlength="255" class="input" placeholder="Tampil di banner">
            </div>

            <div class="rounded-xl bg-espresso-50 p-4">
                <label for="discount_type" class="label">Diskon</label>
                <select id="discount_type" name="discount_type" x-model="type" class="input">
                    <option value="">Tanpa diskon (banner saja)</option>
                    @foreach (\App\Models\Promotion::DISCOUNT_TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div x-show="type" x-cloak class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="discount_value" class="label" x-text="type === 'percent' ? 'Besar diskon (%)' : 'Potongan (Rp)'"></label>
                        <input id="discount_value" type="number" min="0" name="discount_value" value="{{ old('discount_value', $promotion->discount_value) }}" class="input">
                        @error('discount_value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="min_purchase" class="label">Min. belanja (Rp)</label>
                        <input id="min_purchase" type="number" min="0" name="min_purchase" value="{{ old('min_purchase', $promotion->min_purchase) }}" class="input">
                    </div>
                    <div>
                        <label for="voucher_code" class="label">Kode voucher</label>
                        <input id="voucher_code" name="voucher_code" value="{{ old('voucher_code', $promotion->voucher_code) }}" class="input uppercase" placeholder="kosong = otomatis">
                        @error('voucher_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="starts_at" class="label">Mulai</label>
                    <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $promotion->starts_at?->format('Y-m-d\TH:i')) }}" class="input">
                </div>
                <div>
                    <label for="ends_at" class="label">Berakhir</label>
                    <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $promotion->ends_at?->format('Y-m-d\TH:i')) }}" class="input">
                    @error('ends_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion->is_active))> Aktif
            </label>

            <div class="flex gap-2">
                <button class="btn-primary">Simpan</button>
                <a href="{{ route('admin.promotions.index') }}" class="btn-ghost">Batal</a>
            </div>
        </div>

        <div class="card h-fit space-y-3 p-6">
            <p class="label">Banner (opsional)</p>
            <div class="aspect-[21/9] overflow-hidden rounded-lg bg-espresso-100">
                <img id="preview" src="{{ $promotion->imageUrl() }}" alt="" class="h-full w-full object-cover {{ $promotion->imageUrl() ? '' : 'hidden' }}">
            </div>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm"
                   onchange="const f=this.files[0]; if(f){const p=document.getElementById('preview'); p.src=URL.createObjectURL(f); p.classList.remove('hidden');}">
            <p class="text-xs text-espresso-600">Rasio lebar 21:9, mis. 1680×720 px. Maks 4 MB.</p>
            @error('image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            @if ($promotion->image_path)
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_image" value="1"> Hapus banner</label>
            @endif
        </div>
    </form>
@endsection
