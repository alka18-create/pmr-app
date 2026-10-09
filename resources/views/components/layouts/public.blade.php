<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo
        :title="$title ?? null"
        :description="isset($description) ? $description : null"
        :url="isset($url) ? $url : null"
        :image="isset($image) ? $image : null"
    />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#e31837">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-white">Lewati ke konten</a>

    <header class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <span class="flex h-10 items-center rounded-xl bg-white px-2 py-1 shadow-sm ring-1 ring-gray-200" aria-hidden="true">
                    <img src="{{ asset('images/logo-pmi.png') }}" alt="" class="h-7 w-auto object-contain">
                </span>
                <span class="leading-tight">
                    <span class="block text-sm font-extrabold tracking-tight text-gray-900">{{ \App\Models\Setting::get('org_name', config('app.name')) }}</span>
                    <span class="block text-[11px] font-medium uppercase tracking-widest text-brand-600">Palang Merah Remaja</span>
                </span>
            </a>

            @php
                $menu = [
                    ['label' => 'Beranda', 'route' => 'home', 'is' => 'home'],
                    ['label' => 'Profil', 'route' => 'public.profile', 'is' => 'public.profile'],
                    ['label' => 'Kegiatan', 'route' => 'public.events.index', 'is' => 'public.events.*'],
                    ['label' => 'Berita', 'route' => 'public.articles.index', 'is' => 'public.articles.*'],
                    ['label' => 'Galeri', 'route' => 'public.gallery.index', 'is' => 'public.gallery.*'],
                    ['label' => 'Prestasi', 'route' => 'public.achievements.index', 'is' => 'public.achievements.*'],
                    ['label' => 'Anggota', 'route' => 'public.members.index', 'is' => 'public.members.*'],
                    ['label' => 'Kontak', 'route' => 'public.contact', 'is' => 'public.contact'],
                ];
            @endphp
            <nav aria-label="Menu utama" class="hidden items-center gap-1 text-sm lg:flex">
                @foreach ($menu as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @if (request()->routeIs($item['is'])) aria-current="page" @endif
                        class="rounded-lg px-3 py-2 {{ request()->routeIs($item['is']) ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >{{ $item['label'] }}</a>
                @endforeach
                <a
                    href="{{ route('login') }}"
                    class="ml-2 rounded-xl bg-brand-600 px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                >Masuk</a>
            </nav>

            <div class="flex items-center gap-2 lg:hidden">
                <a
                    href="{{ route('login') }}"
                    class="rounded-xl bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700"
                >Masuk</a>
                <button
                    type="button"
                    id="nav-toggle"
                    aria-expanded="false"
                    aria-controls="nav-mobile"
                    aria-label="Buka menu navigasi"
                    class="rounded-xl border border-gray-300 p-2 text-gray-700 hover:bg-gray-50"
                >
                    <x-icon name="menu" class="h-5 w-5" data-nav-icon-open />
                    <x-icon name="x" class="hidden h-5 w-5" data-nav-icon-close />
                </button>
            </div>
        </div>

        <nav id="nav-mobile" aria-label="Menu utama seluler" class="hidden border-t border-gray-100 bg-white px-4 py-3 lg:hidden">
            <div class="grid gap-1 text-sm">
                @foreach ($menu as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @if (request()->routeIs($item['is'])) aria-current="page" @endif
                        class="rounded-lg px-3 py-2.5 {{ request()->routeIs($item['is']) ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-gray-700 hover:bg-gray-50' }}"
                    >{{ $item['label'] }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main id="konten" class="mx-auto max-w-6xl px-4 py-8 sm:py-10">
        @if (session('success'))
            <div class="mb-6"><x-alert type="success">{{ session('success') }}</x-alert></div>
        @endif
        @if (session('error'))
            <div class="mb-6"><x-alert type="error">{{ session('error') }}</x-alert></div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mt-12 border-t border-gray-200 bg-gray-900 text-gray-300">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="flex items-center gap-2.5">
                    <span class="flex h-10 items-center rounded-xl bg-white px-2 py-1" aria-hidden="true">
                        <img src="{{ asset('images/logo-pmi.png') }}" alt="" class="h-7 w-auto object-contain" loading="lazy">
                    </span>
                    <span class="font-extrabold tracking-tight text-white">{{ \App\Models\Setting::get('org_name', config('app.name')) }}</span>
                </p>
                <p class="mt-4 leading-relaxed text-gray-400">Palang Merah Remaja — membina generasi muda yang berkarakter, terampil, dan siap menolong sesama.</p>
            </div>
            <nav aria-label="Navigasi bawah">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Navigasi</p>
                <ul class="mt-3 space-y-2">
                    <li><a class="hover:text-white" href="{{ route('public.profile') }}">Profil</a></li>
                    <li><a class="hover:text-white" href="{{ route('public.events.index') }}">Kegiatan</a></li>
                    <li><a class="hover:text-white" href="{{ route('public.articles.index') }}">Berita</a></li>
                    <li><a class="hover:text-white" href="{{ route('public.achievements.index') }}">Prestasi</a></li>
                    <li><a class="hover:text-white" href="{{ route('public.contact') }}">Kontak</a></li>
                </ul>
            </nav>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Kontak</p>
                <ul class="mt-3 space-y-2.5 text-gray-400">
                    @if (\App\Models\Setting::get('contact_address'))
                        <li class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />{{ \App\Models\Setting::get('contact_address') }}</li>
                    @endif
                    @if (\App\Models\Setting::get('contact_phone'))
                        <li class="flex items-center gap-2"><x-icon name="phone" class="h-4 w-4 shrink-0 text-brand-400" />{{ \App\Models\Setting::get('contact_phone') }}</li>
                    @endif
                    @if (\App\Models\Setting::get('contact_email'))
                        <li class="flex items-center gap-2"><x-icon name="envelope" class="h-4 w-4 shrink-0 text-brand-400" />{{ \App\Models\Setting::get('contact_email') }}</li>
                    @endif
                </ul>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Media Sosial</p>
                <ul class="mt-3 space-y-2 text-gray-400">
                    @if (\App\Models\Setting::get('social_instagram'))
                        <li><a class="hover:text-white" href="{{ \App\Models\Setting::get('social_instagram') }}" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                    @endif
                    @if (\App\Models\Setting::get('social_youtube'))
                        <li><a class="hover:text-white" href="{{ \App\Models\Setting::get('social_youtube') }}" target="_blank" rel="noopener noreferrer">YouTube</a></li>
                    @endif
                    @if (\App\Models\Setting::get('social_tiktok'))
                        <li><a class="hover:text-white" href="{{ \App\Models\Setting::get('social_tiktok') }}" target="_blank" rel="noopener noreferrer">TikTok</a></li>
                    @endif
                    @if (! \App\Models\Setting::get('social_instagram') && ! \App\Models\Setting::get('social_youtube') && ! \App\Models\Setting::get('social_tiktok'))
                        <li>Segera hadir.</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 px-4 py-4 text-center text-xs text-gray-500">
            © {{ date('Y') }} {{ \App\Models\Setting::get('org_name', config('app.name')) }}. Hak cipta dilindungi.
        </div>
    </footer>

    <script>
        (function () {
            var toggle = document.getElementById('nav-toggle');
            var panel = document.getElementById('nav-mobile');
            if (!toggle || !panel) return;
            var iconOpen = toggle.querySelector('[data-nav-icon-open]');
            var iconClose = toggle.querySelector('[data-nav-icon-close]');
            toggle.addEventListener('click', function () {
                var open = panel.classList.toggle('hidden');
                toggle.setAttribute('aria-expanded', String(!open));
                toggle.setAttribute('aria-label', open ? 'Buka menu navigasi' : 'Tutup menu navigasi');
                if (iconOpen) iconOpen.classList.toggle('hidden', !open);
                if (iconClose) iconClose.classList.toggle('hidden', open);
            });
        })();
    </script>
</body>
</html>
