<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Profil Organisasi</h1>
            <p class="mt-1 text-sm text-gray-600">Informasi yang tampil di halaman publik.</p>
        </div>
        <a
            href="{{ route('admin.settings.index') }}"
            class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
        >Kembali ke Pengaturan</a>
    </div>

    <x-card>
        <form wire:submit="save" class="max-w-2xl space-y-4">
            <div>
                <label for="org-name" class="block text-sm font-semibold text-gray-700">Nama Organisasi</label>
                <input
                    id="org-name"
                    type="text"
                    wire:model="name"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                @error('name')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="org-history" class="block text-sm font-semibold text-gray-700">Sejarah</label>
                <textarea
                    id="org-history"
                    wire:model="history"
                    rows="4"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                ></textarea>
                @error('history')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="org-vision" class="block text-sm font-semibold text-gray-700">Visi</label>
                    <textarea
                        id="org-vision"
                        wire:model="vision"
                        rows="3"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    ></textarea>
                    @error('vision')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="org-mission" class="block text-sm font-semibold text-gray-700">Misi</label>
                    <textarea
                        id="org-mission"
                        wire:model="mission"
                        rows="3"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    ></textarea>
                    @error('mission')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <fieldset class="border-t border-gray-100 pt-4">
                <legend class="text-sm font-semibold text-gray-900">Kontak</legend>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="contact-email" class="block text-sm font-semibold text-gray-700">Email</label>
                        <input
                            id="contact-email"
                            type="email"
                            wire:model="email"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="contact-phone" class="block text-sm font-semibold text-gray-700">Telepon</label>
                        <input
                            id="contact-phone"
                            type="text"
                            wire:model="phone"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <label for="contact-address" class="block text-sm font-semibold text-gray-700">Alamat</label>
                    <input
                        id="contact-address"
                        type="text"
                        wire:model="address"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                    @error('address')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <fieldset class="border-t border-gray-100 pt-4">
                <legend class="text-sm font-semibold text-gray-900">Media Sosial</legend>
                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="social-instagram" class="block text-sm font-semibold text-gray-700">Instagram</label>
                        <input
                            id="social-instagram"
                            type="url"
                            wire:model="instagram"
                            placeholder="https://instagram.com/..."
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('instagram')
                            <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="social-youtube" class="block text-sm font-semibold text-gray-700">YouTube</label>
                        <input
                            id="social-youtube"
                            type="url"
                            wire:model="youtube"
                            placeholder="https://youtube.com/..."
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('youtube')
                            <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="social-tiktok" class="block text-sm font-semibold text-gray-700">TikTok</label>
                        <input
                            id="social-tiktok"
                            type="url"
                            wire:model="tiktok"
                            placeholder="https://tiktok.com/..."
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('tiktok')
                            <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-gray-100 pt-4">
                <legend class="text-sm font-semibold text-gray-900">7 Prinsip Dasar</legend>
                <p class="mt-1 text-xs text-gray-500">Tampil di halaman Profil publik. Kosongkan semua untuk kembali ke redaksi resmi.</p>
                <div class="mt-3 space-y-3">
                    @foreach ($principles as $index => $principle)
                        <div wire:key="principle-{{ $index }}" class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-gray-500">Prinsip {{ $index + 1 }}</p>
                                <button
                                    type="button"
                                    wire:click="removePrinciple({{ $index }})"
                                    class="text-xs font-medium text-brand-600 hover:text-brand-800"
                                >Hapus</button>
                            </div>
                            <label for="principle-title-{{ $index }}" class="sr-only">Judul prinsip {{ $index + 1 }}</label>
                            <input
                                id="principle-title-{{ $index }}"
                                type="text"
                                wire:model="principles.{{ $index }}.title"
                                placeholder="Judul prinsip"
                                class="mt-2 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            >
                            @error("principles.{$index}.title")
                                <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                            @enderror
                            <label for="principle-body-{{ $index }}" class="sr-only">Isi prinsip {{ $index + 1 }}</label>
                            <textarea
                                id="principle-body-{{ $index }}"
                                wire:model="principles.{{ $index }}.body"
                                rows="2"
                                placeholder="Uraian prinsip"
                                class="mt-2 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            ></textarea>
                            @error("principles.{$index}.body")
                                <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
                <button
                    type="button"
                    wire:click="addPrinciple"
                    class="mt-3 rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >+ Tambah prinsip</button>
            </fieldset>

            <fieldset class="border-t border-gray-100 pt-4">
                <legend class="text-sm font-semibold text-gray-900">Logo</legend>
                <div class="mt-3 flex items-center gap-4">
                    @if ($logoPath)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}"
                            alt="Logo organisasi"
                            class="h-20 w-20 rounded-lg border border-gray-200 object-contain"
                        >
                    @endif
                    <div class="flex-1">
                        <input
                            type="file"
                            wire:model="logo"
                            accept="image/jpeg,image/png,image/webp"
                            class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100"
                        >
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, atau WebP. Maksimal 2MB.</p>
                        @error('logo')
                            <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="logo" class="mt-1 text-xs text-gray-500">Mengunggah&hellip;</div>
                    </div>
                </div>
            </fieldset>

            <div class="flex gap-3 border-t border-gray-100 pt-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan&hellip;</span>
                </button>
                <a
                    href="{{ route('admin.settings.index') }}"
                    class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >Batal</a>
            </div>
        </form>
    </x-card>
</div>
