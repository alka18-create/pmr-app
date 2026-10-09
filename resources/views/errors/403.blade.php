<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Tidak Ada Akses &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4 font-sans">
    <div class="w-full max-w-md rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm sm:p-10">
        <p class="text-6xl font-extrabold tracking-tight text-brand-600">403</p>
        <h1 class="mt-3 text-lg font-extrabold text-gray-900">Tidak Ada Akses</h1>
        <p class="mt-2 text-sm leading-relaxed text-gray-600">
            Anda tidak memiliki hak mengakses halaman ini. Silakan hubungi admin bila merasa ini keliru.
        </p>
        @isset($exception)
            @if (trim((string) $exception->getMessage()) !== '' && $exception->getMessage() !== 'This action is unauthorized.')
                <p class="mt-3 rounded-xl bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700">{{ $exception->getMessage() }}</p>
            @endif
        @endisset
        <div class="mt-6 flex justify-center gap-3">
            @auth
                <x-btn href="{{ route('panel.home') }}">Ke Dasbor</x-btn>
            @else
                <x-btn href="{{ route('login') }}">Masuk</x-btn>
            @endauth
            <x-btn href="{{ route('home') }}" variant="secondary">Beranda</x-btn>
        </div>
    </div>
</body>
</html>
