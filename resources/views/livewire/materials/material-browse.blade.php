<div class="space-y-6">
    <x-page-header
        eyebrow="Akademi"
        title="Materi Pelatihan"
        description="Materi akademi untuk anggota PMR."
    >
        @if ($canManage)
            <x-slot:actions>
                <x-btn href="{{ route('materials.manage') }}">Kelola Materi</x-btn>
            </x-slot:actions>
        @endif
    </x-page-header>

    <x-card>
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

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($materials as $item)
                <div wire:key="mb-{{ $item->id }}" class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-medium text-gray-900">{{ $item->title }}</h2>
                        <x-badge color="gray">{{ $item->level->label() }}</x-badge>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $item->category->label() }}</p>
                    @if ($item->content)
                        <p class="mt-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($item->content, 120) }}</p>
                    @endif
                    <div class="mt-3">
                        @if ($item->file_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($item->file_path) }}" target="_blank"
                                class="inline-block rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">Buka PDF</a>
                        @else
                            <span class="text-xs text-gray-400">File belum tersedia</span>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada materi" class="sm:col-span-2 lg:col-span-3" />
            @endforelse
        </div>
    </x-card>
</div>
