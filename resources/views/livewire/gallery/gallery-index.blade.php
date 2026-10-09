<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Galeri</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola foto, video, dan kategori galeri.</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-card title="Kategori" class="lg:col-span-1">
            <form wire:submit="saveCategory" class="mb-4 space-y-2">
                <label for="newCategoryName" class="block text-xs font-medium text-gray-600">Nama kategori</label>
                <input id="newCategoryName" type="text" wire:model="newCategoryName" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('newCategoryName') <p class="text-sm text-brand-600">{{ $message }}</p> @enderror
                <label for="newCategoryDescription" class="block text-xs font-medium text-gray-600">Deskripsi</label>
                <input id="newCategoryDescription" type="text" wire:model="newCategoryDescription" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <button type="submit" class="w-full rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Tambah Kategori</button>
            </form>

            <ul class="divide-y divide-gray-100 text-sm">
                @forelse ($categories as $category)
                    <li wire:key="cat-{{ $category->id }}" class="flex items-center justify-between gap-2 py-2">
                        @if ($editCategoryId === $category->id)
                            <input type="text" wire:model="editCategoryName" class="flex-1 rounded border border-gray-300 px-2 py-1 text-sm">
                            <button type="button" wire:click="updateCategory" class="text-xs text-green-700 underline">Simpan</button>
                            <button type="button" wire:click="cancelEditCategory" class="text-xs text-gray-500 underline">Batal</button>
                        @else
                            <span>{{ $category->name }}</span>
                            <span class="flex gap-2">
                                <button type="button" wire:click="startEditCategory({{ $category->id }})" class="text-xs text-gray-600 underline">Edit</button>
                                <button type="button" wire:click="deleteCategory({{ $category->id }})" wire:confirm="Hapus kategori?" class="text-xs text-brand-600 underline">Hapus</button>
                            </span>
                        @endif
                    </li>
                @empty
                    <li class="py-3 text-gray-500">Belum ada kategori.</li>
                @endforelse
            </ul>
        </x-card>

        <div class="space-y-4 lg:col-span-2">
            <x-card title="Tambah Item">
                <form wire:submit="saveItem" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700">Judul</label>
                        <input id="title" type="text" wire:model="title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="category_id" class="block text-sm font-semibold text-gray-700">Kategori</label>
                            <select id="category_id" wire:model="category_id" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="">Tanpa kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="file_type" class="block text-sm font-semibold text-gray-700">Tipe</label>
                            <select id="file_type" wire:model.live="file_type" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                @foreach ($fileTypes as $type)
                                    <option value="{{ $type->value }}">{{ $type->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if ($file_type === 'video')
                        <div>
                            <label for="video_url" class="block text-sm font-semibold text-gray-700">URL Video (YouTube)</label>
                            <input id="video_url" type="url" wire:model="video_url" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @error('video_url') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div>
                            <label for="file" class="block text-sm font-semibold text-gray-700">Gambar (JPG/PNG/WebP, max 4 MB)</label>
                            <input id="file" type="file" wire:model="file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm text-gray-600">
                            <div wire:loading wire:target="file" class="mt-1 text-xs text-gray-500">Mengunggah…</div>
                            @error('file') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                            <input id="description" type="text" wire:model="description" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @error('description') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="taken_at" class="block text-sm font-semibold text-gray-700">Diambil pada</label>
                            <input id="taken_at" type="date" wire:model="taken_at" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @error('taken_at') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="is_public" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Tampilkan ke publik
                    </label>
                    <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Simpan Item</button>
                </form>
            </x-card>

            <x-card title="Item Galeri">
                <div class="mb-4 grid gap-3 sm:grid-cols-2">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari judul…"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <select wire:model.live="categoryFilter"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($items as $item)
                        <div wire:key="item-{{ $item->id }}" class="rounded-lg border border-gray-100 p-3">
                            @if ($item->file_path)
                                <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->title }}" class="h-28 w-full rounded object-cover">
                            @else
                                <div class="flex h-28 items-center justify-center rounded bg-gray-100 text-xs text-gray-500">{{ $item->file_type->value }}</div>
                            @endif
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $item->title }}</p>
                            <p class="text-xs text-gray-500">{{ $item->category?->name ?? '—' }} · {{ $item->is_public ? 'publik' : 'internal' }}</p>
                            <button type="button" wire:click="deleteItem({{ $item->id }})" wire:confirm="Hapus item ini?"
                                class="mt-1 text-xs text-brand-600 underline">Hapus</button>
                        </div>
                    @empty
                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-empty-state title="Belum ada item" />
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">{{ $items->links() }}</div>
            </x-card>
        </div>
    </div>
</div>
