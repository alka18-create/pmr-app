<a
    href="{{ route('notifications.index') }}"
    wire:poll.60s
    class="relative rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
    title="Notifikasi"
>
    Notifikasi
    @if ($count > 0)
        <span class="absolute -right-2 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-600 px-1 text-xs font-semibold text-white">{{ $count }}</span>
    @endif
</a>
