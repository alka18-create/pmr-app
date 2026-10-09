@props(['title', 'message' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'py-10 text-center']) }}>
    @if ($icon)
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
            <x-icon :name="$icon" class="h-6 w-6" />
        </span>
    @endif
    <p class="mt-3 text-sm font-semibold text-gray-700">{{ $title }}</p>
    @if ($message)
        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
