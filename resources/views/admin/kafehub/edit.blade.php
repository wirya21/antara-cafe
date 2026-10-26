@extends('layouts.admin')

@section('title', 'Halaman kafehub.id')

@php
    $text = fn ($key, $label, $hint = null, $long = false) => compact('key', 'label', 'hint', 'long');
    $sections = [
        ['Bagian atas', 'Sapaan pertama yang dilihat calon pengguna.', [
            $text('hero_badge', 'Label kecil di atas judul'),
            $text('hero_title', 'Judul baris pertama'),
            $text('hero_highlight', 'Judul baris kedua (warna oranye)'),
            $text('hero_subtitle', 'Kalimat penjelas', null, true),
        ], [['stats', 'Angka singkat', ['label' => ['Label', 'text'], 'value' => ['Isi', 'text']]]]],
        ['Masalah yang diselesaikan', 'Baris coret (masalah) dan jawabannya.', [], [['pains', 'Masalah', ['pain' => ['Masalah', 'text'], 'fix' => ['Jawaban kafehub', 'text']]]]],
        ['Fitur', 'Kartu fitur dengan ikon garis.', [
            $text('features_title', 'Judul'),
            $text('features_subtitle', 'Penjelasan', null, true),
        ], [['features', 'Fitur', ['icon' => ['Ikon', 'icon'], 'title' => ['Judul', 'text'], 'text' => ['Isi', 'textarea']]]]],
        ['Alur pesanan', 'Langkah bernomor 01, 02, dan seterusnya di bagian gelap.', [
            $text('flow_title', 'Judul'),
        ], [['flow', 'Langkah', ['icon' => ['Ikon', 'icon'], 'title' => ['Judul', 'text'], 'text' => ['Isi', 'textarea']]]]],
        ['Kenapa kafehub', 'Alasan memilih kafehub.', [
            $text('reasons_title', 'Judul'),
            $text('reasons_subtitle', 'Penjelasan', null, true),
        ], [['reasons', 'Alasan', ['icon' => ['Ikon', 'icon'], 'title' => ['Judul', 'text'], 'text' => ['Isi', 'textarea']]]]],
        ['Cara mulai', 'Langkah untuk bergabung.', [
            $text('start_title', 'Judul'),
        ], [['start', 'Langkah', ['title' => ['Judul', 'text'], 'text' => ['Isi', 'textarea']]]]],
        ['Sudah dipakai', 'Kafe yang sudah memakai kafehub. Kafe pertama juga dipakai untuk tombol "Coba demo".', [
            $text('showcase_title', 'Judul'),
        ], [['showcase', 'Kafe', ['name' => ['Nama kafe', 'text'], 'url' => ['Alamat (https://…)', 'url'], 'text' => ['Keterangan', 'textarea']]]]],
        ['Tanya jawab', null, [], [['faqs', 'Pertanyaan', ['q' => ['Pertanyaan', 'text'], 'a' => ['Jawaban', 'textarea']]]]],
        ['Ajakan akhir', 'Kotak oranye di bagian bawah.', [
            $text('cta_title', 'Judul'),
            $text('cta_subtitle', 'Kalimat', null, true),
        ], []],
    ];
@endphp

@section('content')
    <form method="POST" action="{{ route('admin.kafehub.update') }}" class="max-w-4xl space-y-6 pb-24">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="card flex flex-wrap items-center justify-between gap-3 p-4 sm:p-6">
            <div>
                <p class="text-sm text-espresso-600">Isi halaman depan <b>kafehub.id</b> untuk calon pengguna. Kosongkan kolom untuk memakai teks bawaan.</p>
            </div>
            <a href="{{ route('kafehub') }}" target="_blank" class="btn-ghost">Lihat halaman ↗</a>
        </div>

        <div class="card space-y-3 p-4 sm:p-6">
            <h2 class="text-lg font-semibold">Kontak</h2>
            <div class="max-w-sm">
                <label for="whatsapp" class="label">Nomor WhatsApp untuk tombol "Gabung"</label>
                <input id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $c['whatsapp']) }}" inputmode="tel" class="input" placeholder="08xxxxxxxxxx">
                <p class="mt-1 text-xs text-espresso-500">Bisa ditulis 08… atau 628…. Kosongkan untuk memakai nomor bawaan.</p>
            </div>
        </div>

        @foreach ($sections as [$title, $hint, $texts, $lists])
            <div class="card space-y-4 p-4 sm:p-6">
                <div>
                    <h2 class="text-lg font-semibold">{{ $title }}</h2>
                    @if ($hint)<p class="text-sm text-espresso-600">{{ $hint }}</p>@endif
                </div>
                @foreach ($texts as $field)
                    <div>
                        <label for="{{ $field['key'] }}" class="label">{{ $field['label'] }}</label>
                        @if ($field['long'])
                            <textarea id="{{ $field['key'] }}" name="{{ $field['key'] }}" rows="2" maxlength="300" class="input">{{ old($field['key'], $c[$field['key']]) }}</textarea>
                        @else
                            <input id="{{ $field['key'] }}" name="{{ $field['key'] }}" value="{{ old($field['key'], $c[$field['key']]) }}" maxlength="120" class="input">
                        @endif
                    </div>
                @endforeach
                @foreach ($lists as [$name, $label, $fields])
                    @include('admin.kafehub.list', ['name' => $name, 'label' => $label, 'fields' => $fields, 'items' => $c[$name]])
                @endforeach
            </div>
        @endforeach

        <div class="sticky bottom-0 z-10 -mx-4 flex items-center justify-between gap-3 border-t border-espresso-100 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
            <p class="text-sm text-espresso-600">Perubahan tampil di kafehub.id setelah disimpan.</p>
            <button class="btn-primary">Simpan halaman</button>
        </div>
    </form>
@endsection
