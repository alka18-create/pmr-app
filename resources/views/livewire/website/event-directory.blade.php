<div class="space-y-6">
    <x-page-header
        eyebrow="Agenda"
        title="Kegiatan"
        description="Agenda kegiatan PMR yang dipublikasikan."
    />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($events as $event)
            <a wire:key="pub-event-{{ $event->id }}" href="{{ route('public.events.show', $event->slug) }}"
                class="group flex gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <span class="text-lg font-extrabold leading-none">{{ $event->starts_at->format('d') }}</span>
                    <span class="text-[11px] font-bold uppercase">{{ $event->starts_at->format('M') }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-600">{{ $event->type->value }}</p>
                    <h2 class="mt-0.5 font-bold text-gray-900 group-hover:text-brand-700">{{ $event->title }}</h2>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-600"><x-icon name="clock" class="h-4 w-4 shrink-0 text-gray-400" />{{ $event->starts_at->format('d M Y H:i') }}</p>
                    @if ($event->location)
                        <p class="mt-0.5 flex items-center gap-1.5 truncate text-sm text-gray-600"><x-icon name="map-pin" class="h-4 w-4 shrink-0 text-gray-400" />{{ $event->location }}</p>
                    @endif
                    <span class="mt-3 inline-block">
                        <x-badge :color="$event->status->value === 'published' ? 'green' : 'gray'">{{ $event->status->value }}</x-badge>
                    </span>
                </div>
            </a>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-card>
                    <x-empty-state title="Belum ada kegiatan" message="Kegiatan publik akan muncul di sini." icon="calendar" />
                </x-card>
            </div>
        @endforelse
    </div>

    <div>{{ $events->links() }}</div>
</div>
