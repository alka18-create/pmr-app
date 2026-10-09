<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Edit Artikel</h1>
        <p class="mt-1 text-sm text-gray-600">Perbarui konten artikel.</p>
    </div>

    <x-card>
        <form wire:submit="save" class="max-w-3xl space-y-4">
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
                            <option value="{{ $item->value }}">{{ $item->value }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                    <select id="status" wire:model="status" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($statuses as $item)
                            <option value="{{ $item->value }}">{{ $item->value }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="is_public" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Tampilkan ke publik
                    </label>
                </div>
            </div>

            <div>
                <label for="excerpt" class="block text-sm font-semibold text-gray-700">Ringkasan</label>
                <textarea id="excerpt" wire:model="excerpt" rows="2" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                @error('excerpt') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="content" class="block text-sm font-semibold text-gray-700">Konten</label>
                <textarea id="content" wire:model="content" rows="12" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                @error('content') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="meta_title" class="block text-sm font-semibold text-gray-700">Meta Title (SEO)</label>
                    <input id="meta_title" type="text" wire:model="meta_title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('meta_title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="meta_description" class="block text-sm font-semibold text-gray-700">Meta Description (SEO)</label>
                    <input id="meta_description" type="text" wire:model="meta_description" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('meta_description') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="cover" class="block text-sm font-semibold text-gray-700">Cover (opsional, max 2 MB)</label>
                <input id="cover" type="file" wire:model="cover" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm text-gray-600">
                @error('cover') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-4">
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Simpan</button>
                <a href="{{ route('articles.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </x-card>
</div>
