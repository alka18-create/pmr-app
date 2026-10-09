<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Import Anggota</h1>
            <p class="mt-1 text-sm text-gray-600">Unggah .xlsx → tinjau ringkasan → konfirmasi insert.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('members.index') }}" class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
            <button
                type="button"
                wire:click="downloadTemplate"
                class="rounded-xl border border-brand-600 px-3 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50"
            >Unduh Template</button>
        </div>
    </div>

    <x-card>
        <form wire:submit="validateFile" class="space-y-4">
            <div>
                <label for="file" class="block text-sm font-semibold text-gray-700">File Excel (.xlsx)</label>
                <input
                    id="file"
                    type="file"
                    wire:model="file"
                    accept=".xlsx,.xls"
                    class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-brand-700"
                >
                @error('file')
                    <p class="mt-1 text-sm text-brand-600">{{ $message }}</p>
                @enderror
                <div wire:loading wire:target="file" class="mt-2 text-sm text-gray-500">Memuat file&hellip;</div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="validateFile"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50"
            >Validasi Baris</button>
        </form>
    </x-card>

    @if ($previewed)
        <x-card title="Ringkasan Validasi">
            <div class="mb-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg bg-green-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-green-700">Valid</p>
                    <p class="mt-1 text-2xl font-semibold text-green-900">{{ count($valid) }}</p>
                </div>
                <div class="rounded-lg bg-brand-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-brand-700">Error</p>
                    <p class="mt-1 text-2xl font-semibold text-brand-900">{{ count($rowErrors) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-600">Total Baris</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ count($valid) + count($rowErrors) }}</p>
                </div>
            </div>

            @if ($rowErrors !== [])
                <div class="mb-4 overflow-x-auto">
                    <p class="mb-2 text-sm font-medium text-brand-700">Baris bermasalah:</p>
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4 font-medium">Baris</th>
                                <th class="py-2 font-medium">Pesan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rowErrors as $error)
                                <tr wire:key="err-{{ $error['row'] }}" class="transition hover:bg-gray-50">
                                    <td class="py-2 pr-4 text-gray-700">{{ $error['row'] }}</td>
                                    <td class="py-2 text-brand-700">{{ $error['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex gap-3 border-t border-gray-100 pt-4">
                <button
                    type="button"
                    wire:click="confirmImport"
                    wire:loading.attr="disabled"
                    wire:target="confirmImport"
                    @disabled(count($valid) === 0)
                    class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50"
                >Konfirmasi Import ({{ count($valid) }} baris)</button>
                <button
                    type="button"
                    wire:click="resetPreview"
                    class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >Batal / Ulang</button>
            </div>
        </x-card>
    @endif
</div>
