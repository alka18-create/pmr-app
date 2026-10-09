<div class="space-y-6">
    <x-page-header
        eyebrow="Akademi"
        title="Nilai Saya"
        description="Riwayat attempt quiz & sertifikat yang sudah terbit."
    />

    @if (! $member)
        <x-alert type="error">Akun Anda belum terhubung ke profil anggota. Hubungi pengurus.</x-alert>
    @endif

    <x-card>
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">Riwayat Quiz</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Quiz</th>
                        <th class="py-2 pr-4 font-medium">Attempt</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Skor</th>
                        <th class="py-2 pr-4 font-medium">Lulus</th>
                        <th class="py-2 pr-4 font-medium">Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($attempts as $attempt)
                        <tr wire:key="att-{{ $attempt->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $attempt->quiz?->title ?? '—' }}</td>
                            <td class="py-3 pr-4 text-gray-600">#{{ $attempt->attempt_no }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$attempt->status->value === 'finished' ? 'green' : ($attempt->status->value === 'expired' ? 'red' : 'gray')">
                                    {{ $attempt->status->value }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4 text-gray-900">{{ $attempt->score ?? '—' }}</td>
                            <td class="py-3 pr-4">
                                @if ($attempt->score !== null && $attempt->quiz)
                                    <x-badge :color="$attempt->score >= $attempt->quiz->pass_score ? 'green' : 'red'">
                                        {{ $attempt->score >= $attempt->quiz->pass_score ? 'lulus' : 'tidak lulus' }}
                                    </x-badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-gray-600">{{ $attempt->finished_at?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada attempt quiz" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-card>
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">Sertifikat Saya</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Kode</th>
                        <th class="py-2 pr-4 font-medium">Tanggal</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($certificates as $cert)
                        <tr wire:key="cert-{{ $cert->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $cert->title }}</td>
                            <td class="py-3 pr-4 font-mono text-xs text-gray-600">{{ $cert->code }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $cert->issued_at?->format('d M Y') }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$cert->is_revoked ? 'red' : 'green'">{{ $cert->is_revoked ? 'dicabut' : 'valid' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                <a href="{{ route('certificates.mine') }}" class="text-sm text-brand-700 underline">Kelola unduhan</a>
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
