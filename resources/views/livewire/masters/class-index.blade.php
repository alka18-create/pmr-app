<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Master Kelas</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola kelas (X/XI/XII) anggota PMR.</p>
        </div>
        @include('livewire.masters._nav')
    </div>

    <x-card>
        <form wire:submit="save" class="mb-6 grid max-w-2xl gap-3 sm:grid-cols-[1fr_120px_120px_auto] sm:items-end">
            <div>
                <label for="class-name" class="block text-sm font-semibold text-gray-700">Nama</label>
                <input
                    id="class-name"
                    type="text"
                    wire:model="name"
                    placeholder="XI IPA 1"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                @error('name')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="class-level" class="block text-sm font-semibold text-gray-700">Tingkat</label>
                <select
                    id="class-level"
                    wire:model="level"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </select>
                @error('level')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="class-year" class="block text-sm font-semibold text-gray-700">Tahun</label>
                <input
                    id="class-year"
                    type="number"
                    wire:model="year"
                    class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                @error('year')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50"
                >{{ $editId ? 'Simpan' : 'Tambah' }}</button>
                @if ($editId)
                    <button
                        type="button"
                        wire:click="cancelEdit"
                        class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >Batal</button>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Nama</th>
                        <th class="py-2 pr-4 font-medium">Tingkat</th>
                        <th class="py-2 pr-4 font-medium">Tahun</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($classes as $class)
                        <tr wire:key="class-{{ $class->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $class->name }}</td>
                            <td class="py-3 pr-4">
                                <x-badge color="gray">{{ $class->level }}</x-badge>
                            </td>
                            <td class="py-3 pr-4 text-gray-600">{{ $class->year }}</td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <button type="button" wire:click="edit({{ $class->id }})" class="text-sm text-gray-700 underline hover:text-gray-900">Edit</button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $class->id }})"
                                    wire:confirm="Hapus kelas ini?"
                                    class="ml-3 text-sm text-brand-600 underline hover:text-brand-800"
                                >Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state title="Belum ada kelas" message="Tambahkan kelas di atas." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
