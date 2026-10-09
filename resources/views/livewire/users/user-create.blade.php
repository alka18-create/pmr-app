<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Pengguna Baru</h1>
        <p class="mt-1 text-sm text-gray-600">Buat akun yang dapat masuk ke panel.</p>
    </div>

    <x-card>
        <form wire:submit="save" class="max-w-xl space-y-4">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700">Nama</label>
                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                @error('name')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    autocomplete="off"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                @error('email')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                <input
                    id="password"
                    type="password"
                    wire:model="password"
                    autocomplete="new-password"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                @error('password')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700">Role</label>
                    <select
                        id="role"
                        wire:model="role"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                        @foreach ($roles as $item)
                            <option value="{{ $item->value }}">{{ ucfirst($item->value) }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                    <select
                        id="status"
                        wire:model="status"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                        @foreach ($statuses as $item)
                            <option value="{{ $item->value }}">{{ $item->value }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

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
                    href="{{ route('admin.users.index') }}"
                    class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >Batal</a>
            </div>
        </form>
    </x-card>
</div>
