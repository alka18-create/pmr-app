<div class="space-y-6">
    <x-page-header
        eyebrow="Akademi"
        title="Daftar Quiz"
        description="Quiz akademi PMR — kerjakan sesuai batas waktu & attempt."
    >
        @if ($canManage)
            <x-slot:actions>
                <x-btn href="{{ route('quizzes.create') }}">Buat Quiz</x-btn>
            </x-slot:actions>
        @endif
    </x-page-header>

    <x-card>
        <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari judul quiz…"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Judul</th>
                        <th class="py-2 pr-4 font-medium">Materi</th>
                        <th class="py-2 pr-4 font-medium">Waktu</th>
                        <th class="py-2 pr-4 font-medium">Attempt</th>
                        <th class="py-2 pr-4 font-medium">Pass</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($quizzes as $quiz)
                        <tr wire:key="qz-{{ $quiz->id }}" class="transition hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $quiz->title }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $quiz->material?->title ?? '—' }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes.' mnt' : 'Tanpa batas' }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $quiz->max_attempts }}×</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $quiz->pass_score }}</td>
                            <td class="py-3 pr-4">
                                <x-badge :color="$quiz->is_active ? 'green' : 'gray'">{{ $quiz->is_active ? 'aktif' : 'nonaktif' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap py-3 text-right">
                                @if ($quiz->isWithinWindow())
                                    <a href="{{ route('quizzes.take', $quiz) }}" class="text-sm text-brand-700 underline hover:text-brand-900">Kerjakan</a>
                                @endif
                                @if ($canManage)
                                    <a href="{{ route('quizzes.edit', $quiz) }}" class="ml-3 text-sm text-gray-700 underline hover:text-gray-900">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="Belum ada quiz" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $quizzes->links() }}</div>
    </x-card>
</div>
