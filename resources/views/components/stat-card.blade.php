@props(['label', 'value', 'icon' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $label }}</p>
        @if ($icon)
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>
    <p class="mt-2 text-2xl font-extrabold tracking-tight text-gray-900">{{ $value }}</p>
</div>
