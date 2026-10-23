@props(['ingredient'])

{{-- Catat perubahan stok satu bahan: beli, hitung fisik, atau terbuang. Dipakai di Stok bahan dan Resep & stok. --}}
<form method="POST" action="{{ route('admin.ingredients.move', $ingredient) }}" x-data="{ type: 'purchase' }" {{ $attributes->merge(['class' => 'flex flex-wrap items-end gap-3']) }}>
    @csrf
    <div>
        <label class="label">Jenis</label>
        <select name="type" x-model="type" class="input">
            <option value="purchase">Stok masuk (beli)</option>
            <option value="adjustment">Ubah ke stok fisik (hitung ulang)</option>
            <option value="waste">Terbuang / rusak</option>
        </select>
    </div>
    <div>
        <label class="label" x-text="type === 'adjustment' ? 'Stok sebenarnya sekarang ({{ $ingredient->unit }})' : 'Jumlah ({{ $ingredient->unit }})'"></label>
        <input type="number" step="0.01" min="0" name="quantity" required class="input w-40">
    </div>
    <div x-show="type === 'purchase'">
        <label class="label">Total harga beli (Rp)</label>
        <input type="number" min="0" name="total_cost" class="input w-40" :required="type === 'purchase'">
    </div>
    <div class="min-w-48 flex-1">
        <label class="label">Catatan</label>
        <input name="note" maxlength="150" class="input" placeholder="mis. beli di supplier A">
    </div>
    <button class="btn-primary">Simpan</button>
</form>
