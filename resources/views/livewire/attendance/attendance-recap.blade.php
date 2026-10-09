<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Rekap Absensi</h1>
        <p class="mt-1 text-sm text-gray-600">Rekap absensi per kegiatan dan anggota.</p>
    </div>

    <x-card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2">
            <select wire:model.live="memberFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua anggota</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}">{{ $member->full_name }}</option>
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
                        <th class="py-2 pr-4 font-medium">Anggota</th>
                        <th class="py-2 pr-4 font-medium">Kegiatan</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Metode</th>
                        <th class="py-2 pr-4 font-medium">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $record)
                        <tr wire:key="recap-{{ $record->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $record->member?->full_name }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $record->event?->title }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$record->status->value === 'hadir' ? 'green' : 'yellow'">
                                    {{ $record->status->value }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4 text-gray-600">{{ $record->method->value }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $record->checked_in_at?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Belum ada data absensi" message="Ubah filter atau catat absensi dulu." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $records->links() }}</div>
    </x-card>
</div>
