@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-100 bg-white p-6 shadow-sm']) }}>
    @if ($title)
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-500">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>
