<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Audit Log</h1>
        <p class="mt-1 text-sm text-gray-600">Riwayat aksi sensitif di sistem.</p>
    </div>

    <x-card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari model&hellip;"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
            <select
                wire:model.live="actionFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua aksi</option>
                @foreach ($actions as $action)
                    <option value="{{ $action->value }}">{{ $action->value }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="userFilter"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
                <option value="">Semua pengguna</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <input
                type="date"
                wire:model.live="dateFrom"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
            <input
                type="date"
                wire:model.live="dateTo"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            >
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Waktu</th>
                        <th class="py-2 pr-4 font-medium">Pengguna</th>
                        <th class="py-2 pr-4 font-medium">Aksi</th>
                        <th class="py-2 pr-4 font-medium">Model</th>
                        <th class="py-2 pr-4 font-medium">Perubahan</th>
                        <th class="py-2 font-medium">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr wire:key="audit-{{ $log->id }}" class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap py-3 pr-4 text-gray-600">
                                {{ $log->created_at?->format('d M Y H:i') }}
                            </td>
                            <td class="py-3 pr-4 font-medium text-gray-900">
                                {{ $log->user?->name ?? '-' }}
                            </td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$log->action === 'deleted' || $log->action === 'login' ? 'red' : 'gray'">
                                    {{ $log->action }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4 text-gray-600">
                                {{ class_basename($log->model_type ?? '') }}
                                @if ($log->model_id)
                                    <span class="text-gray-400">#{{ $log->model_id }}</span>
                                @endif
                            </td>
                            <td class="max-w-xs py-3 pr-4">
                                @if ($log->old_values || $log->new_values)
                                    <details>
                                        <summary class="cursor-pointer text-sm text-gray-700 underline">Lihat</summary>
                                        <pre class="mt-2 max-h-40 overflow-auto rounded bg-gray-50 p-2 text-xs text-gray-600">@json(['old' => $log->old_values, 'new' => $log->new_values], JSON_PRETTY_PRINT)</pre>
                                    </details>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap py-3 text-gray-500">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state
                                    title="Belum ada audit log"
                                    message="Aksi sensitif akan tercatat di sini."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-card>
</div>
