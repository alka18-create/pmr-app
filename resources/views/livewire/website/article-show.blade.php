<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('public.articles.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-brand-700">
        <x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Semua berita
    </a>

    <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-brand-600">{{ $article['category'] }}</p>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-gray-900">{{ $article['title'] }}</h1>
        <p class="mt-2 flex flex-wrap items-center gap-x-2 text-sm text-gray-500">
            <span>{{ $article['published_at'] }}</span>
            @if ($article['author']) <span aria-hidden="true">·</span> <span>{{ $article['author'] }}</span> @endif
            @if ($article['views']) <span aria-hidden="true">·</span> <span>{{ $article['views'] }}x dibaca</span> @endif
        </p>

        @if ($article['cover_path'])
            <img src="{{ asset('storage/'.$article['cover_path']) }}" alt="{{ $article['title'] }}"
                class="mt-5 w-full rounded-2xl object-cover" loading="lazy">
        @endif

        @if ($article['excerpt'])
            <p class="mt-5 border-l-4 border-brand-200 bg-brand-50/50 py-2 pl-4 pr-3 text-sm italic leading-relaxed text-gray-600">{{ $article['excerpt'] }}</p>
        @endif

        <div class="prose prose-sm mt-6 max-w-none text-gray-800">
            {!! $article['content'] !!}
        </div>
    </article>
</div>
