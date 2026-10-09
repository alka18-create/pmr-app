<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Laporan Gabungan</h1>
        <p class="mt-1 text-sm text-gray-600">Pilih jenis laporan, generate preview, lalu export.</p>
    </div>

    <x-card>
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Jenis Laporan</label>
                <select wire:model.live="reportType"
                    class="mt-1 w-56 rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="absensi">Rekap Absensi</option>
                    <option value="anggota">Statistik Anggota</option>
                    <option value="kegiatan">Statistik Kegiatan</option>
                </select>
            </div>

            <button type="button" wire:click="generate"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                Generate
            </button>

            @if ($reportType === 'absensi')
                <button type="button" wire:click="exportExcel"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Export Excel
                </button>
                <a href="{{ route('reports.attendance.pdf') }}" target="_blank" rel="noopener"
                    class="rounded-xl border border-brand-600 px-4 py-2 text-sm font-medium text-brand-700 hover:bg-brand-50">
                    Export PDF
                </a>
            @endif
        </div>
    </x-card>

    @if ($generated)
        @if ($reportType === 'absensi' && $attendanceSummary)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($attendanceSummary as $status => $total)
                    <x-stat-card :label="ucfirst($status)" :value="$total" />
                @endforeach
            </div>

            <x-card title="Detail Rekap Absensi">
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
                                <th class="py-2 pr-4 font-medium">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($attendanceRows as $row)
                                <tr wire:key="report-row-{{ $loop->index }}" class="transition hover:bg-gray-50">
                                    <td class="py-3 pr-4 font-medium text-gray-900">{{ $row['member'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['kelas'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['hadir'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['izin'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['sakit'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['alpa'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['persentase'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><x-empty-state title="Belum ada data absensi" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @elseif ($reportType === 'anggota' && $memberStats)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card label="Total Anggota" :value="$memberStats['total']" />
            </div>
            <div class="grid gap-4 lg:grid-cols-2">
                <x-bar-chart title="Per Kelas" :items="$memberStats['per_class']" color="red" />
                <x-bar-chart title="Per Angkatan" :items="$memberStats['per_batch']" color="amber" />
                <x-bar-chart title="Per Status" :items="$memberStats['per_status']" color="green" />
                <x-bar-chart title="Per Jabatan" :items="$memberStats['per_position']" color="blue" />
            </div>
        @elseif ($reportType === 'kegiatan' && $eventStats)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card label="Total Kegiatan" :value="$eventStats['total']" />
                <x-stat-card label="Total Peserta" :value="$eventStats['total_participants']" />
                <x-stat-card label="Catatan Absensi" :value="$eventStats['total_attendance']" />
                <x-stat-card label="Rasio Hadir (%)" :value="$eventStats['attendance_ratio']" />
            </div>
            <div class="grid gap-4 lg:grid-cols-2">
                <x-bar-chart title="Kegiatan per Bulan" :items="$eventStats['per_month']" color="red" />
                <x-bar-chart title="Kegiatan per Jenis" :items="$eventStats['per_type']" color="blue" />
            </div>
        @endif
    @else
        <x-card>
            <x-empty-state title="Pilih jenis laporan lalu klik Generate" message="Preview akan tampil di sini." />
        </x-card>
    @endif
</div>
