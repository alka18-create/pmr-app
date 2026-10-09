<div class="space-y-6">
    <x-page-header
        eyebrow="Penghargaan"
        title="Prestasi"
        description="Capaian dan penghargaan organisasi."
    >
        <x-slot:actions>
            <label for="levelFilter" class="sr-only">Filter tingkat</label>
            <select id="levelFilter" wire:model.live="levelFilter"
                class="rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua tingkat</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->value }}">{{ $level->value }}</option>
                @endforeach
            </select>
            <label for="yearFilter" class="sr-only">Filter tahun</label>
            <select id="yearFilter" wire:model.live="yearFilter"
                class="rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Semua tahun</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($achievements as $item)
            <article wire:key="ach-{{ $item->id }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md">
                <div class="flex items-start justify-between gap-2">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-yellow-50 text-yellow-600"><x-icon name="trophy" class="h-5 w-5" /></span>
                    <x-badge color="red">{{ $item->year }}</x-badge>
                </div>
                <h2 class="mt-3 font-bold text-gray-900">{{ $item->title }}</h2>
                <p class="mt-2 flex flex-wrap gap-1.5">
                    <x-badge>{{ $item->level->value }}</x-badge>
                    <x-badge>{{ $item->category->value }}</x-badge>
                </p>
                @if ($item->winner)
                    <p class="mt-2 text-sm text-gray-700">Peserta: {{ $item->winner }}</p>
                @endif
                @if ($item->description)
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $item->description }}</p>
                @endif
            </article>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-card><x-empty-state title="Belum ada prestasi" message="Prestasi akan tampil di sini." icon="trophy" /></x-card>
            </div>
        @endforelse
    </div>

    <div>{{ $achievements->links() }}</div>
</div>
