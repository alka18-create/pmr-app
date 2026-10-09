<div class="space-y-6">
    <x-page-header
        eyebrow="Informasi"
        title="Berita"
        description="Informasi dan pengumuman terbaru dari organisasi."
    >
        <x-slot:actions>
            <label for="categoryFilter" class="sr-only">Filter kategori</label>
            <select id="categoryFilter" wire:model.live="categoryFilter"
                class="rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}">{{ $category->value }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($articles as $article)
            <a wire:key="article-{{ $article->id }}" href="{{ route('public.articles.show', $article->slug) }}"
                class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                @if ($article->cover_path)
                    <img src="{{ asset('storage/'.$article->cover_path) }}" alt="{{ $article->title }}"
                        class="h-40 w-full object-cover" loading="lazy">
                @endif
                <div class="p-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-600">{{ $article->category->value }}</p>
                    <h2 class="mt-1 font-bold text-gray-900 group-hover:text-brand-700">{{ $article->title }}</h2>
                    @if ($article->excerpt)
                        <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $article->excerpt }}</p>
                    @endif
                    <p class="mt-3 text-xs text-gray-500">{{ $article->published_at?->format('d M Y') }}</p>
                </div>
            </a>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-card><x-empty-state title="Belum ada berita" message="Belum ada artikel yang dipublikasikan." icon="newspaper" /></x-card>
            </div>
        @endforelse
    </div>

    <div>{{ $articles->links() }}</div>
</div>
