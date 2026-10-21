<div class="w-full max-w-sm">
    <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
        <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Reset Password</h1>
        <p class="mt-1 text-sm text-gray-500">Buat password baru untuk akun Anda.</p>

        <form wire:submit="resetPassword" class="mt-6 space-y-4">
            <input type="hidden" wire:model="token">

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                <div class="relative mt-1.5">
                    <x-icon name="envelope" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        placeholder="nama@pmr.test"
                        class="w-full rounded-xl border border-gray-300 py-2.5 pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                </div>
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">Password Baru</label>
                <div class="relative mt-1.5">
                    <x-icon name="shield" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input
                        id="password"
                        type="password"
                        wire:model="password"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full rounded-xl border border-gray-300 py-2.5 pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>
                <div class="relative mt-1.5">
                    <x-icon name="shield" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input
                        id="password_confirmation"
                        type="password"
                        wire:model="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                        class="w-full rounded-xl border border-gray-300 py-2.5 pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                </div>
            </div>

            <x-btn class="w-full" wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">Perbarui Password</span>
                <span wire:loading wire:target="resetPassword">Memproses&hellip;</span>
            </x-btn>
        </form>

        <div class="mt-6 border-t border-gray-100 pt-4 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                ← Batal & kembali ke login
            </a>
        </div>
    </div>
</div>
