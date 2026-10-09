<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $member->full_name }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                Detail anggota
                @if ($showInternal) — <span class="font-medium text-brand-700">data internal terlihat</span> @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('members.index') }}" class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
            @if ($showInternal)
                <a href="{{ route('members.edit', $member) }}" class="rounded-xl bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Edit</a>
                <button
                    type="button"
                    wire:click="purgePersonalData"
                    wire:confirm="Hapus (anonimkan) SELURUH data pribadi anggota ini? Tindakan tidak dapat dibatalkan."
                    class="rounded-xl border border-brand-300 px-3 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50"
                >Hapus Data Pribadi</button>
            @endif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card title="Data Publik">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Nama</dt>
                    <dd class="font-medium text-gray-900">{{ $member->full_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Kelas</dt>
                    <dd class="font-medium text-gray-900">{{ $member->schoolClass?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Angkatan</dt>
                    <dd class="font-medium text-gray-900">{{ $member->batch?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Jabatan</dt>
                    <dd class="font-medium text-gray-900">{{ $member->position?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <x-badge :color="$member->status->value === 'aktif' ? 'green' : 'yellow'">
                            {{ $member->status->value }}
                        </x-badge>
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Anggota sejak</dt>
                    <dd class="font-medium text-gray-900">{{ $member->joined_at?->format('d M Y') }}</dd>
                </div>
            </dl>
        </x-card>

        @if ($showInternal)
            <x-card title="Data Internal (RAHASIA)">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">NIS</dt>
                        <dd class="font-medium text-gray-900">{{ $member->nis ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">NISN</dt>
                        <dd class="font-medium text-gray-900">{{ $member->nisn ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Tgl lahir</dt>
                        <dd class="font-medium text-gray-900">{{ $member->birth_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Tempat lahir</dt>
                        <dd class="font-medium text-gray-900">{{ $member->birth_place ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">No. HP</dt>
                        <dd class="font-medium text-gray-900">{{ $member->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900">{{ $member->email_pribadi ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Orang tua</dt>
                        <dd class="font-medium text-gray-900">{{ $member->parent_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">HP orang tua</dt>
                        <dd class="font-medium text-gray-900">{{ $member->parent_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Alamat</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $member->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Catatan</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $member->notes ?? '—' }}</dd>
                    </div>
                </dl>
            </x-card>
        @endif
    </div>
</div>
