@props(['eyebrow' => null, 'title' => null, 'description' => null])

{{--
    Kepala halaman publik yang konsisten: eyebrow + judul + deskripsi + aksi.
    Pakai: <x-page-header eyebrow="..." title="..." description="...">
               <x-slot:actions><x-btn .../></x-slot:actions>
           </x-page-header>
--}}

<div class="mb-6 flex flex-wrap items-end justify-between gap-4 sm:mb-8">
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="text-xs font-bold uppercase tracking-widest text-brand-600">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">{{ $title }}</h1>
        @endif
        @if ($description)
            <p class="mt-2 text-sm leading-relaxed text-gray-600 sm:text-base">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
