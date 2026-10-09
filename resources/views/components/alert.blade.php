@props(['type' => 'info'])

@php
    $styles = match ($type) {
        'success' => ['classes' => 'border-green-200 bg-green-50 text-green-800', 'icon' => 'check'],
        'error' => ['classes' => 'border-red-200 bg-red-50 text-red-800', 'icon' => 'alert'],
        default => ['classes' => 'border-blue-200 bg-blue-50 text-blue-800', 'icon' => 'info'],
    };
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {$styles['classes']}"]) }} role="alert">
    <x-icon :name="$styles['icon']" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="min-w-0">{{ $slot }}</div>
</div>
