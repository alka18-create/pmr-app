<div class="space-y-6">
    <x-page-header
        eyebrow="Kelola"
        title="Daftar Anggota"
        description="Kelola data anggota PMR (data internal terlindungi)."
    >
        <x-slot:actions>
            <x-btn href="{{ route('members.import') }}" variant="secondary">Import</x-btn>
            <x-btn href="{{ route('members.export') }}" variant="secondary">Export</x-btn>
            <x-btn href="{{ route('members.create') }}">Tambah Anggota</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama / NIS / NISN&hellip;"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
            <select
                wire:model.live="classFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="batchFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua angkatan</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="statusFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-lg bg-gray-50 px-3 py-2">
            <span class="text-xs text-gray-500">Aksi massal angkatan terpilih (pakai filter Angkatan di atas):</span>
            <button
                type="button"
                wire:click="markBatchAsAlumni"
                wire:confirm="Tandai SELURUH anggota aktif di angkatan terpilih sebagai alumni?"
                class="rounded-lg border border-yellow-300 bg-yellow-50 px-3 py-1.5 text-sm font-medium text-yellow-800 hover:bg-yellow-100"
            >Tandai Alumni</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Nama</th>
                        <th class="py-2 pr-4 font-medium">Kelas</th>
                        <th class="py-2 pr-4 font-medium">Angkatan</th>
                        <th class="py-2 pr-4 font-medium">Jabatan</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($members as $member)
                        <tr wire:key="member-{{ $member->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $member->full_name }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $member->schoolClass?->name }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $member->batch?->name }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $member->position?->name ?? '—' }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$member->status->value === 'aktif' ? 'green' : 'yellow'">
                                    {{ $member->status->value }}
                                </x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <a href="{{ route('members.show', $member) }}" class="text-sm text-gray-700 underline hover:text-gray-900">Detail</a>
                                <a href="{{ route('members.edit', $member) }}" class="ml-3 text-sm text-gray-700 underline hover:text-gray-900">Edit</a>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $member->id }})"
                                    wire:confirm="Ubah status anggota ini?"
                                    class="ml-3 text-sm text-yellow-700 underline hover:text-yellow-900"
                                >{{ $member->status->value === 'aktif' ? 'Nonaktifkan' : ($member->status->value === 'alumni' ? 'Kembalikan ke Aktif' : 'Aktifkan') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Tidak ada anggota" message="Ubah filter atau tambah anggota baru." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $members->links() }}
        </div>
    </x-card>
</div>
