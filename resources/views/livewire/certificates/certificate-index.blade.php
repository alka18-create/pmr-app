<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Kelola Sertifikat</h1>
            <p class="mt-1 text-sm text-gray-600">Terbitkan manual, cabut, atau generate ulang PDF sertifikat.</p>
        </div>
        <a href="{{ route('certificates.mine') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Sertifikat Saya</a>
    </div>

    <x-card>
        <form wire:submit="issue" class="mb-6 grid max-w-3xl gap-4 border-b border-gray-100 pb-6 sm:grid-cols-2">
            <div>
                <label for="issueMemberId" class="block text-sm font-semibold text-gray-700">Anggota</label>
                <select id="issueMemberId" wire:model="issueMemberId" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">— Pilih anggota —</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                    @endforeach
                </select>
                @error('issueMemberId') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="issueSourceType" class="block text-sm font-semibold text-gray-700">Sumber</label>
                <select id="issueSourceType" wire:model="issueSourceType" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @foreach ($sourceTypes as $st)
                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                    @endforeach
                </select>
                @error('issueSourceType') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            @if ($issueSourceType === 'event')
                <div>
                    <label for="issueSourceId" class="block text-sm font-semibold text-gray-700">Kegiatan (opsional)</label>
                    <select id="issueSourceId" wire:model="issueSourceId" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">— Tanpa relasi —</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                    @error('issueSourceId') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            @endif
            <div>
                <label for="issueTitle" class="block text-sm font-semibold text-gray-700">Judul sertifikat</label>
                <input id="issueTitle" type="text" wire:model="issueTitle" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('issueTitle') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Terbitkan Sertifikat</button>
            </div>
        </form>

        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode / judul…"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
            <select wire:model.live="sourceFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua sumber</option>
                @foreach ($sourceTypes as $st)
                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Kode</th>
                        <th class="py-2 pr-4 font-medium">Anggota</th>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Sumber</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($certificates as $cert)
                        <tr wire:key="cm-{{ $cert->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-mono text-xs text-gray-600">{{ $cert->code }}</td>
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $cert->member?->full_name ?? '—' }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $cert->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $cert->source_type->label() }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$cert->is_revoked ? 'red' : 'green'">{{ $cert->is_revoked ? 'dicabut' : 'valid' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                @if (! $cert->is_revoked)
                                    <button type="button" wire:click="download({{ $cert->id }})" class="text-sm text-gray-700 underline">PDF</button>
                                    <button type="button" wire:click="regenerate({{ $cert->id }})" class="ml-3 text-sm text-gray-700 underline">Regen</button>
                                    <button type="button" wire:click="revoke({{ $cert->id }})" wire:confirm="Cabut sertifikat ini?"
                                        class="ml-3 text-sm text-brand-600 underline">Cabut</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada sertifikat" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $certificates->links() }}</div>
    </x-card>
</div>
