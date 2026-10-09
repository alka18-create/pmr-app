<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Inbox Pesan</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $unreadCount }} pesan belum dibaca.</p>
        </div>
        <a href="{{ route('articles.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Kembali</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card title="Daftar Pesan">
            <div class="mb-4">
                <label for="statusFilter" class="sr-only">Filter status</label>
                <select id="statusFilter" wire:model.live="statusFilter"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>

            <ul class="divide-y divide-gray-100">
                @forelse ($messages as $message)
                    <li wire:key="msg-{{ $message->id }}" class="py-3">
                        <button type="button" wire:click="open({{ $message->id }})" class="w-full text-left">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium text-gray-900">{{ $message->subject }}</p>
                                <x-badge :color="$message->status->value === 'unread' ? 'red' : ($message->status->value === 'replied' ? 'green' : 'gray')">
                                    {{ $message->status->value }}
                                </x-badge>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ $message->name }} · {{ $message->email }}</p>
                            <p class="text-xs text-gray-500">{{ $message->created_at->format('d M Y H:i') }}</p>
                        </button>
                    </li>
                @empty
                    <li class="py-4">
                        <x-empty-state title="Inbox kosong" message="Pesan dari form kontak akan muncul di sini." />
                    </li>
                @endforelse
            </ul>

            <div class="mt-4">{{ $messages->links() }}</div>
        </x-card>

        <x-card title="Detail Pesan">
            @if ($open)
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Subjek</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $open->subject }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pengirim</p>
                        <p class="mt-1 text-gray-800">{{ $open->name }} &lt;{{ $open->email }}&gt;</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu</p>
                        <p class="mt-1 text-gray-700">{{ $open->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pesan</p>
                        <p class="mt-1 whitespace-pre-wrap text-gray-800">{{ $open->body }}</p>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <a href="mailto:{{ $open->email }}?subject=Re: {{ $open->subject }}"
                            class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Balas via Email</a>
                        @if ($open->status->value !== 'replied')
                            <button type="button" wire:click="markReplied({{ $open->id }})"
                                class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Tandai Dibalas</button>
                        @endif
                        <button type="button" wire:click="close"
                            class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Tutup</button>
                    </div>
                </div>
            @else
                <x-empty-state title="Pilih pesan" message="Klik salah satu pesan untuk melihat detail." />
            @endif
        </x-card>
    </div>
</div>
