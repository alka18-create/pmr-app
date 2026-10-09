<div class="space-y-6" id="quiz-take" data-seconds="{{ $initialSeconds }}">
    <x-page-header
        eyebrow="Akademi · Attempt #{{ $attempt->attempt_no }}"
        :title="$quiz->title"
        :description="'Pass score '.$quiz->pass_score.' · Maksimal '.$quiz->max_attempts.'×.'"
    >
        @if ($attempt->status === \App\Enums\QuizAttemptStatus::Running && $secondsRemaining !== null)
            <x-slot:actions>
                <span class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-800 ring-1 ring-amber-200">
                    <x-icon name="clock" class="h-5 w-5" />
                    Sisa waktu: <span id="quiz-timer">--:--</span>
                </span>
            </x-slot:actions>
        @endif
    </x-page-header>

    @if ($attempt->status !== \App\Enums\QuizAttemptStatus::Running)
        <x-alert type="info">
            Attempt ini sudah berstatus <strong>{{ $attempt->status->value }}</strong>.
            @if ($attempt->status === \App\Enums\QuizAttemptStatus::Expired)
                Waktu habis — jawaban tidak dinilai.
            @elseif ($attempt->status === \App\Enums\QuizAttemptStatus::Finished)
                Skor Anda: <strong>{{ $attempt->score }}</strong>
            @endif
            <a href="{{ route('results.index') }}" class="ml-2 underline">Lihat hasil</a>
        </x-alert>
    @else
        <x-card>
            <form wire:submit="submit" class="space-y-6">
                @error('answers') <x-alert type="error">{{ $message }}</x-alert> @enderror
                @error('quiz') <x-alert type="error">{{ $message }}</x-alert> @enderror

                @foreach ($quiz->questions as $qi => $question)
                    <div wire:key="take-q-{{ $question->id }}" class="border-b border-gray-100 pb-5 last:border-0 last:pb-0">
                        <p class="flex items-start gap-3 font-semibold text-gray-900">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-extrabold text-brand-700">{{ $qi + 1 }}</span>
                            <span>{{ $question->question }}
                                <span class="text-sm font-normal text-gray-500">({{ $question->score }} poin)</span>
                            </span>
                        </p>
                        <div class="mt-3 space-y-2">
                            @foreach ($question->options as $option)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm transition hover:border-brand-300 hover:bg-brand-50/50 has-checked:border-brand-500 has-checked:bg-brand-50 has-checked:ring-1 has-checked:ring-brand-500">
                                    <input type="radio" name="q{{ $question->id }}" value="{{ $option->id }}"
                                        wire:model="answers.{{ $question->id }}"
                                        class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 focus:ring-brand-500">
                                    <span>{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
                    <x-btn wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">Kumpulkan Jawaban</span>
                        <span wire:loading wire:target="submit">Menyimpan…</span>
                    </x-btn>
                    <x-btn href="{{ route('quizzes.index') }}" variant="secondary">Kembali</x-btn>
                </div>
            </form>
        </x-card>
    @endif

    <script>
        (function () {
            var root = document.getElementById('quiz-take');
            var el = document.getElementById('quiz-timer');
            if (!root || !el) return;
            var left = parseInt(root.getAttribute('data-seconds'), 10) || 0;
            function fmt(total) {
                var m = Math.floor(total / 60);
                var s = total % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }
            el.textContent = fmt(left);
            if (left <= 0) return;
            setInterval(function () {
                if (left > 0) {
                    left--;
                    el.textContent = fmt(left);
                }
            }, 1000);
        })();
    </script>
</div>
