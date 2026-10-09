<div class="space-y-6">
    <x-page-header
        eyebrow="Kelola"
        title="Area Pengurus"
        description="Ringkasan operasional: anggota, kegiatan, pesan masuk, dan akademi."
    >
        <x-slot:actions>
            <x-btn href="{{ route('members.create') }}" variant="secondary">Tambah Anggota</x-btn>
            <x-btn href="{{ route('events.create') }}">Buat Kegiatan</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Anggota Aktif" :value="$activeMembers" icon="users" />
        <x-stat-card label="Kegiatan Mendatang" :value="$upcomingCount" icon="calendar" />
        <x-stat-card label="Pesan Belum Dibaca" :value="$unreadMessages" icon="envelope" />
        <x-stat-card label="Quiz Aktif" :value="$activeQuizzes" icon="academic" />
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-2">
        <x-card title="Kegiatan Mendatang">
            @forelse ($upcomingEvents as $event)
                <a wire:key="pa-up-{{ $event->id }}" href="{{ route('events.show', $event->id) }}"
                    class="group flex items-center justify-between gap-3 border-b border-gray-100 py-2.5 last:border-0">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-gray-800 group-hover:text-brand-700">{{ $event->title }}</span>
                        @if ($event->location)
                            <span class="block truncate text-xs text-gray-500">{{ $event->location }}</span>
                        @endif
                    </span>
                    <span class="flex shrink-0 items-center gap-1 text-xs text-gray-500">
                        <x-icon name="calendar" class="h-4 w-4 text-gray-400" />{{ $event->starts_at?->format('d M Y') }}
                    </span>
                </a>
            @empty
                <x-empty-state title="Belum ada kegiatan mendatang" icon="calendar" />
            @endforelse
        </x-card>

        <x-card title="Pesan Masuk Terbaru">
            @forelse ($latestMessages as $message)
                <a wire:key="pa-msg-{{ $message->id }}" href="{{ route('messages.index') }}"
                    class="group flex items-start justify-between gap-3 border-b border-gray-100 py-2.5 last:border-0">
                    <span class="min-w-0">
                        <span class="flex items-center gap-2 text-sm font-medium {{ $message->status->value === 'unread' ? 'text-gray-900' : 'text-gray-600' }}">
                            {{ $message->subject ?: '(Tanpa subjek)' }}
                            @if ($message->status->value === 'unread')
                                <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-brand-600" title="Belum dibaca"></span>
                            @endif
                        </span>
                        <span class="block truncate text-xs text-gray-500">dari {{ $message->name }}</span>
                    </span>
                    <span class="shrink-0 text-xs text-gray-500">{{ $message->created_at?->format('d M Y') }}</span>
                </a>
            @empty
                <x-empty-state title="Belum ada pesan masuk" icon="envelope" />
            @endforelse
        </x-card>
    </div>

    @if ($draftEvents->isNotEmpty())
        <x-card title="Draf Kegiatan — perlu ditindaklanjuti">
            <div class="flex flex-wrap gap-2">
                @foreach ($draftEvents as $event)
                    <a wire:key="pa-draft-{{ $event->id }}" href="{{ route('events.edit', $event->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-full border border-yellow-300 bg-yellow-50 px-3 py-1.5 text-xs font-semibold text-yellow-800 transition hover:bg-yellow-100">
                        <x-icon name="document" class="h-4 w-4" />{{ $event->title }}
                    </a>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
