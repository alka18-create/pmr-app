<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Edit Kegiatan</h1>
        <p class="mt-1 text-sm text-gray-600">Perbarui data kegiatan.</p>
    </div>

    <x-card>
        <form wire:submit="save" class="max-w-3xl space-y-4">
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">Judul</label>
                <input id="title" type="text" wire:model="title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700">Jenis</label>
                    <select id="type" wire:model="type" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @foreach ($types as $item)
                            <option value="{{ $item->value }}">{{ $item->value }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
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
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                <textarea id="description" wire:model="description" rows="4" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                @error('description') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="location" class="block text-sm font-semibold text-gray-700">Lokasi</label>
                    <input id="location" type="text" wire:model="location" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('location') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_participants" class="block text-sm font-semibold text-gray-700">Maks. Peserta</label>
                    <input id="max_participants" type="number" wire:model="max_participants" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('max_participants') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="starts_at" class="block text-sm font-semibold text-gray-700">Mulai</label>
                    <input id="starts_at" type="datetime-local" wire:model="starts_at" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('starts_at') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ends_at" class="block text-sm font-semibold text-gray-700">Selesai</label>
                    <input id="ends_at" type="datetime-local" wire:model="ends_at" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('ends_at') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="cover" class="block text-sm font-semibold text-gray-700">Poster (opsional, max 2 MB)</label>
                <input id="cover" type="file" wire:model="cover" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm text-gray-600">
                @error('cover') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model="is_public" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Tampilkan ke publik
            </label>

            <div class="flex gap-3 border-t border-gray-100 pt-4">
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Simpan</button>
                <a href="{{ route('events.show', $eventId) }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </x-card>
</div>
