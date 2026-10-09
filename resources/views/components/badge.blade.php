@props(['color' => 'gray'])

@php
    $colors = match ($color) {
        'red' => 'bg-red-100 text-red-700',
        'green' => 'bg-green-100 text-green-700',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'blue' => 'bg-blue-100 text-blue-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {$colors}"]) }}>{{ $slot }}</span>
