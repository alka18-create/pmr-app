<div class="space-y-6">
    <x-page-header
        eyebrow="Admin"
        title="Area Admin"
        description="Ringkasan sistem: pengguna, data master, dan aktivitas terbaru."
    >
        <x-slot:actions>
            <x-btn href="{{ route('admin.users.create') }}" variant="secondary">Tambah Pengguna</x-btn>
            <x-btn href="{{ route('admin.audit-log.index') }}">Lihat Audit Log</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Pengguna" :value="$totalUsers" icon="user-circle" />
        <x-stat-card label="Pengguna Aktif" :value="$activeUsers" icon="check" />
        <x-stat-card label="Nonaktif" :value="$inactiveUsers" icon="alert" />
        <x-stat-card label="Angkatan · Kelas · Jabatan" :value="collect($masterCounts)->sum()" icon="cog" />
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-2">
        <x-card title="Pengguna Terbaru">
            @forelse ($newestUsers as $user)
                <div wire:key="aa-user-{{ $user->id }}" class="flex items-center justify-between gap-3 border-b border-gray-100 py-2.5 last:border-0">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-extrabold text-brand-700">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-gray-800">{{ $user->name }}</span>
                            <span class="block truncate text-xs text-gray-500">{{ $user->email }}</span>
                        </span>
                    </span>
                    <x-badge :color="$user->status->value === 'active' ? 'green' : 'yellow'">
                        {{ $this->roleLabel($user->role->value) }}
                    </x-badge>
                </div>
            @empty
                <x-empty-state title="Belum ada pengguna" icon="user-circle" />
            @endforelse
            <div class="mt-4 border-t border-gray-100 pt-3">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">
                    Kelola pengguna <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        </x-card>

        <x-card title="Aktivitas Terbaru">
            @forelse ($latestLogs as $log)
                <div wire:key="aa-log-{{ $log->id }}" class="flex items-start justify-between gap-3 border-b border-gray-100 py-2.5 last:border-0">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-gray-800">{{ $log->user?->name ?? 'Sistem' }}</span>
                        <span class="block truncate text-xs text-gray-500">{{ $log->action }}@if ($log->model_type) · {{ class_basename($log->model_type) }} @endif</span>
                    </span>
                    <span class="shrink-0 text-xs text-gray-500">{{ $log->created_at?->format('d M H:i') }}</span>
                </div>
            @empty
                <x-empty-state title="Belum ada aktivitas tercatat" icon="document" />
            @endforelse
            <div class="mt-4 border-t border-gray-100 pt-3">
                <a href="{{ route('admin.audit-log.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">
                    Buka audit log <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        </x-card>
    </div>

    <x-card title="Data Master">
        <div class="flex flex-wrap gap-2">
            @foreach ($masterCounts as $label => $total)
                <a href="{{ route('admin.master.batches') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-brand-200 hover:text-brand-700">
                    {{ $label }} <span class="rounded-full bg-brand-50 px-2 py-0.5 text-brand-700">{{ $total }}</span>
                </a>
            @endforeach
        </div>
    </x-card>
</div>
