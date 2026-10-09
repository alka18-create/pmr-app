<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Berita & Artikel</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola konten berita, pengumuman, dan artikel.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('messages.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Inbox</a>
            <a href="{{ route('articles.create') }}" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Artikel Baru</a>
        </div>
    </div>

    <x-card>
        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <label class="sr-only" for="search">Cari judul</label>
            <input id="search" type="text" wire:model.live.debounce.300ms="search" placeholder="Cari judul…"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
            <label class="sr-only" for="categoryFilter">Filter kategori</label>
            <select id="categoryFilter" wire:model.live="categoryFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}">{{ $category->value }}</option>
                @endforeach
            </select>
            <label class="sr-only" for="statusFilter">Filter status</label>
            <select id="statusFilter" wire:model.live="statusFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Kategori</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Publik</th>
                        <th class="py-2 pr-4 font-medium">Penulis</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($articles as $article)
                        <tr wire:key="article-{{ $article->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $article->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $article->category->value }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$article->status->value === 'published' ? 'green' : 'yellow'">{{ $article->status->value }}</x-badge>
                            </td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$article->is_public ? 'green' : 'gray'">{{ $article->is_public ? 'ya' : 'tidak' }}</x-badge>
                            </td>
                            <td class="py-3 pr-4 text-gray-600">{{ $article->author?->name }}</td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <a href="{{ route('articles.edit', $article) }}" class="text-sm text-gray-700 underline hover:text-gray-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada artikel" message="Buat artikel pertama Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $articles->links() }}</div>
    </x-card>
</div>
