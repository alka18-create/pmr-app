<div class="space-y-6">
    <x-page-header
        eyebrow="Keluarga Besar"
        title="Direktori Anggota"
        description="Data publik anggota PMR — tanpa data internal."
    />

    <x-card>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <label for="memberSearch" class="sr-only">Cari nama</label>
                <input
                    id="memberSearch"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama&hellip;"
                    class="w-full rounded-xl border border-gray-300 py-2.5 pl-9 pr-3 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
            </div>
            <div>
                <label for="classFilter" class="sr-only">Filter kelas</label>
                <select
                    id="classFilter"
                    wire:model.live="classFilter"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="batchFilter" class="sr-only">Filter angkatan</label>
                <select
                    id="batchFilter"
                    wire:model.live="batchFilter"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">Semua angkatan</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="statusFilter" class="sr-only">Filter status</label>
                <select
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">Semua status</option>
                    @foreach ($visibleStatuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($members as $member)
                <a
                    wire:key="public-member-{{ $member['slug'] }}"
                    href="{{ route('public.members.show', $member['slug']) }}"
                    class="group block rounded-2xl border border-gray-200 p-4 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md"
                >
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-base font-extrabold text-brand-700">
                            {{ strtoupper(substr($member['full_name'], 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-gray-900 group-hover:text-brand-700">{{ $member['full_name'] }}</p>
                            <p class="mt-0.5 text-sm text-gray-600">{{ $member['school_class']['name'] ?? '—' }}</p>
                            <p class="text-sm text-gray-600">Angkatan {{ $member['batch']['name'] ?? '—' }}</p>
                            <p class="text-sm text-gray-600">{{ $member['position']['name'] ?? '—' }}</p>
                            <span class="mt-2 inline-block">
                                <x-badge :color="$member['status'] === 'aktif' ? 'green' : 'yellow'">
                                    {{ $member['status'] }}
                                </x-badge>
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-empty-state title="Tidak ada anggota" message="Ubah filter atau kata kunci pencarian." icon="users" />
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $members->links() }}
        </div>
    </x-card>
</div>
