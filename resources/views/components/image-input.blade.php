@props(['name' => 'image', 'url' => null, 'aspect' => 'aspect-[4/3]', 'hint' => null])

{{-- Pemilih foto: area pratinjau yang bisa diketuk plus tombol yang jelas, karena input file bawaan browser tampil polos. --}}
<div x-data="{ preview: @js($url), fileName: '' }" class="space-y-3">
    <label class="group relative block cursor-pointer overflow-hidden rounded-xl border-2 border-dashed border-espresso-200 bg-espresso-50 transition hover:border-espresso-500 {{ $aspect }}">
        <img x-show="preview" :src="preview" alt="" class="absolute inset-0 h-full w-full object-cover" @if (! $url) style="display:none" @endif>
        <span x-show="!preview" class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-4 text-center text-espresso-600" @if ($url) style="display:none" @endif>
            <svg class="h-10 w-10 text-espresso-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/></svg>
            <span class="text-sm font-semibold">Ketuk untuk memilih foto</span>
        </span>
        <input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="sr-only"
               @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); fileName = f.name }">
    </label>

    <div class="flex flex-wrap items-center gap-2">
        <button type="button" class="btn-ghost" @click="$el.closest('[x-data]').querySelector('input[type=file]').click()">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 16V4m0 0-4 4m4-4 4 4M4 20h16"/></svg>
            <span x-text="preview ? 'Ganti foto' : 'Pilih foto'">Pilih foto</span>
        </button>
        <span x-show="fileName" x-text="fileName" class="min-w-0 flex-1 truncate text-xs text-espresso-600"></span>
    </div>

    @if ($hint)<p class="text-xs text-espresso-600">{{ $hint }}</p>@endif
    @error($name) <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>
