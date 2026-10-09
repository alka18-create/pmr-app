<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Materi Pelatihan</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola materi akademi PMR (upload PDF).</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('materials.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Lihat Sebagai Anggota</a>
            <button type="button" wire:click="startEdit()"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Materi Baru</button>
        </div>
    </div>

    <x-card>
        <form wire:submit="save" id="material-form" class="mb-6 max-w-3xl space-y-4 border-b border-gray-100 pb-6">
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">Judul</label>
                <input id="title" type="text" wire:model="title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="category" class="block text-sm font-semibold text-gray-700">Kategori</label>
                    <select id="category" wire:model="category" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($categories as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="level" class="block text-sm font-semibold text-gray-700">Level</label>
                    <select id="level" wire:model="level" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($levels as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('level') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="order" class="block text-sm font-semibold text-gray-700">Urutan</label>
                    <input id="order" type="number" wire:model="order" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('order') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="content" class="block text-sm font-semibold text-gray-700">Catatan (opsional)</label>
                <textarea id="content" wire:model="content" rows="3" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                @error('content') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="file" class="block text-sm font-semibold text-gray-700">File PDF (wajib, max 5 MB)</label>
                <input id="file" type="file" wire:model="file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-600">
                <div wire:loading wire:target="file" class="mt-1 text-sm text-gray-500">Mengunggah…</div>
                @error('file') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model="is_public" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Tampilkan ke publik (opsional)
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
            <select wire:model.live="categoryFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua kategori</option>
                @foreach ($categories as $item)
                    <option value="{{ $item->value }}">{{ $item->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="levelFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua level</option>
                @foreach ($levels as $item)
                    <option value="{{ $item->value }}">{{ $item->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Kategori</th>
                        <th class="py-2 pr-4 font-medium">Level</th>
                        <th class="py-2 pr-4 font-medium">Urutan</th>
                        <th class="py-2 pr-4 font-medium">File</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($materials as $item)
                        <tr wire:key="mat-{{ $item->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $item->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $item->category->label() }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $item->level->label() }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $item->order }}</td>
                            <td class="py-3 pr-4">
                                @if ($item->file_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($item->file_path) }}" target="_blank" class="text-brand-700 underline">PDF</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <button type="button" wire:click="startEdit({{ $item->id }})" class="text-sm text-gray-700 underline hover:text-gray-900">Edit</button>
                                <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Hapus materi ini?"
                                    class="ml-3 text-sm text-brand-600 underline hover:text-brand-800">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada materi" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $materials->links() }}</div>
    </x-card>
</div>
