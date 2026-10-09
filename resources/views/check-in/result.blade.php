<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md rounded-xl bg-white p-8 text-center shadow">
        @if ($success)
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl text-green-700">✓</div>
            <h1 class="text-lg font-semibold text-gray-900">Berhasil Check-in</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
            @isset($member)
                <p class="mt-4 text-sm text-gray-900">Anggota: <strong>{{ $member->full_name }}</strong></p>
            @endisset
        @else
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl text-red-700">!</div>
            <h1 class="text-lg font-semibold text-gray-900">Check-in Gagal</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
        @endif
        <a href="{{ route('home') }}" class="mt-6 inline-block text-sm text-red-700 underline hover:text-red-900">Kembali ke beranda</a>
    </div>
</body>
</html>
