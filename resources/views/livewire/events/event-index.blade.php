<div class="space-y-6">
    <x-page-header
        eyebrow="Kelola"
        title="Kegiatan"
        description="Kelola kegiatan, peserta, dan absensi."
    >
        <x-slot:actions>
            <x-btn href="{{ route('events.create') }}">Kegiatan Baru</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari judul&hellip;"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
            <select wire:model.live="typeFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua jenis</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}">{{ $type->value }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Jenis</th>
                        <th class="py-2 pr-4 font-medium">Waktu</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Peserta</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($events as $event)
                        <tr wire:key="event-{{ $event->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $event->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $event->type->value }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $event->starts_at->format('d M Y H:i') }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$event->status->value === 'published' ? 'green' : ($event->status->value === 'draft' ? 'yellow' : 'gray')">
                                    {{ $event->status->value }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4 text-gray-600">{{ $event->participants_count }}</td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <a href="{{ route('events.show', $event) }}" class="text-sm text-gray-700 underline hover:text-gray-900">Detail</a>
                                <a href="{{ route('events.edit', $event) }}" class="ml-3 text-sm text-gray-700 underline hover:text-gray-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada kegiatan" message="Buat kegiatan pertama Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $events->links() }}</div>
    </x-card>
</div>
