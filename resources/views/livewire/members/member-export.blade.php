<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Export Anggota</h1>
            <p class="mt-1 text-sm text-gray-600">Unduh data anggota ke Excel sesuai filter (dicatat di audit log).</p>
        </div>
        <a href="{{ route('members.index') }}" class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
    </div>

    <x-card>
        <div class="mb-4 grid max-w-2xl gap-3 sm:grid-cols-3">
            <div>
                <label for="classFilter" class="block text-sm font-semibold text-gray-700">Kelas</label>
                <select id="classFilter" wire:model="classFilter" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="batchFilter" class="block text-sm font-semibold text-gray-700">Angkatan</label>
                <select id="batchFilter" wire:model="batchFilter" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">Semua angkatan</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="statusFilter" class="block text-sm font-semibold text-gray-700">Status</label>
                <select id="statusFilter" wire:model="statusFilter" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button
            type="button"
            wire:click="download"
            wire:loading.attr="disabled"
            wire:target="download"
            class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="download">Download Excel</span>
            <span wire:loading wire:target="download">Menyiapkan&hellip;</span>
        </button>
    </x-card>
</div>
