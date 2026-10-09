<div class="space-y-6">
    <x-page-header
        eyebrow="Hubungi Kami"
        title="Kontak"
        description="Hubungi kami untuk informasi, kerja sama, atau pertanyaan."
    />

    <div class="grid items-start gap-6 lg:grid-cols-2">
        <section aria-label="Informasi kontak" class="space-y-4">
            <x-card title="Informasi Organisasi">
                <dl class="space-y-4 text-sm text-gray-700">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="map-pin" class="h-5 w-5" /></span>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">Alamat</dt>
                            <dd class="mt-0.5">{{ $address ?: '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="phone" class="h-5 w-5" /></span>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">Telepon</dt>
                            <dd class="mt-0.5">{{ $phone ?: '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="envelope" class="h-5 w-5" /></span>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">Email</dt>
                            <dd class="mt-0.5">{{ $email ?: '—' }}</dd>
                        </div>
                    </div>
                </dl>

                @if ($instagram || $youtube || $tiktok)
                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Media Sosial</p>
                        <ul class="mt-2 flex flex-wrap gap-2 text-sm">
                            @if ($instagram)
                                <li><a class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1.5 font-medium text-gray-700 hover:border-brand-200 hover:text-brand-700" href="{{ $instagram }}" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                            @endif
                            @if ($youtube)
                                <li><a class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1.5 font-medium text-gray-700 hover:border-brand-200 hover:text-brand-700" href="{{ $youtube }}" target="_blank" rel="noopener noreferrer">YouTube</a></li>
                            @endif
                            @if ($tiktok)
                                <li><a class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1.5 font-medium text-gray-700 hover:border-brand-200 hover:text-brand-700" href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer">TikTok</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </x-card>

            <x-card title="Peta">
                <div class="rounded-xl bg-gray-100 p-8 text-center text-sm text-gray-500">
                    Peta lokasi (opsional) — tambahkan embed peta sesuai alamat sekolah.
                </div>
            </x-card>
        </section>

        <section aria-labelledby="form-kontak">
            <x-card>
                <h2 id="form-kontak" class="text-base font-extrabold tracking-tight text-gray-900">Kirim Pesan</h2>
                <form wire:submit="submit" class="mt-4 space-y-4" novalidate>
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700">Nama</label>
                        <input id="name" type="text" wire:model="name" autocomplete="name"
                            class="mt-1.5 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                        <input id="email" type="email" wire:model="email" autocomplete="email"
                            class="mt-1.5 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700">Subjek</label>
                        <input id="subject" type="text" wire:model="subject"
                            class="mt-1.5 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('subject') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="body" class="block text-sm font-semibold text-gray-700">Pesan</label>
                        <textarea id="body" wire:model="body" rows="5"
                            class="mt-1.5 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                        @error('body') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Honeypot --}}
                    <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                    </div>

                    <x-btn wire:loading.attr="disabled">
                        Kirim Pesan
                        <x-icon name="arrow-right" class="h-4 w-4" wire:loading.remove />
                    </x-btn>
                </form>
            </x-card>
        </section>
    </div>
</div>
