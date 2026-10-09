@props(['title' => null, 'items' => [], 'color' => 'red'])

@php
    $max = collect($items)->max('value') ?: 1;
    $bar = match ($color) {
        'green' => 'bg-green-500',
        'blue' => 'bg-blue-500',
        'amber' => 'bg-amber-500',
        default => 'bg-red-500',
    };
@endphp

<x-card :title="$title">
    @forelse ($items as $item)
        <div wire:key="bar-{{ $loop->index }}" class="mb-2">
            <div class="mb-1 flex items-center justify-between text-xs text-gray-600">
                <span>{{ $item['label'] }}</span>
                <span class="font-medium text-gray-900">{{ $item['value'] }}</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="{{ $bar }} h-2 rounded-full" style="width: {{ max(2, round($item['value'] / $max * 100)) }}%"></div>
            </div>
        </div>
    @empty
        <x-empty-state title="Belum ada data" />
    @endforelse
</x-card>
