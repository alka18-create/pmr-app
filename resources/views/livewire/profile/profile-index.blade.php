<div class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Profil Saya</h1>
            <p class="text-sm text-gray-500">Kelola informasi akun dan data kontak pribadi Anda.</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-700 ring-1 ring-inset ring-brand-200">
                <x-icon name="shield" class="h-3.5 w-3.5" />
                Peran: {{ $role }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom 1 & 2: Informasi Akun & Data Kontak --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-4">
                    <x-icon name="user-circle" class="h-5 w-5 text-brand-600" />
                    <h2 class="text-base font-bold text-gray-900">Informasi Akun</h2>
                </div>

                <form wire:submit="saveProfile" class="mt-5 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Nama Lengkap</label>
                            <input
                                id="name"
                                type="text"
                                wire:model="name"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            >
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Alamat Email</label>
                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            >
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($hasMember)
                        <div class="mt-6 border-t border-gray-100 pt-5">
                            <h3 class="text-sm font-bold text-gray-900">Data Kontak Pribadi & Orang Tua</h3>
                            <p class="text-xs text-gray-500">Data ini dapat Anda perbarui bila terdapat perubahan domisili atau nomor telepon.</p>

                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-gray-600">No. WhatsApp / HP</label>
                                    <input
                                        id="phone"
                                        type="text"
                                        wire:model="phone"
                                        placeholder="08xxxxxxxxxx"
                                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                    >
                                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="email_pribadi" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Email Pribadi</label>
                                    <input
                                        id="email_pribadi"
                                        type="email"
                                        wire:model="email_pribadi"
                                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                    >
                                    @error('email_pribadi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Alamat Tempat Tinggal</label>
                                    <textarea
                                        id="address"
                                        rows="2"
                                        wire:model="address"
                                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                    ></textarea>
                                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="parent_name" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Nama Orang Tua / Wali</label>
                                    <input
                                        id="parent_name"
                                        type="text"
                                        wire:model="parent_name"
                                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                    >
                                    @error('parent_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="parent_phone" class="block text-xs font-bold uppercase tracking-wider text-gray-600">No. HP Orang Tua / Wali</label>
                                    <input
                                        id="parent_phone"
                                        type="text"
                                        wire:model="parent_phone"
                                        placeholder="08xxxxxxxxxx"
                                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                    >
                                    @error('parent_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end pt-4">
                        <x-btn type="submit" wire:loading.attr="disabled" wire:target="saveProfile">
                            <span wire:loading.remove wire:target="saveProfile">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveProfile">Menyimpan&hellip;</span>
                        </x-btn>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kolom 3: Ganti Password & Data Identitas PMR --}}
        <div class="space-y-6">
            {{-- Form Ganti Password --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-4">
                    <x-icon name="shield" class="h-5 w-5 text-brand-600" />
                    <h2 class="text-base font-bold text-gray-900">Ganti Password</h2>
                </div>

                <form wire:submit="savePassword" class="mt-5 space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Password Saat Ini</label>
                        <input
                            id="current_password"
                            type="password"
                            wire:model="current_password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Password Baru</label>
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Konfirmasi Password Baru</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            wire:model="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                    </div>

                    <div class="pt-2">
                        <x-btn type="submit" variant="secondary" class="w-full" wire:loading.attr="disabled" wire:target="savePassword">
                            <span wire:loading.remove wire:target="savePassword">Perbarui Password</span>
                            <span wire:loading wire:target="savePassword">Menyimpan&hellip;</span>
                        </x-btn>
                    </div>
                </form>
            </div>

            {{-- Data Anggota Read-Only --}}
            @if ($hasMember)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-4">
                        <x-icon name="academic" class="h-5 w-5 text-brand-600" />
                        <h2 class="text-base font-bold text-gray-900">Identitas Anggota</h2>
                    </div>

                    <dl class="mt-4 space-y-3 text-xs">
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">Status</dt>
                            <dd class="font-semibold text-gray-900">{{ $status }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">NIS / NISN</dt>
                            <dd class="font-semibold text-gray-900">{{ $nis }} / {{ $nisn }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">Kelas / Angkatan</dt>
                            <dd class="font-semibold text-gray-900">{{ $class_name }} / {{ $batch_name }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">Jabatan</dt>
                            <dd class="font-semibold text-gray-900">{{ $position_name }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">TTL</dt>
                            <dd class="font-semibold text-gray-900">{{ $birth_place }}, {{ $birth_date }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">Jenis Kelamin</dt>
                            <dd class="font-semibold text-gray-900">{{ $gender }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-bold uppercase tracking-wider text-gray-500">Golongan Darah</dt>
                            <dd class="font-semibold text-gray-900">{{ $blood_type }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-[11px] text-gray-400">
                        *Data identitas akademik di atas dikelola oleh Pengurus/Admin. Hubungi pengurus bila terdapat ketidaksesuaian data.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
