<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk · '.config('app.name') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#e31837">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <div class="relative hidden overflow-hidden bg-brand-800 lg:flex lg:w-[45%] lg:flex-col lg:justify-between lg:p-12">
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/60 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-brand-950/60 blur-3xl" aria-hidden="true"></div>

            <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                <span class="flex h-12 items-center rounded-2xl bg-white px-3 py-1.5 shadow" aria-hidden="true">
                    <img src="{{ asset('images/logo-pmi.png') }}" alt="" class="h-9 w-auto object-contain">
                </span>
                <span class="leading-tight">
                    <span class="block font-extrabold tracking-tight text-white">{{ \App\Models\Setting::get('org_name', config('app.name')) }}</span>
                    <span class="block text-xs font-medium uppercase tracking-widest text-brand-200">Palang Merah Remaja</span>
                </span>
            </a>

            <div class="relative">
                <p class="text-xs font-bold uppercase tracking-widest text-brand-200">Panel Anggota & Pengurus</p>
                <p class="mt-3 max-w-md text-3xl font-extrabold leading-tight tracking-tight text-white">Satu pintu untuk kegiatan, absensi, dan prestasi PMR.</p>
                <ul class="mt-8 space-y-4 text-sm text-brand-100">
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10"><x-icon name="calendar" class="h-5 w-5" /></span>
                        Pantau jadwal kegiatan dan rekap kehadiranmu
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10"><x-icon name="academic" class="h-5 w-5" /></span>
                        Kerjakan quiz materi dan kumpulkan sertifikat
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10"><x-icon name="shield" class="h-5 w-5" /></span>
                        Data internal anggota terlindungi & terverifikasi
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-brand-200">© {{ date('Y') }} {{ \App\Models\Setting::get('org_name', config('app.name')) }}</p>
        </div>

        <div class="relative flex flex-1 items-center justify-center overflow-hidden px-4 py-10 sm:px-8">
            <div class="pointer-events-none absolute -left-20 top-10 h-64 w-64 rounded-full bg-brand-100 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-16 bottom-10 h-72 w-72 rounded-full bg-brand-50 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0" aria-hidden="true" style="background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96'%3E%3Cpath d='M42 36h12v6h6v12h-6v6H42v-6h-6V42h6z' fill='%23e31837' fill-opacity='0.05'/%3E%3C/svg%3E&quot;);"></div>
            <div class="relative w-full max-w-sm">
                <svg viewBox="0 0 220 24" class="mx-auto mb-6 h-6 w-44 text-brand-200" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12h60l8-10 10 20 8-14 4 4h40l8-10 10 20 8-14 4 4h48" />
                </svg>
                <a href="{{ route('home') }}" class="mb-6 flex items-center gap-2.5 lg:hidden">
                    <span class="flex h-10 items-center rounded-xl bg-white px-2.5 py-1 shadow-sm ring-1 ring-gray-200" aria-hidden="true">
                        <img src="{{ asset('images/logo-pmi.png') }}" alt="" class="h-7 w-auto object-contain">
                    </span>
                    <span class="text-sm font-extrabold tracking-tight text-gray-900">{{ \App\Models\Setting::get('org_name', config('app.name')) }}</span>
                </a>

                @if (session('success'))
                    <div class="mb-4"><x-alert type="success">{{ session('success') }}</x-alert></div>
                @endif
                @if (session('error'))
                    <div class="mb-4"><x-alert type="error">{{ session('error') }}</x-alert></div>
                @endif

                {{ $slot }}

                <p class="mt-6 text-center text-xs text-gray-500">
                    <a href="{{ route('home') }}" class="font-medium text-brand-700 hover:text-brand-800">← Kembali ke beranda</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
