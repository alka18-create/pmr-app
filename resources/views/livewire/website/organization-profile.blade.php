<div class="space-y-8">
    <x-page-header
        eyebrow="Tentang Kami"
        title="Profil Organisasi"
        description="Tentang {{ $orgName }} — sejarah, visi misi, struktur, dan prinsip dasar."
    />

    <x-card>
        <h2 id="sejarah" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="book" class="h-5 w-5" /></span>
            Sejarah
        </h2>
        <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $history }}</p>
    </x-card>

    <section class="grid gap-4 md:grid-cols-2" aria-label="Visi dan misi">
        <x-card>
            <h2 class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="sparkles" class="h-5 w-5" /></span>
                Visi
            </h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-700">{{ $vision }}</p>
        </x-card>
        <x-card>
            <h2 class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="check" class="h-5 w-5" /></span>
                Misi
            </h2>
            <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $mission }}</p>
        </x-card>
    </section>

    <x-card>
        <h2 id="struktur" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="users" class="h-5 w-5" /></span>
            Struktur Pengurus
        </h2>
        @if ($structure->isEmpty())
            <p class="mt-3 text-sm text-gray-500">Struktur pengurus belum tersedia.</p>
        @else
            <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($structure as $member)
                    <li class="flex items-center gap-3 rounded-2xl border border-gray-100 p-4 transition hover:border-brand-200">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-brand-700">
                            {{ strtoupper(substr($member->full_name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-bold text-gray-900">{{ $member->full_name }}</p>
                            <p class="mt-0.5 text-sm font-medium text-brand-700">{{ $member->position?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $member->schoolClass?->name }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <x-card>
        <h2 id="prinsip" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="shield" class="h-5 w-5" /></span>
            7 Prinsip Dasar
        </h2>
        <ol class="mt-4 space-y-3">
            @foreach ($principles as $principle)
                <li class="flex gap-4 rounded-2xl border border-gray-100 p-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-sm font-extrabold text-white">{{ $loop->iteration }}</span>
                    <div>
                        <p class="font-bold text-gray-900">{{ $principle['title'] }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $principle['body'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </x-card>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $orgName,
            'description' => $vision,
            'email' => $contactEmail,
            'telephone' => $contactPhone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $contactAddress,
            ],
            'sameAs' => array_values(array_filter([$instagram, $youtube, $tiktok])),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</div>
