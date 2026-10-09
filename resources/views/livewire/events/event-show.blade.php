<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $event->title }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $event->type->value }} · {{ $event->starts_at->format('d M Y H:i') }}
                @if ($event->location) · {{ $event->location }} @endif
            </p>
            <p class="mt-2">
                <x-badge :color="$event->status->value === 'published' ? 'green' : ($event->status->value === 'draft' ? 'yellow' : 'gray')">
                    {{ $event->status->value }}
                </x-badge>
                @if ($event->is_public)
                    <x-badge color="green">publik</x-badge>
                @else
                    <x-badge color="gray">internal</x-badge>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('events.index') }}" class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
            <a href="{{ route('events.edit', $event) }}" class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Edit</a>
            <button type="button" wire:click="delete" wire:confirm="Hapus kegiatan ini?"
                class="rounded-xl border border-brand-300 px-3 py-1.5 text-sm text-brand-700 hover:bg-brand-50">Hapus</button>
        </div>
    </div>

    @if ($event->description)
        <x-card title="Deskripsi">
            <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $event->description }}</p>
        </x-card>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card title="Peserta ({{ $event->participants_count }})">
            <div class="mb-4 space-y-2">
                <div class="flex gap-2">
                    <select wire:model="memberSearch" class="flex-1 rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">Pilih anggota…</option>
                        @foreach ($members as $member)
                            @if (! $participantMemberIds->contains($member->id))
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" wire:click="addParticipant"
                        class="rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Tambah</button>
                </div>
                <div class="flex gap-2">
                    <select wire:model="bulkClassId" class="flex-1 rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">Tambah semua kelas…</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="addParticipantsByClass"
                        class="rounded-xl border border-brand-600 px-3 py-2 text-sm font-medium text-brand-700 hover:bg-brand-50">Tambah Kelas</button>
                </div>
            </div>

            <div class="divide-y divide-gray-100 text-sm">
                @forelse ($participants as $participant)
                    <div wire:key="p-{{ $participant->id }}" class="flex items-center justify-between gap-2 py-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $participant->member?->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $participant->member?->schoolClass?->name ?? '—' }} · {{ $participant->role_in_event->value }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('check-in', $participant->qr_token) }}" target="_blank" rel="noopener"
                                class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-600 hover:bg-gray-200"
                                title="{{ route('check-in', $participant->qr_token) }}">QR aktif</a>
                            <button type="button" wire:click="removeParticipant({{ $participant->id }})"
                                wire:confirm="Hapus peserta ini?"
                                class="text-xs text-brand-600 underline hover:text-brand-800">Hapus</button>
                        </div>
                    </div>
                @empty
                    <p class="py-3 text-gray-500">Belum ada peserta.</p>
                @endforelse
            </div>
            <div class="mt-3">{{ $participants->links() }}</div>
        </x-card>

        <x-card title="Absensi Manual">
            <form wire:submit="saveAttendance" class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Anggota</label>
                    <select wire:model="attendanceMemberId" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">Pilih anggota…</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                    @error('attendanceMemberId') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    @error('member_id') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Status</label>
                    <select wire:model="attendanceStatus" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($attendanceStatuses as $status)
                            <option value="{{ $status->value }}">{{ $status->value }}</option>
                        @endforeach
                    </select>
                    @error('attendanceStatus') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    @error('status') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Catatan</label>
                    <input type="text" wire:model="attendanceNotes" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Simpan Absensi</button>
            </form>

            <div class="mt-6 border-t border-gray-100 pt-4">
                <h3 class="mb-2 text-sm font-semibold text-gray-700">Rekap Absensi</h3>
                <div class="divide-y divide-gray-100 text-sm">
                    @forelse ($attendance as $record)
                        <div wire:key="a-{{ $record->id }}" class="flex items-center justify-between py-2">
                            <span class="text-gray-900">{{ $record->member?->full_name }}</span>
                            <span class="flex items-center gap-2">
                                <x-badge :color="$record->status->value === 'hadir' ? 'green' : 'yellow'">{{ $record->status->value }}</x-badge>
                                <span class="text-xs text-gray-500">{{ $record->method->value }}</span>
                            </span>
                        </div>
                    @empty
                        <p class="py-2 text-gray-500">Belum ada absensi.</p>
                    @endforelse
                </div>
                <div class="mt-3">{{ $attendance->links() }}</div>
            </div>
        </x-card>
    </div>

    <x-card title="Dokumentasi">
        <form wire:submit="uploadDocument" class="mb-4 flex flex-wrap items-end gap-3 border-b border-gray-100 pb-4">
            <div class="min-w-48 flex-1">
                <label class="block text-sm font-semibold text-gray-700">File (JPG/PNG/WebP/MP4, max 5 MB)</label>
                <input type="file" wire:model="documentFile" accept=".jpg,.jpeg,.png,.webp,.mp4"
                    class="mt-1 block w-full text-sm text-gray-600">
                @error('documentFile') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div class="min-w-40 flex-1">
                <label class="block text-sm font-semibold text-gray-700">Caption</label>
                <input type="text" wire:model="documentCaption" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm text-gray-700">
                <input type="checkbox" wire:model="documentIsPublic" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Publik
            </label>
            <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Unggah</button>
        </form>

        <div class="divide-y divide-gray-100 text-sm">
            @forelse ($documents as $doc)
                <div wire:key="doc-{{ $doc->id }}" class="flex items-center justify-between gap-2 py-2">
                    <div>
                        <p class="font-medium text-gray-900">{{ $doc->caption ?? basename($doc->file_path) }}</p>
                        <p class="text-xs text-gray-500">{{ $doc->file_type }} · {{ $doc->is_public ? 'publik' : 'internal' }}</p>
                    </div>
                </div>
            @empty
                <p class="py-2 text-gray-500">Belum ada dokumentasi.</p>
            @endforelse
        </div>
    </x-card>
</div>
