{{-- Logo kafe dari Pengaturan; bila belum diunggah tampil isi slot (ikon bawaan). --}}
@php $logo = \App\Support\Tenant::cafe()?->logoUrl(); @endphp
@if ($logo)
    <img src="{{ $logo }}" alt="Logo {{ \App\Support\Tenant::cafe()->name }}" {{ $attributes->merge(['class' => 'shrink-0 bg-white object-contain p-0.5']) }}>
@else
    {{ $slot }}
@endif
