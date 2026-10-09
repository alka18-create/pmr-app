<div class="space-y-12">
    <section class="relative overflow-hidden rounded-3xl bg-brand-800 px-6 py-12 text-white shadow-lg sm:px-10 sm:py-16">
        <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-brand-600/70 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-brand-950/70 blur-3xl" aria-hidden="true"></div>
        <div class="relative">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-brand-100">
                <x-icon name="sparkles" class="h-4 w-4" />
                Palang Merah Remaja
            </p>
            <h1 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl lg:text-5xl">{{ $orgName }}</h1>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-brand-100 sm:text-base">{{ $vision }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-btn href="{{ route('public.members.index') }}" variant="light">
                    Lihat Anggota
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </x-btn>
                <x-btn href="{{ route('public.contact') }}" variant="outline-light">Hubungi Kami</x-btn>
            </div>
        </div>
    </section>

    <section aria-label="Statistik">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Anggota Aktif" :value="$stats['members']" icon="users" />
            <x-stat-card label="Kegiatan" :value="$stats['events']" icon="calendar" />
            <x-stat-card label="Prestasi" :value="$stats['achievements']" icon="trophy" />
            <x-stat-card label="Berita" :value="$stats['articles']" icon="newspaper" />
        </div>
    </section>

    <section aria-labelledby="kegiatan-heading">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 id="kegiatan-heading" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="calendar" class="h-5 w-5" /></span>
                Kegiatan Mendatang
            </h2>
            <a href="{{ route('public.events.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">Semua <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($upcomingEvents as $event)
                <a wire:key="home-event-{{ $event->id }}" href="{{ route('public.events.show', $event->slug) }}"
                    class="group flex gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                    <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <span class="text-lg font-extrabold leading-none">{{ $event->starts_at->format('d') }}</span>
                        <span class="text-[11px] font-bold uppercase">{{ $event->starts_at->format('M') }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-brand-600">{{ $event->type->value }}</p>
                        <h3 class="mt-0.5 truncate font-bold text-gray-900 group-hover:text-brand-700">{{ $event->title }}</h3>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-600"><x-icon name="clock" class="h-4 w-4 shrink-0 text-gray-400" />{{ $event->starts_at->format('d M Y H:i') }}</p>
                        @if ($event->location)
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-sm text-gray-600"><x-icon name="map-pin" class="h-4 w-4 shrink-0 text-gray-400" />{{ $event->location }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-card><x-empty-state title="Belum ada kegiatan mendatang" message="Pantau jadwal terbaru di halaman kegiatan." icon="calendar" /></x-card>
                </div>
            @endforelse
        </div>
    </section>

    <section aria-labelledby="berita-heading">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 id="berita-heading" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="newspaper" class="h-5 w-5" /></span>
                Berita Terbaru
            </h2>
            <a href="{{ route('public.articles.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">Semua <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($latestArticles as $article)
                <a wire:key="home-article-{{ $article->id }}" href="{{ route('public.articles.show', $article->slug) }}"
                    class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                    @if ($article->cover_path)
                        <img src="{{ asset('storage/'.$article->cover_path) }}" alt="{{ $article->title }}" class="h-40 w-full object-cover" loading="lazy">
                    @endif
                    <div class="p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-brand-600">{{ $article->category->value }}</p>
                        <h3 class="mt-1 font-bold text-gray-900 group-hover:text-brand-700">{{ $article->title }}</h3>
                        @if ($article->excerpt)
                            <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $article->excerpt }}</p>
                        @endif
                        <p class="mt-3 text-xs text-gray-500">{{ $article->published_at?->format('d M Y') }}</p>
                    </div>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-card><x-empty-state title="Belum ada berita" message="Berita terbaru akan muncul di sini." icon="newspaper" /></x-card>
                </div>
            @endforelse
        </div>
    </section>

    <section aria-labelledby="prestasi-heading" class="grid gap-8 lg:grid-cols-2">
        <div>
            <div class="mb-5 flex items-center justify-between gap-4">
                <h2 id="prestasi-heading" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="trophy" class="h-5 w-5" /></span>
                    Prestasi Unggulan
                </h2>
                <a href="{{ route('public.achievements.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">Semua <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="space-y-3">
                @forelse ($featuredAchievements as $item)
                    <div wire:key="home-ach-{{ $item->id }}" class="flex items-start gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-yellow-50 text-yellow-600"><x-icon name="trophy" class="h-5 w-5" /></span>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900">{{ $item->title }}</p>
                            <p class="mt-0.5 text-sm text-gray-600">{{ $item->level->value }} · {{ $item->year }}@if ($item->winner) · {{ $item->winner }} @endif</p>
                        </div>
                    </div>
                @empty
                    <x-card><x-empty-state title="Belum ada prestasi" icon="trophy" /></x-card>
                @endforelse
            </div>
        </div>

        <div>
            <div class="mb-5 flex items-center justify-between gap-4">
                <h2 class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="photo" class="h-5 w-5" /></span>
                    Galeri
                </h2>
                <a href="{{ route('public.gallery.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">Semua <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            @if ($galleryPreview->isEmpty())
                <x-card><x-empty-state title="Galeri kosong" message="Foto kegiatan akan tampil di sini." icon="photo" /></x-card>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($galleryPreview as $item)
                        <div wire:key="home-gal-{{ $item->id }}" class="aspect-square overflow-hidden rounded-2xl bg-gray-100 shadow-sm">
                            @if ($item->file_path)
                                <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->title }}" class="h-full w-full object-cover transition hover:scale-105" loading="lazy">
                            @else
                                <div class="flex h-full flex-col items-center justify-center gap-1 text-xs text-gray-500"><x-icon name="photo" class="h-6 w-6 text-gray-400" />Video</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="relative overflow-hidden rounded-3xl bg-gray-900 px-6 py-10 text-center sm:py-12">
        <div class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 rounded-full bg-brand-600/40 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-20 -right-10 h-56 w-56 rounded-full bg-brand-600/30 blur-3xl" aria-hidden="true"></div>
        <div class="relative">
            <h2 class="text-xl font-extrabold tracking-tight text-white sm:text-2xl">Siap bergabung atau berkolaborasi?</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-gray-300">{{ $mission }}</p>
            <x-btn href="{{ route('public.contact') }}" class="mt-6">
                Hubungi Kami
                <x-icon name="arrow-right" class="h-4 w-4" />
            </x-btn>
        </div>
    </section>
</div>
