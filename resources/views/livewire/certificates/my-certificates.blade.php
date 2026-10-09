<div class="space-y-6">
    <x-page-header
        eyebrow="Akademi"
        title="Sertifikat Saya"
        description="Unduh sertifikat yang sudah terbit atas nama Anda."
    >
        <x-slot:actions>
            <x-btn href="{{ route('verify.certificate') }}" variant="secondary">Verifikasi Publik</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Kode</th>
                        <th class="py-2 pr-4 font-medium">Tanggal Terbit</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($certificates as $cert)
                        <tr wire:key="myc-{{ $cert->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $cert->title }}</td>
                            <td class="py-3 pr-4 font-mono text-xs text-gray-600">{{ $cert->code }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $cert->issued_at?->format('d M Y') }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$cert->is_revoked ? 'red' : 'green'">{{ $cert->is_revoked ? 'dicabut' : 'valid' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                @if (! $cert->is_revoked && $cert->file_path)
                                    <button type="button" wire:click="download({{ $cert->id }})"
                                        class="text-sm text-brand-700 underline hover:text-brand-900">Unduh PDF</button>
                                @elseif (! $cert->is_revoked)
                                    <span class="text-xs text-gray-400">PDF diproses…</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                                <a href="{{ route('verify.certificate', $cert->code) }}" target="_blank"
                                    class="ml-3 text-sm text-gray-700 underline">Verifikasi</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Belum ada sertifikat" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
