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

    <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 text-xs text-blue-900 sm:p-5">
        <h2 class="mb-2 text-sm font-bold text-blue-950">📋 Panduan Format Template Excel:</h2>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-3 shadow-xs">
                <span class="font-bold text-gray-800">Jenis Kelamin:</span>
                <p class="mt-0.5 text-gray-600"><code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">laki</code> atau <code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">perempuan</code></p>
                <p class="text-[10px] text-gray-400">Toleran: Laki-laki, L, P, Perempuan</p>
            </div>
            <div class="rounded-xl bg-white p-3 shadow-xs">
                <span class="font-bold text-gray-800">Status Anggota:</span>
                <p class="mt-0.5 text-gray-600"><code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">aktif</code>, <code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">alumni</code>, <code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">nonaktif</code></p>
                <p class="text-[10px] text-gray-400">Otomatis 'aktif' jika dikosongkan</p>
            </div>
            <div class="rounded-xl bg-white p-3 shadow-xs">
                <span class="font-bold text-gray-800">Angkatan & Kelas:</span>
                <p class="mt-0.5 text-gray-600">Harus sama persis dengan yang ada di menu <span class="font-semibold text-gray-800">Master Data</span></p>
                <p class="text-[10px] text-gray-400">Contoh: "Angkatan 2026", "X-A"</p>
            </div>
            <div class="rounded-xl bg-white p-3 shadow-xs">
                <span class="font-bold text-gray-800">Format Tanggal:</span>
                <p class="mt-0.5 text-gray-600"><code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">YYYY-MM-DD</code> atau <code class="rounded bg-gray-100 px-1 py-0.5 text-brand-700">DD/MM/YYYY</code></p>
                <p class="text-[10px] text-gray-400">Contoh: 2008-05-15 atau 15/05/2008</p>
            </div>
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
