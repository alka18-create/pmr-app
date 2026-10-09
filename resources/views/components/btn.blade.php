@props(['href' => null, 'variant' => 'primary', 'type' => 'submit'])

{{--
    Tombol/link konsisten. Varian: primary, secondary, light,
    outline-light, ghost.
    Pakai: <x-btn href="...">Simpan</x-btn> atau <x-btn>Simpan</x-btn>
--}}

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50',
        'light' => 'bg-white text-brand-700 shadow-sm hover:bg-brand-50',
        'outline-light' => 'border border-white/70 text-white hover:bg-white/10',
        'ghost' => 'text-brand-700 hover:bg-brand-50',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
