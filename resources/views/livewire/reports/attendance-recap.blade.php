<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Laporan Rekap Absensi</h1>
            <p class="mt-1 text-sm text-gray-600">Filter berdasarkan kegiatan, periode, dan kelas.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" wire:click="exportExcel"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                Export Excel
            </button>
            <a href="{{ $this->pdfUrl() }}" target="_blank" rel="noopener"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                Export PDF
            </a>
        </div>
    </div>

    <x-card>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <select wire:model.live="eventFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua kegiatan</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                @endforeach
            </select>

            <select wire:model.live="classFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>

            <input type="date" wire:model.live="from"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100" />

            <input type="date" wire:model.live="to"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100" />

            <button type="button" wire:click="resetFilters"
                class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                Reset Filter
            </button>
        </div>
    </x-card>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($statuses as $status)
            <x-stat-card :label="ucfirst($status->value)" :value="$summary[$status->value] ?? 0" />
        @endforeach
    </div>

    <x-card title="Rekap per Anggota">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Nama</th>
                        <th class="py-2 pr-4 font-medium">Kelas</th>
                        <th class="py-2 pr-4 font-medium">Hadir</th>
                        <th class="py-2 pr-4 font-medium">Izin</th>
                        <th class="py-2 pr-4 font-medium">Sakit</th>
                        <th class="py-2 pr-4 font-medium">Alpa</th>
                        <th class="py-2 pr-4 font-medium">Terlambat</th>
                        <th class="py-2 pr-4 font-medium">Total</th>
                        <th class="py-2 pr-4 font-medium">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $row)
                        <tr wire:key="recap-row-{{ $loop->index }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $row['member'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['kelas'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['hadir'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['izin'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['sakit'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['alpa'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['terlambat'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['total'] }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $row['persentase'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-empty-state title="Belum ada data absensi" message="Ubah filter atau catat absensi dulu." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
