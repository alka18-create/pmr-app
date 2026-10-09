<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Program Kerja</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola program kerja tahunan.</p>
        </div>
    </div>

    <x-card>
        <form wire:submit="save" class="mb-6 grid gap-4 border-b border-gray-100 pb-6 sm:grid-cols-2 lg:grid-cols-5">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-700">Judul</label>
                <input type="text" wire:model="title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Tahun</label>
                <input type="number" wire:model="year" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                @error('year') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Fase</label>
                <input type="text" wire:model="phase" placeholder="Q1" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Status</label>
                <select wire:model="status" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @foreach ($statuses as $item)
                        <option value="{{ $item->value }}">{{ $item->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-5">
                <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                <textarea wire:model="description" rows="2" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
            </div>
            <div class="sm:col-span-2 lg:col-span-5 flex gap-3">
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                    {{ $editId ? 'Perbarui' : 'Simpan' }}
                </button>
                @if ($editId)
                    <button type="button" wire:click="cancelEdit" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</button>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Tahun</th>
                        <th class="py-2 pr-4 font-medium">Fase</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($programs as $program)
                        <tr wire:key="program-{{ $program->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $program->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $program->year }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $program->phase ?? '—' }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$program->status->value === 'selesai' ? 'green' : ($program->status->value === 'berjalan' ? 'yellow' : 'gray')">
                                    {{ $program->status->value }}
                                </x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <button type="button" wire:click="edit({{ $program->id }})" class="text-sm text-gray-700 underline hover:text-gray-900">Edit</button>
                                <button type="button" wire:click="delete({{ $program->id }})" wire:confirm="Hapus program ini?"
                                    class="ml-3 text-sm text-brand-600 underline hover:text-brand-800">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Belum ada program" message="Tambahkan program kerja." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
