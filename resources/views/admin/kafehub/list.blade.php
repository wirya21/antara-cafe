{{-- Daftar yang bisa ditambah, dihapus, dan diurutkan. $fields: kunci => [label, jenis: text|textarea|icon|url] --}}
<div x-data="{ items: @js(array_values($items)) }" class="space-y-3">
    <template x-for="(item, i) in items" :key="i">
        <div class="rounded-xl border border-espresso-100 bg-espresso-50/50 p-3">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs font-semibold text-espresso-600" x-text="'{{ $label }} ' + (i + 1)"></span>
                <div class="flex items-center gap-1 text-sm">
                    <button type="button" class="rounded px-2 py-0.5 hover:bg-white disabled:opacity-30" :disabled="i === 0" @click="items.splice(i - 1, 0, items.splice(i, 1)[0])" title="Naikkan">↑</button>
                    <button type="button" class="rounded px-2 py-0.5 hover:bg-white disabled:opacity-30" :disabled="i === items.length - 1" @click="items.splice(i + 1, 0, items.splice(i, 1)[0])" title="Turunkan">↓</button>
                    <button type="button" class="rounded px-2 py-0.5 text-red-700 hover:bg-red-50" @click="items.splice(i, 1)">Hapus</button>
                </div>
            </div>
            <div class="grid gap-2 {{ count($fields) > 2 ? 'sm:grid-cols-[10rem_1fr]' : '' }}">
                @foreach ($fields as $key => [$fieldLabel, $type])
                    <div class="{{ $type === 'textarea' && count($fields) > 2 ? 'sm:col-span-2' : '' }}">
                        <label class="mb-0.5 block text-xs text-espresso-600">{{ $fieldLabel }}</label>
                        @if ($type === 'icon')
                            <select class="input py-1.5" x-model="item.{{ $key }}" :name="`{{ $name }}[${i}][{{ $key }}]`">
                                @foreach ($icons as $value => $iconLabel)
                                    <option value="{{ $value }}">{{ $iconLabel }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'textarea')
                            <textarea rows="2" class="input py-1.5" x-model="item.{{ $key }}" :name="`{{ $name }}[${i}][{{ $key }}]`"></textarea>
                        @else
                            <input type="{{ $type === 'url' ? 'url' : 'text' }}" class="input py-1.5" x-model="item.{{ $key }}" :name="`{{ $name }}[${i}][{{ $key }}]`">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </template>
    <input type="hidden" name="{{ $name }}" value="" x-show="false" x-bind:disabled="items.length > 0">
    <button type="button" class="btn-ghost w-full" @click="items.push(@js(array_map(fn ($f) => $f[1] === 'icon' ? 'bintang' : '', $fields)))">+ Tambah {{ strtolower($label) }}</button>
</div>
