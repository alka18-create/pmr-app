<div class="space-y-6">
    <x-page-header
        eyebrow="Panel"
        title="Dasbor"
        :description="'Selamat datang, '.auth()->user()->name.'.'"
    >
        @can('create', \App\Models\Member::class)
            <x-slot:actions>
                <x-btn href="{{ route('members.create') }}" variant="secondary">Tambah Anggota</x-btn>
                <x-btn href="{{ route('events.create') }}">Buat Kegiatan</x-btn>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Anggota" :value="$totalMembers" icon="users" />
        <x-stat-card label="Kegiatan" :value="$totalEvents" icon="calendar" />
        <x-stat-card label="Kegiatan Mendatang" :value="$upcomingEvents->count()" icon="clock" />
        <x-stat-card label="Pengguna Aktif" :value="\App\Models\User::where('status', \App\Enums\UserStatus::Active->value)->count()" icon="user-circle" />
    </div>

    @if ($memberStats)
        <div class="grid gap-4 lg:grid-cols-2">
            <x-bar-chart title="Anggota per Kelas" :items="$memberStats['per_class']" color="red" />
            <x-bar-chart title="Anggota per Angkatan" :items="$memberStats['per_batch']" color="amber" />
        </div>
    @endif

    <div class="grid items-start gap-4 lg:grid-cols-2">
        <x-card title="Kegiatan Mendatang">
            @forelse ($upcomingEvents as $event)
                <div wire:key="up-{{ $event->id }}" class="flex items-center justify-between gap-3 border-b border-gray-100 py-2.5 last:border-0">
                    <span class="min-w-0 truncate text-sm font-medium text-gray-800">{{ $event->title }}</span>
                    <span class="flex shrink-0 items-center gap-1 text-xs text-gray-500"><x-icon name="calendar" class="h-4 w-4 text-gray-400" />{{ $event->starts_at?->format('d M Y') }}</span>
                </div>
            @empty
                <x-empty-state title="Belum ada kegiatan mendatang" icon="calendar" />
            @endforelse
        </x-card>

    @can('access-pengurus-area')
        <x-card title="Akses Cepat">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @php
                    $shortcuts = [
                        ['label' => 'Anggota', 'route' => 'members.index', 'icon' => 'users'],
                        ['label' => 'Kegiatan', 'route' => 'events.index', 'icon' => 'calendar'],
                        ['label' => 'Buat Quiz', 'route' => 'quizzes.create', 'icon' => 'academic'],
                        ['label' => 'Berita', 'route' => 'articles.index', 'icon' => 'newspaper'],
                        ['label' => 'Inbox', 'route' => 'messages.index', 'icon' => 'envelope'],
                        ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'chart'],
                    ];
                @endphp
                @foreach ($shortcuts as $shortcut)
                    <a
                        href="{{ route($shortcut['route']) }}"
                        class="flex flex-col items-center gap-2 rounded-2xl border border-gray-100 px-3 py-4 text-center transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-icon name="{{ $shortcut['icon'] }}" class="h-5 w-5" />
                        </span>
                        <span class="text-xs font-bold text-gray-800">{{ $shortcut['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </x-card>
    @endcan

    @if ($memberStats)
            <x-card title="Anggota Terbaru">
                @forelse ($recentMembers as $member)
                    <div wire:key="rm-{{ $member->id }}" class="flex items-center gap-3 border-b border-gray-100 py-2.5 text-sm last:border-0">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-extrabold text-brand-700">
                            {{ strtoupper(substr($member->full_name, 0, 1)) }}
                        </span>
                        <span class="min-w-0 truncate font-medium text-gray-800">{{ $member->full_name }}</span>
                    </div>
                @empty
                    <x-empty-state title="Belum ada anggota" icon="users" />
                @endforelse
            </x-card>
        @endif
    </div>
</div>
