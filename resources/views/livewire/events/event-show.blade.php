<div class="space-y-6">
    {{-- Header Kegiatan --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-extrabold tracking-tight text-gray-900 sm:text-2xl">{{ $event->title }}</h1>
                <x-badge :color="$event->status->value === 'published' ? 'green' : ($event->status->value === 'draft' ? 'yellow' : 'gray')">
                    {{ $event->status->value }}
                </x-badge>
                @if ($event->is_public)
                    <x-badge color="green">publik</x-badge>
                @else
                    <x-badge color="gray">internal</x-badge>
                @endif
            </div>
            <p class="mt-1.5 text-xs text-gray-600 sm:text-sm">
                <span class="font-semibold text-gray-800">{{ $event->type->value }}</span> ·
                {{ $event->starts_at->format('d M Y, H:i') }}
                @if ($event->ends_at)
                    — {{ $event->ends_at->format('H:i') }}
                @endif
                @if ($event->location)
                    · <span class="text-gray-700">📍 {{ $event->location }}</span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('events.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 sm:text-sm">
                &larr; Kembali
            </a>
            <a href="{{ route('events.edit', $event) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 sm:text-sm">
                Edit
            </a>
            <button type="button" wire:click="delete" wire:confirm="Hapus kegiatan ini?"
                class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 sm:text-sm">
                Hapus
            </button>
        </div>
    </div>

    @if ($event->description)
        <x-card title="Deskripsi">
            <p class="whitespace-pre-wrap text-sm text-gray-700 leading-relaxed">{{ $event->description }}</p>
        </x-card>
    @endif

    {{-- Grid 2 Kolom: Peserta & Absensi --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Kartu Peserta --}}
        <x-card title="Daftar Peserta ({{ $event->participants_count }})">
            {{-- Form Tambah Peserta --}}
            <div class="mb-5 space-y-3 rounded-xl border border-gray-100 bg-gray-50/60 p-3 sm:p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Tambah Peserta Kegiatan</p>

                {{-- Tambah Perorangan --}}
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select wire:model="memberSearch" class="flex-1 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm">
                        <option value="">Pilih anggota perorangan…</option>
                        @foreach ($members as $member)
                            @if (! $participantMemberIds->contains($member->id))
                                <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->schoolClass?->name ?? 'Tanpa Kelas' }})</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" wire:click="addParticipant"
                        class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-brand-700 sm:text-sm">
                        + Tambah
                    </button>
                </div>

                {{-- Tambah Semua Kelas --}}
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select wire:model="bulkClassId" class="flex-1 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm">
                        <option value="">Tambah semua anggota per kelas…</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="addParticipantsByClass"
                        class="inline-flex items-center justify-center rounded-xl border border-brand-600 bg-white px-4 py-2 text-xs font-bold text-brand-700 shadow-sm transition hover:bg-brand-50 sm:text-sm">
                        + Tambah Kelas
                    </button>
                </div>
            </div>

            {{-- List Peserta dengan Status Presensi & Quick Action --}}
            <div class="divide-y divide-gray-100">
                @forelse ($participants as $participant)
                    @php
                        $member = $participant->member;
                        $record = $attendanceByMember->get($participant->member_id);
                        $hasAttended = $record !== null;
                        $recordColor = $hasAttended ? match($record->status->value) {
                            'hadir' => 'green',
                            'izin' => 'blue',
                            'sakit' => 'yellow',
                            'alpa' => 'red',
                            default => 'gray',
                        } : 'gray';
                    @endphp
                    <div wire:key="p-{{ $participant->id }}" class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">
                                    {{ strtoupper(substr($member?->full_name ?? '?', 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $member?->full_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $member?->schoolClass?->name ?? 'Tanpa Kelas' }} ·
                                        <span class="text-gray-400">{{ $participant->role_in_event->value }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Status & Aksi Cepat --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 sm:justify-end sm:pt-0">
                            @if ($hasAttended)
                                <div class="flex items-center gap-1.5">
                                    <x-badge :color="$recordColor">{{ strtoupper($record->status->value) }}</x-badge>
                                    <span class="text-[10px] uppercase font-bold text-gray-400">({{ $record->method->value }})</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="quickAttendance({{ $participant->member_id }}, 'hadir')"
                                        title="Absen Hadir langsung"
                                        class="inline-flex items-center rounded-lg bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100 hover:text-emerald-800">
                                        + Hadir
                                    </button>
                                    <button type="button" wire:click="quickAttendance({{ $participant->member_id }}, 'izin')"
                                        title="Absen Izin"
                                        class="inline-flex items-center rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700 transition hover:bg-blue-100">
                                        Izin
                                    </button>
                                    <button type="button" wire:click="quickAttendance({{ $participant->member_id }}, 'sakit')"
                                        title="Absen Sakit"
                                        class="inline-flex items-center rounded-lg bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                        Sakit
                                    </button>
                                </div>
                            @endif

                            <div class="flex items-center gap-2 pl-1">
                                <a href="{{ route('check-in', $participant->qr_token) }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-[11px] font-semibold text-gray-600 transition hover:bg-gray-100"
                                    title="Tautan QR Check-in Peserta">
                                    <span>QR</span>
                                </a>
                                <button type="button" wire:click="removeParticipant({{ $participant->id }})"
                                    wire:confirm="Hapus {{ $member?->full_name }} dari kegiatan ini?"
                                    class="text-xs font-medium text-rose-600 transition hover:text-rose-800">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center">
                        <p class="text-xs text-gray-500">Belum ada peserta yang didaftarkan ke kegiatan ini.</p>
                        <p class="mt-1 text-[11px] text-gray-400">Gunakan form di atas untuk menambah peserta perorangan atau seluruh kelas.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">{{ $participants->links() }}</div>
        </x-card>

        {{-- Kartu Absensi Manual --}}
        <x-card title="Absensi Manual Kegiatan">
            <form wire:submit="saveAttendance" class="space-y-4">
                {{-- Pemilih Anggota Touch-Friendly & Searchable (Alpine-based, tidak floating di luar layar) --}}
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: @entangle('attendanceMemberId'),
                    selectedName: '',
                    members: {{ $members->map(fn($m) => [
                        'id' => (string) $m->id,
                        'name' => $m->full_name,
                        'class' => $m->schoolClass?->name ?? 'Tanpa Kelas',
                        'isParticipant' => $participantMemberIds->contains($m->id),
                    ])->toJson() }},
                    init() {
                        this.updateSelectedLabel();
                        this.$watch('selectedId', () => this.updateSelectedLabel());
                    },
                    updateSelectedLabel() {
                        if (!this.selectedId) {
                            this.selectedName = '';
                            return;
                        }
                        const found = this.members.find(m => String(m.id) === String(this.selectedId));
                        this.selectedName = found ? found.name + ' (' + found.class + ')' : '';
                    },
                    select(id) {
                        this.selectedId = String(id);
                        this.open = false;
                        this.search = '';
                    },
                    clear() {
                        this.selectedId = '';
                        this.selectedName = '';
                        this.search = '';
                    },
                    get filteredList() {
                        if (!this.search.trim()) return this.members;
                        const q = this.search.toLowerCase();
                        return this.members.filter(m => m.name.toLowerCase().includes(q) || m.class.toLowerCase().includes(q));
                    }
                }" class="relative">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Pilih Anggota</label>

                    {{-- Tombol Pemicu Dropdown --}}
                    <div class="mt-1">
                        <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                            class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-left text-sm shadow-sm transition hover:border-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <span x-show="selectedName" x-text="selectedName" class="truncate font-semibold text-gray-900"></span>
                            <span x-show="!selectedName" class="text-gray-400">Pilih atau cari anggota…</span>
                            <span class="ml-2 flex items-center gap-1 text-gray-400">
                                <button type="button" x-show="selectedName" @click.stop="clear()" class="rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </button>
                    </div>

                    {{-- Hidden Native Select untuk sinkronisasi form & kompatibilitas test --}}
                    <select wire:model="attendanceMemberId" class="sr-only" tabindex="-1" aria-hidden="true">
                        <option value="">Pilih anggota…</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->schoolClass?->name ?? 'Tanpa Kelas' }})</option>
                        @endforeach
                    </select>

                    {{-- Popover Dropdown di dalam container kartu --}}
                    <div x-show="open" @click.away="open = false" x-transition.opacity.duration.150ms
                        class="absolute left-0 right-0 z-30 mt-1 max-h-64 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
                        style="display: none;">
                        <div class="border-b border-gray-100 p-2">
                            <input x-ref="searchInput" x-model="search" type="text" placeholder="Ketik nama atau kelas..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                        </div>

                        <div class="max-h-52 overflow-y-auto divide-y divide-gray-50 p-1 text-sm">
                            <template x-for="item in filteredList" :key="item.id">
                                <button type="button" @click="select(item.id)"
                                    class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left transition hover:bg-brand-50/60"
                                    :class="String(item.id) === String(selectedId) ? 'bg-brand-50 text-brand-900 font-semibold' : 'text-gray-800'">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-semibold sm:text-sm" x-text="item.name"></p>
                                        <p class="text-[11px] text-gray-500" x-text="item.class"></p>
                                    </div>
                                    <template x-if="item.isParticipant">
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Peserta</span>
                                    </template>
                                </button>
                            </template>
                            <div x-show="filteredList.length === 0" class="py-3 text-center text-xs text-gray-400">
                                Anggota tidak ditemukan.
                            </div>
                        </div>
                    </div>

                    @error('attendanceMemberId') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Status Kehadiran (Pills Segmented Touch-Friendly) --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Status Kehadiran</label>
                    <div class="mt-1.5 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($attendanceStatuses as $status)
                            @php
                                $val = $status->value;
                                $activeClass = match($val) {
                                    'hadir' => 'peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 border-emerald-200 text-emerald-800 bg-emerald-50/50',
                                    'izin' => 'peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-blue-200 text-blue-800 bg-blue-50/50',
                                    'sakit' => 'peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-amber-600 border-amber-200 text-amber-800 bg-amber-50/50',
                                    'alpa' => 'peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 border-rose-200 text-rose-800 bg-rose-50/50',
                                    default => 'peer-checked:bg-gray-800 peer-checked:text-white peer-checked:border-gray-800 border-gray-200 text-gray-700 bg-gray-50',
                                };
                            @endphp
                            <label class="relative flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-center text-xs font-bold uppercase tracking-wider transition hover:opacity-90 {{ $activeClass }}">
                                <input type="radio" wire:model.live="attendanceStatus" value="{{ $val }}" class="peer sr-only">
                                <span>{{ $val }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('attendanceStatus') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Catatan Absensi (Opsional)</label>
                    <input type="text" wire:model="attendanceNotes" placeholder="Misal: Surat izin terlampir, Dispensasi, dll..."
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm">
                </div>

                {{-- Tombol Simpan Absensi --}}
                <button type="submit" wire:loading.attr="disabled"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50 sm:text-sm">
                    <span wire:loading.remove wire:target="saveAttendance">Simpan Absensi</span>
                    <span wire:loading wire:target="saveAttendance" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
            </form>

            {{-- Rekap Absensi Kegiatan --}}
            <div class="mt-6 border-t border-gray-100 pt-5">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-600">Catatan Kehadiran ({{ $attendance->total() }})</h3>
                    <a href="{{ route('attendance.recap') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800">Lihat Rekap Lengkap &rarr;</a>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($attendance as $record)
                        @php
                            $statusColor = match($record->status->value) {
                                'hadir' => 'green',
                                'izin' => 'blue',
                                'sakit' => 'yellow',
                                'alpa' => 'red',
                                default => 'gray',
                            };
                        @endphp
                        <div wire:key="a-{{ $record->id }}" class="flex flex-col gap-1 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900 sm:text-sm">{{ $record->member?->full_name }}</p>
                                <p class="text-[11px] text-gray-500">
                                    {{ $record->member?->schoolClass?->name ?? 'Tanpa Kelas' }}
                                    @if ($record->notes)
                                        · <span class="italic text-gray-600">"{{ $record->notes }}"</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 pt-1 sm:pt-0">
                                <x-badge :color="$statusColor">{{ strtoupper($record->status->value) }}</x-badge>
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">{{ $record->method->value }}</span>
                                <span class="text-[11px] text-gray-400">{{ $record->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-gray-400">Belum ada catatan absensi untuk kegiatan ini.</p>
                    @endforelse
                </div>
                <div class="mt-3">{{ $attendance->links() }}</div>
            </div>
        </x-card>
    </div>

    {{-- Kartu Dokumentasi --}}
    <x-card title="Dokumentasi Kegiatan">
        <form wire:submit="uploadDocument" class="mb-6 space-y-4 border-b border-gray-100 pb-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">File Foto / Video</label>
                <div class="mt-1 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-4 transition hover:bg-gray-50">
                    <input type="file" wire:model="documentFile" accept=".jpg,.jpeg,.png,.webp,.mp4"
                        class="block w-full text-xs text-gray-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-1 text-[11px] text-gray-400">Format JPG, PNG, WebP, atau MP4 (Maks. 5 MB)</p>
                </div>
                @error('documentFile') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Keterangan / Caption</label>
                <input type="text" wire:model="documentCaption" placeholder="Misal: Pembukaan acara, Simulasi evakuasi korban gempa..."
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm">
                @error('documentCaption') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" wire:model="documentIsPublic" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-700">Tampilkan ke Publik</p>
                    <p class="text-[11px] text-gray-500">Foto/video ini dapat dilihat oleh masyarakat umum di website galeri dan detail kegiatan publik.</p>
                </div>
            </label>

            <button type="submit" wire:loading.attr="disabled"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50 sm:w-auto sm:text-sm">
                <span wire:loading.remove wire:target="uploadDocument">Unggah Dokumentasi</span>
                <span wire:loading wire:target="uploadDocument">Mengunggah...</span>
            </button>
        </form>

        <div class="divide-y divide-gray-100 text-sm">
            @forelse ($documents as $doc)
                <div wire:key="doc-{{ $doc->id }}" class="flex items-center justify-between gap-2 py-2.5">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-gray-900 text-xs sm:text-sm">{{ $doc->caption ?? basename($doc->file_path) }}</p>
                        <p class="text-[11px] text-gray-500">{{ strtoupper($doc->file_type) }} · {{ $doc->is_public ? 'Publik' : 'Internal' }}</p>
                    </div>
                </div>
            @empty
                <p class="py-3 text-center text-xs text-gray-400">Belum ada dokumentasi untuk kegiatan ini.</p>
            @endforelse
        </div>
    </x-card>
</div>
