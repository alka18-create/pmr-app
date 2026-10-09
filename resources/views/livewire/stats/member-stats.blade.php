<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Statistik Anggota</h1>
        <p class="mt-1 text-sm text-gray-600">Distribusi anggota per kelas, angkatan, status, dan jabatan.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Anggota" :value="$stats['total']" />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-bar-chart title="Per Kelas" :items="$stats['per_class']" color="red" />
        <x-bar-chart title="Per Angkatan" :items="$stats['per_batch']" color="amber" />
        <x-bar-chart title="Per Status" :items="$stats['per_status']" color="green" />
        <x-bar-chart title="Per Jabatan" :items="$stats['per_position']" color="blue" />
    </div>
</div>
