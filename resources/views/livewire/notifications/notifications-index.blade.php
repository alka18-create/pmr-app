<div class="space-y-6">
    <x-page-header
        eyebrow="Saya"
        :title="'Notifikasi'"
        :description="$unreadCount.' notifikasi belum dibaca.'"
    >
        @if ($unreadCount > 0)
            <x-slot:actions>
                <x-btn wire:click="markAllRead" type="button">Tandai semua dibaca</x-btn>
            </x-slot:actions>
        @endif
    </x-page-header>

    <x-card>
        <ul class="divide-y divide-gray-100">
            @forelse ($notifications as $notification)
                <li wire:key="notif-{{ $notification->id }}" class="py-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium {{ $notification->read_at ? 'text-gray-600' : 'text-gray-900' }}">
                                {{ $notification->data['title'] ?? 'Notifikasi' }}
                                @unless ($notification->read_at)
                                    <span class="ml-1 inline-block h-2 w-2 rounded-full bg-brand-600 align-middle" title="Belum dibaca"></span>
                                @endunless
                            </p>
                            <p class="mt-0.5 text-sm text-gray-600">{{ $notification->data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            @if (! empty($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}" class="text-sm text-brand-700 underline hover:text-brand-900">Buka</a>
                            @endif
                            @unless ($notification->read_at)
                                <button
                                    type="button"
                                    wire:click="markRead('{{ $notification->id }}')"
                                    class="text-sm text-gray-500 underline hover:text-gray-700"
                                >Tandai dibaca</button>
                            @endunless
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-6">
                    <x-empty-state title="Belum ada notifikasi" message="Kegiatan baru, nilai, dan sertifikat akan muncul di sini." />
                </li>
            @endforelse
        </ul>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </x-card>
</div>
