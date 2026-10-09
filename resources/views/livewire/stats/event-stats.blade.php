<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Statistik Kegiatan</h1>
            <p class="mt-1 text-sm text-gray-600">Jumlah kegiatan per bulan/jenis, peserta, dan rasio kehadiran.</p>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Tahun</label>
            <input type="number" wire:model.live="year" min="2000" max="2100"
                class="w-28 rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Kegiatan" :value="$stats['total']" />
        <x-stat-card label="Total Peserta" :value="$stats['total_participants']" />
        <x-stat-card label="Catatan Absensi" :value="$stats['total_attendance']" />
        <x-stat-card label="Rasio Hadir (%)" :value="$stats['attendance_ratio']" />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-bar-chart title="Kegiatan per Bulan ({{ $year }})" :items="$stats['per_month']" color="red" />
        <x-bar-chart title="Kegiatan per Jenis" :items="$stats['per_type']" color="blue" />
    </div>
</div>
