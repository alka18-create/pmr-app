<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Panel · '.config('app.name') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#e31837">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen">
        <div id="panel-overlay" class="fixed inset-0 z-30 hidden bg-gray-900/50 md:hidden" aria-hidden="true"></div>

        <aside id="panel-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-gray-900 text-gray-300 transition-transform duration-200 md:static md:z-auto md:translate-x-0 md:shrink-0" aria-label="Navigasi panel">
            <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-white/10 px-5">
                <span class="flex h-9 items-center rounded-lg bg-white px-2 py-1" aria-hidden="true">
                    <img src="{{ asset('images/logo-pmi.png') }}" alt="" class="h-6 w-auto object-contain">
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-extrabold tracking-tight text-white">{{ \App\Models\Setting::get('org_name', config('app.name')) }}</p>
                    <p class="text-[11px] font-medium uppercase tracking-widest text-brand-400">Panel Organisasi</p>
                </div>
            </div>

            @php
                $navSaya = [
                    ['label' => 'Dasbor', 'route' => 'panel.home', 'is' => 'panel.home', 'icon' => 'home'],
                    ['label' => 'Materi', 'route' => 'materials.index', 'is' => 'materials.index', 'icon' => 'book'],
                    ['label' => 'Quiz', 'route' => 'quizzes.index', 'is' => ['quizzes.index', 'quizzes.take'], 'icon' => 'academic'],
                    ['label' => 'Nilai', 'route' => 'results.index', 'is' => 'results.*', 'icon' => 'chart'],
                    ['label' => 'Sertifikat Saya', 'route' => 'certificates.mine', 'is' => 'certificates.mine', 'icon' => 'shield'],
                    ['label' => 'Notifikasi', 'route' => 'notifications.index', 'is' => 'notifications.*', 'icon' => 'bell'],
                ];
                $navKelolaTop = [
                    ['label' => 'Area Pengurus', 'route' => 'panel.pengurus', 'is' => 'panel.pengurus', 'icon' => 'sparkles'],
                    ['label' => 'Anggota', 'route' => 'members.index', 'is' => 'members.*', 'icon' => 'users'],
                    ['label' => 'Kegiatan', 'route' => 'events.index', 'is' => 'events.*', 'icon' => 'calendar'],
                ];
                $navGroups = [
                    [
                        'label' => 'Akademi',
                        'icon' => 'academic',
                        'items' => [
                            ['label' => 'Kelola Materi', 'route' => 'materials.manage', 'is' => 'materials.manage', 'icon' => 'book'],
                            ['label' => 'Kelola Quiz', 'route' => 'quizzes.create', 'is' => ['quizzes.create', 'quizzes.edit'], 'icon' => 'academic'],
                            ['label' => 'Kelola Sertifikat', 'route' => 'certificates.manage', 'is' => 'certificates.manage', 'icon' => 'shield'],
                        ],
                    ],
                    [
                        'label' => 'Konten',
                        'icon' => 'newspaper',
                        'items' => [
                            ['label' => 'Berita', 'route' => 'articles.index', 'is' => 'articles.*', 'icon' => 'newspaper'],
                            ['label' => 'Galeri', 'route' => 'gallery.index', 'is' => 'gallery.*', 'icon' => 'photo'],
                            ['label' => 'Prestasi', 'route' => 'achievements.index', 'is' => 'achievements.*', 'icon' => 'trophy'],
                            ['label' => 'Program', 'route' => 'programs.index', 'is' => 'programs.*', 'icon' => 'document'],
                            ['label' => 'Inbox', 'route' => 'messages.index', 'is' => 'messages.*', 'icon' => 'envelope'],
                        ],
                    ],
                    [
                        'label' => 'Data & Laporan',
                        'icon' => 'chart',
                        'items' => [
                            ['label' => 'Rekap Absensi', 'route' => 'attendance.recap', 'is' => 'attendance.*', 'icon' => 'clock'],
                            ['label' => 'Laporan', 'route' => 'reports.index', 'is' => 'reports.*', 'icon' => 'chart'],
                            ['label' => 'Statistik Anggota', 'route' => 'stats.members', 'is' => 'stats.members', 'icon' => 'chart'],
                            ['label' => 'Statistik Kegiatan', 'route' => 'stats.events', 'is' => 'stats.events', 'icon' => 'chart'],
                        ],
                    ],
                ];
                $navAdmin = [
                    ['label' => 'Area Admin', 'route' => 'panel.admin', 'is' => 'panel.admin', 'icon' => 'cog'],
                    ['label' => 'Pengguna', 'route' => 'admin.users.index', 'is' => 'admin.users.*', 'icon' => 'user-circle'],
                    ['label' => 'Master', 'route' => 'admin.master.batches', 'is' => 'admin.master.*', 'icon' => 'users'],
                    ['label' => 'Pengaturan', 'route' => 'admin.settings.index', 'is' => 'admin.settings.*', 'icon' => 'wrench'],
                    ['label' => 'Audit Log', 'route' => 'admin.audit-log.index', 'is' => 'admin.audit-log.*', 'icon' => 'shield'],
                ];
            @endphp

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5 text-sm">
                <div>
                    <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-500">Saya</p>
                    <div class="space-y-0.5">
                        @foreach ($navSaya as $item)
                            <x-panel-nav-link
                                :label="$item['label']"
                                :route="$item['route']"
                                :icon="$item['icon']"
                                :active="request()->routeIs((array) $item['is'])"
                            />
                        @endforeach
                    </div>
                </div>

                @can('access-pengurus-area')
                    <div>
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-500">Kelola</p>
                        <div class="space-y-0.5">
                            @foreach ($navKelolaTop as $item)
                                <x-panel-nav-link
                                    :label="$item['label']"
                                    :route="$item['route']"
                                    :icon="$item['icon']"
                                    :active="request()->routeIs((array) $item['is'])"
                                />
                            @endforeach

                            @foreach ($navGroups as $group)
                                @php
                                    $groupActive = collect($group['items'])->contains(fn ($i) => request()->routeIs((array) $i['is']));
                                @endphp
                                <details @if ($groupActive) open @endif class="group rounded-xl">
                                    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 font-medium text-gray-300 transition hover:bg-white/5 hover:text-white [&::-webkit-details-marker]:hidden {{ $groupActive ? 'bg-white/5 text-white' : '' }}">
                                        <x-icon name="{{ $group['icon'] }}" class="h-5 w-5 shrink-0 text-gray-500" />
                                        {{ $group['label'] }}
                                        <x-icon name="chevron-down" class="ml-auto h-4 w-4 shrink-0 text-gray-500 transition group-open:rotate-180" />
                                    </summary>
                                    <div class="ml-5 mt-0.5 space-y-0.5 border-l border-white/10 pl-2">
                                        @foreach ($group['items'] as $item)
                                            <x-panel-nav-link
                                                :label="$item['label']"
                                                :route="$item['route']"
                                                :icon="$item['icon']"
                                                :active="request()->routeIs((array) $item['is'])"
                                                nested
                                            />
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endcan

                @can('access-admin-area')
                    <div>
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-500">Admin</p>
                        <div class="space-y-0.5">
                            @foreach ($navAdmin as $item)
                                <x-panel-nav-link
                                    :label="$item['label']"
                                    :route="$item['route']"
                                    :icon="$item['icon']"
                                    :active="request()->routeIs((array) $item['is'])"
                                />
                            @endforeach
                        </div>
                    </div>
                @endcan
            </nav>

            <div class="shrink-0 border-t border-white/10 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/5 hover:text-white"
                    ><x-icon name="arrow-right" class="h-5 w-5 rotate-180 text-gray-500" />Keluar</button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white/90 px-4 backdrop-blur sm:px-6">
                <button
                    type="button"
                    id="panel-menu-btn"
                    aria-expanded="false"
                    aria-controls="panel-sidebar"
                    aria-label="Buka navigasi"
                    class="rounded-xl border border-gray-300 p-2 text-gray-700 hover:bg-gray-50 md:hidden"
                >
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
                <div class="ml-auto flex items-center gap-3 sm:gap-4">
                    <livewire:notifications.notifications-badge />
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-extrabold text-white" aria-hidden="true">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="hidden text-right sm:block">
                            <p class="max-w-40 truncate text-sm font-bold leading-tight text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] font-medium uppercase tracking-widest text-gray-500">{{ auth()->user()->role->value }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 space-y-4 p-4 sm:p-6">
                @if (session('success'))
                    <x-alert type="success">{{ session('success') }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert type="error">{{ session('error') }}</x-alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        (function () {
            var btn = document.getElementById('panel-menu-btn');
            var sidebar = document.getElementById('panel-sidebar');
            var overlay = document.getElementById('panel-overlay');
            if (!btn || !sidebar || !overlay) return;
            function setOpen(open) {
                sidebar.classList.toggle('-translate-x-full', !open);
                overlay.classList.toggle('hidden', !open);
                btn.setAttribute('aria-expanded', String(open));
                btn.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
            }
            btn.addEventListener('click', function () {
                setOpen(sidebar.classList.contains('-translate-x-full'));
            });
            overlay.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setOpen(false);
            });
        })();
    </script>

    {{-- PWA: daftarkan service worker hanya di area panel/anggota (docs/13 §7.1) --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
</body>
</html>
