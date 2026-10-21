<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Konfirmasi Check-in · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md rounded-xl bg-white p-8 text-center shadow">
        <h1 class="text-lg font-semibold text-gray-900">Konfirmasi Check-in</h1>
        <p class="mt-2 text-sm text-gray-600">Kegiatan: <strong>{{ $event->title }}</strong></p>
        <p class="mt-1 text-sm text-gray-600">Anggota: <strong>{{ $member->full_name }}</strong></p>

        <form method="POST" action="{{ route('check-in.store', $token) }}" class="mt-6">
            @csrf
            <button type="submit"
                class="w-full rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800">
                Catat Kehadiran Saya
            </button>
        </form>
        <a href="{{ route('home') }}" class="mt-4 inline-block text-sm text-gray-500 underline hover:text-gray-700">Batal</a>
    </div>
</body>
</html>
