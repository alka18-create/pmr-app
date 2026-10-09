<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Pengaturan</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola konfigurasi sistem per grup.</p>
        </div>
        <a
            href="{{ route('admin.settings.organization') }}"
            class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
        >Profil Organisasi</a>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($groups as $group)
            <button
                type="button"
                wire:click="$set('activeGroup', '{{ $group->value }}')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $activeGroup === $group->value ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}"
            >{{ ucfirst($group->value) }}</button>
        @endforeach
    </div>

    <x-card>
        <form wire:submit="save" class="mb-6 space-y-4 border-b border-gray-100 pb-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="setting-key" class="block text-sm font-semibold text-gray-700">Key</label>
                    <input
                        id="setting-key"
                        type="text"
                        wire:model="key"
                        placeholder="contoh: org_tagline"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                    @error('key')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="setting-value" class="block text-sm font-semibold text-gray-700">Nilai</label>
                    <input
                        id="setting-value"
                        type="text"
                        wire:model="value"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                    @error('value')
                        <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            @error('group')
                <p class="text-sm text-brand-600">{{ $message }}</p>
            @enderror
            <div class="flex gap-3">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Perbarui' : 'Simpan' }}</span>
                    <span wire:loading wire:target="save">Menyimpan&hellip;</span>
                </button>
                @if ($editId)
                    <button
                        type="button"
                        wire:click="cancelEdit"
                        class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >Batal Edit</button>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Key</th>
                        <th class="py-2 pr-4 font-medium">Nilai</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($settings as $setting)
                        <tr wire:key="setting-{{ $setting->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $setting->key }}</td>
                            <td class="max-w-md py-3 pr-4 text-gray-600">
                                <span class="break-all">{{ \Illuminate\Support\Str::limit($setting->value ?? '-', 120) }}</span>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="edit({{ $setting->id }})"
                                    class="text-sm text-gray-700 underline hover:text-gray-900"
                                >Edit</button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $setting->id }})"
                                    wire:confirm="Hapus pengaturan ini?"
                                    class="ml-3 text-sm text-brand-600 underline hover:text-brand-800"
                                >Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-empty-state
                                    title="Belum ada pengaturan"
                                    message="Tambahkan key-value untuk grup {{ $activeGroup }}."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
