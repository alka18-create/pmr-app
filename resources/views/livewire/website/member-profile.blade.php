<div class="mx-auto max-w-3xl space-y-6">
    <a
        href="{{ route('public.members.index') }}"
        class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-brand-700"
    ><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Kembali ke direktori</a>

    <x-card>
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-brand-50 text-2xl font-extrabold text-brand-700">
                {{ strtoupper(substr($member['full_name'], 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1 space-y-4">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-gray-900">{{ $member['full_name'] }}</h1>
                    <p class="mt-2">
                        <x-badge :color="$member['status'] === 'aktif' ? 'green' : 'yellow'">
                            {{ $member['status'] }}
                        </x-badge>
                    </p>
                </div>

                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Kelas</dt>
                        <dd class="text-right font-semibold text-gray-900">{{ $member['school_class']['name'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Angkatan</dt>
                        <dd class="text-right font-semibold text-gray-900">{{ $member['batch']['name'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Jabatan</dt>
                        <dd class="text-right font-semibold text-gray-900">{{ $member['position']['name'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Anggota sejak</dt>
                        <dd class="text-right font-semibold text-gray-900">
                            {{ $member['joined_at'] ? \Illuminate\Support\Carbon::parse($member['joined_at'])->format('d M Y') : '—' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </x-card>
</div>
