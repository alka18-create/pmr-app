<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Prestasi</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola catatan prestasi organisasi.</p>
        </div>
        <button type="button" wire:click="startEdit()"
            class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Prestasi Baru</button>
    </div>

    <x-card>
        <form wire:submit="save" id="achievement-form" class="mb-6 max-w-3xl space-y-4 border-b border-gray-100 pb-6">
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">Judul</label>
                <input id="title" type="text" wire:model="title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="level" class="block text-sm font-semibold text-gray-700">Tingkat</label>
                    <select id="level" wire:model="level" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($levels as $item)
                            <option value="{{ $item->value }}">{{ $item->value }}</option>
                        @endforeach
                    </select>
                    @error('level') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="category" class="block text-sm font-semibold text-gray-700">Kategori</label>
                    <select id="category" wire:model="category" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($categories as $item)
                            <option value="{{ $item->value }}">{{ $item->value }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="year" class="block text-sm font-semibold text-gray-700">Tahun</label>
                    <input id="year" type="number" wire:model="year" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('year') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="winner" class="block text-sm font-semibold text-gray-700">Peserta (opsional)</label>
                    <input id="winner" type="text" wire:model="winner" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('winner') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="attachment" class="block text-sm font-semibold text-gray-700">Lampiran (JPG/PNG/PDF, max 5 MB)</label>
                    <input id="attachment" type="file" wire:model="attachment" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full text-sm text-gray-600">
                    @error('attachment') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                <textarea id="description" wire:model="description" rows="3" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                @error('description') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model="is_public" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Tampilkan ke publik
            </label>
            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                {{ $editId ? 'Perbarui' : 'Simpan' }}
            </button>
                @if ($editId)
                    <button type="button" wire:click="cancelEdit" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</button>
                @endif
            </div>
        </form>

        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari judul…"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
            <select wire:model.live="levelFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua tingkat</option>
                @foreach ($levels as $item)
                    <option value="{{ $item->value }}">{{ $item->value }}</option>
                @endforeach
            </select>
            <input type="text" wire:model.live.debounce.300ms="yearFilter" placeholder="Filter tahun…"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Tingkat</th>
                        <th class="py-2 pr-4 font-medium">Kategori</th>
                        <th class="py-2 pr-4 font-medium">Tahun</th>
                        <th class="py-2 pr-4 font-medium">Publik</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($achievements as $item)
                        <tr wire:key="ach-{{ $item->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $item->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $item->level->value }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $item->category->value }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $item->year }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$item->is_public ? 'green' : 'gray'">{{ $item->is_public ? 'ya' : 'tidak' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                @if ($item->attachment_path)
                                    <a href="{{ route('achievements.download', $item) }}" target="_blank" class="mr-3 text-sm text-brand-700 underline hover:text-brand-900">Lampiran</a>
                                @endif
                                <button type="button" wire:click="startEdit({{ $item->id }})" class="text-sm text-gray-700 underline hover:text-gray-900">Edit</button>
                                <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Hapus prestasi ini?"
                                    class="ml-3 text-sm text-brand-600 underline hover:text-brand-800">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada prestasi" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $achievements->links() }}</div>
    </x-card>
</div>
