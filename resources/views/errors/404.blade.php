<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4 font-sans">
    <div class="w-full max-w-md rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm sm:p-10">
        <p class="text-6xl font-extrabold tracking-tight text-gray-800">404</p>
        <h1 class="mt-3 text-lg font-extrabold text-gray-900">Halaman Tidak Ditemukan</h1>
        <p class="mt-2 text-sm leading-relaxed text-gray-600">
            Halaman yang Anda cari tidak ada atau sudah dipindahkan.
        </p>
        <div class="mt-6 flex justify-center gap-3">
            <x-btn href="{{ route('home') }}">Ke Beranda</x-btn>
            @auth
                <x-btn href="{{ route('panel.home') }}" variant="secondary">Ke Dasbor</x-btn>
            @endauth
        </div>
    </div>
</body>
</html>
