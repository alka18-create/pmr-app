<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('public.events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-brand-700">
        <x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Semua kegiatan
    </a>

    <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-brand-600">{{ $event['type'] }}</p>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-gray-900">{{ $event['title'] }}</h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="h-4 w-4 text-gray-400" />{{ $event['starts_at'] }}</span>
            @if ($event['location'])
                <span class="inline-flex items-center gap-1.5"><x-icon name="map-pin" class="h-4 w-4 text-gray-400" />{{ $event['location'] }}</span>
            @endif
        </p>
        <p class="mt-3">
            <x-badge :color="$event['status'] === 'published' ? 'green' : 'gray'">{{ $event['status'] }}</x-badge>
        </p>

        @if ($event['description'])
            <div class="mt-5 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $event['description'] }}</div>
        @endif

        @if (! empty($event['documents']))
            <div class="mt-6 border-t border-gray-100 pt-5">
                <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500">
                    <x-icon name="photo" class="h-4 w-4" /> Dokumentasi
                </h2>
                <ul class="mt-3 space-y-1.5 text-sm text-gray-600">
                    @foreach ($event['documents'] as $doc)
                        <li>{{ $doc['caption'] ?? $doc['file_path'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </article>
</div>
