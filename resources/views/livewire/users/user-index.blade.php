<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Pengguna</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola akun yang dapat masuk ke panel.</p>
        </div>
        <a
            href="{{ route('admin.users.create') }}"
            class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
        >Pengguna Baru</a>
    </div>

    <x-card>
        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau email&hellip;"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
            <select
                wire:model.live="roleFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="statusFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Nama</th>
                        <th class="py-2 pr-4 font-medium">Email</th>
                        <th class="py-2 pr-4 font-medium">Role</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $user->email }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$user->role->value === 'admin' ? 'red' : 'gray'">
                                    {{ $user->role->value }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$user->status->value === 'active' ? 'green' : 'yellow'">
                                    {{ $user->status->value }}
                                </x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-sm text-gray-700 underline hover:text-gray-900">Edit</a>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $user->id }})"
                                    wire:confirm="Ubah status pengguna ini?"
                                    class="ml-3 text-sm text-yellow-700 underline hover:text-yellow-900"
                                >{{ $user->status->value === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $user->id }})"
                                    wire:confirm="Hapus pengguna ini? Tindakan tidak bisa dibatalkan."
                                    class="ml-3 text-sm text-brand-600 underline hover:text-brand-800"
                                >Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state
                                    title="Tidak ada pengguna"
                                    message="Ubah filter pencarian atau tambah pengguna baru."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-card>
</div>
