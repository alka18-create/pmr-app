<div class="mx-auto max-w-lg">
    <div class="mb-6 text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
            <x-icon name="shield" class="h-7 w-7" />
        </span>
        <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-gray-900">Verifikasi Sertifikat</h1>
        <p class="mt-1 text-sm text-gray-600">Masukkan kode sertifikat untuk memeriksa keasliannya.</p>
    </div>

    <x-card>
        <form wire:submit="search" class="space-y-4">
            <div>
                <label for="code" class="block text-sm font-semibold text-gray-700">Kode Sertifikat</label>
                <div class="relative mt-1.5">
                    <x-icon name="qr" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input id="code" type="text" wire:model="code" placeholder="Contoh: ABC123..."
                        class="w-full rounded-xl border border-gray-300 py-2.5 pl-10 pr-3 text-sm font-mono shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                @error('code') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <x-btn class="w-full">
                Periksa Sertifikat
            </x-btn>
        </form>

        @if ($searched)
            <div class="mt-6 border-t border-gray-100 pt-6">
                @if ($isValid && $certificate)
                    <div class="rounded-2xl bg-green-50 p-5 text-center">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-full bg-green-100 text-green-700"><x-icon name="check" class="h-6 w-6" /></div>
                        <p class="font-bold text-green-800">Sertifikat Valid</p>
                        <dl class="mt-4 space-y-1.5 rounded-xl bg-white/70 p-4 text-left text-sm text-gray-700">
                            <div><dt class="inline font-semibold">Nama:</dt> <dd class="inline">{{ $memberName }}</dd></div>
                            <div><dt class="inline font-semibold">Judul:</dt> <dd class="inline">{{ $certificate->title }}</dd></div>
                            <div><dt class="inline font-semibold">Tanggal:</dt> <dd class="inline">{{ $certificate->issued_at?->format('d F Y') }}</dd></div>
                            <div><dt class="inline font-semibold">Kode:</dt> <dd class="inline font-mono">{{ $certificate->code }}</dd></div>
                        </dl>
                    </div>
                @elseif ($certificate && ! $certificate->isValid())
                    <div class="rounded-2xl bg-red-50 p-5 text-center">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-700"><x-icon name="alert" class="h-6 w-6" /></div>
                        <p class="font-bold text-red-800">Sertifikat Dicabut</p>
                        <p class="mt-1 text-sm text-red-700">Sertifikat dengan kode tersebut sudah tidak berlaku.</p>
                    </div>
                @else
                    <div class="rounded-2xl bg-red-50 p-5 text-center">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-700"><x-icon name="alert" class="h-6 w-6" /></div>
                        <p class="font-bold text-red-800">Tidak Ditemukan</p>
                        <p class="mt-1 text-sm text-red-700">Kode sertifikat tidak ditemukan atau salah.</p>
                    </div>
                @endif
            </div>
        @endif
    </x-card>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('home') }}" class="font-semibold text-brand-700 hover:text-brand-800">Kembali ke beranda</a>
    </p>
</div>
