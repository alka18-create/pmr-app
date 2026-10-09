<div class="space-y-6">
    <x-page-header
        eyebrow="Dokumentasi"
        title="Galeri{{ $category ? ' — '.$category->name : '' }}"
        description="Foto dan video dokumentasi kegiatan."
    />

    @if ($categories->isNotEmpty())
        <nav aria-label="Kategori galeri" class="flex flex-wrap gap-2">
            <a href="{{ route('public.gallery.index') }}"
                class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $categorySlug === '' ? 'bg-brand-600 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">Semua</a>
            @foreach ($categories as $cat)
                <a href="{{ route('public.gallery.category', $cat->slug) }}"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $categorySlug === $cat->slug ? 'bg-brand-600 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">{{ $cat->name }}</a>
            @endforeach
        </nav>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            <figure wire:key="gal-{{ $item->id }}" class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                @if ($item->file_type === \App\Enums\GalleryFileType::Video && $item->video_url)
                    <div class="aspect-video bg-gray-900">
                        <a href="{{ $item->video_url }}" target="_blank" rel="noopener noreferrer" class="flex h-full flex-col items-center justify-center gap-2 text-sm font-semibold text-white">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 transition group-hover:scale-110"><x-icon name="photo" class="h-6 w-6" /></span>
                            Putar video (YouTube)
                        </a>
                    </div>
                @elseif ($item->file_path)
                    <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->title }}"
                        class="aspect-square w-full object-cover transition group-hover:scale-[1.03]" loading="lazy">
                @else
                    <div class="flex aspect-square flex-col items-center justify-center gap-1 bg-gray-100 text-sm text-gray-500"><x-icon name="photo" class="h-8 w-8 text-gray-400" />Tidak ada media</div>
                @endif
                <figcaption class="p-4">
                    <p class="font-bold text-gray-900">{{ $item->title }}</p>
                    @if ($item->category)
                        <a href="{{ route('public.gallery.category', $item->category->slug) }}" class="mt-1 inline-block text-xs font-semibold text-brand-700 hover:underline">{{ $item->category->name }}</a>
                    @endif
                </figcaption>
            </figure>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-card><x-empty-state title="Galeri kosong" message="Belum ada item yang dipublikasikan." icon="photo" /></x-card>
            </div>
        @endforelse
    </div>

    <div>{{ $items->links() }}</div>
</div>
