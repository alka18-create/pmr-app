<div class="w-full max-w-sm">
    <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
        <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Masuk</h1>
        <p class="mt-1 text-sm text-gray-500">PMR Management System</p>

        <form wire:submit="login" class="mt-6 space-y-4">
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
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                <div class="relative mt-1.5">
                    <x-icon name="shield" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input
                        id="password"
                        type="password"
                        wire:model="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-gray-300 py-2.5 pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Ingat saya
                </label>
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                    Lupa password?
                </a>
            </div>

            <x-btn class="w-full" wire:loading.attr="disabled" wire:target="login">
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Memproses&hellip;</span>
            </x-btn>
        </form>
    </div>
</div>
