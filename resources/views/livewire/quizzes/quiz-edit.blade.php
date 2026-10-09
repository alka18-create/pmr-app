<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Edit Quiz</h1>
        <p class="mt-1 text-sm text-gray-600">Perbarui pengaturan & soal quiz.</p>
    </div>

    <x-card>
        <form wire:submit="save" class="max-w-4xl space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-semibold text-gray-700">Judul</label>
                    <input id="title" type="text" wire:model="title" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('title') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea id="description" wire:model="description" rows="2" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                    @error('description') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="material_id" class="block text-sm font-semibold text-gray-700">Materi terkait</label>
                    <select id="material_id" wire:model="material_id" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">— Tanpa materi —</option>
                        @foreach ($materials as $m)
                            <option value="{{ $m->id }}">{{ $m->title }}</option>
                        @endforeach
                    </select>
                    @error('material_id') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="time_limit_minutes" class="block text-sm font-semibold text-gray-700">Batas waktu (menit)</label>
                    <input id="time_limit_minutes" type="number" wire:model="time_limit_minutes" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('time_limit_minutes') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_attempts" class="block text-sm font-semibold text-gray-700">Batas attempt</label>
                    <input id="max_attempts" type="number" wire:model="max_attempts" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('max_attempts') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pass_score" class="block text-sm font-semibold text-gray-700">Nilai kelulusan</label>
                    <input id="pass_score" type="number" wire:model="pass_score" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('pass_score') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="starts_at" class="block text-sm font-semibold text-gray-700">Mulai</label>
                    <input id="starts_at" type="datetime-local" wire:model="starts_at" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('starts_at') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ends_at" class="block text-sm font-semibold text-gray-700">Selesai</label>
                    <input id="ends_at" type="datetime-local" wire:model="ends_at" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    @error('ends_at') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 sm:col-span-2">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Quiz aktif
                </label>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-medium text-gray-900">Soal ({{ count($questions) }})</h2>
                    <button type="button" wire:click="addQuestion" class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">+ Tambah Soal</button>
                </div>

                @foreach ($questions as $qi => $question)
                    <div wire:key="q-{{ $qi }}" class="mb-4 rounded-lg border border-gray-200 p-4">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Soal #{{ $qi + 1 }}</label>
                                <textarea wire:model="questions.{{ $qi }}.question" rows="2" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                            </div>
                            <div class="w-28">
                                <label class="block text-sm font-semibold text-gray-700">Skor</label>
                                <input type="number" wire:model="questions.{{ $qi }}.score" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            </div>
                            <button type="button" wire:click="removeQuestion({{ $qi }})" wire:confirm="Hapus soal ini?"
                                class="mt-6 text-sm text-brand-600 underline">Hapus</button>
                        </div>
                        @error("questions.{$qi}") <p class="mb-2 text-sm text-brand-600">{{ $message }}</p> @enderror
                        @error("questions.{$qi}.question") <p class="mb-2 text-sm text-brand-600">{{ $message }}</p> @enderror

                        <div class="space-y-2">
                            @foreach ($question['options'] as $oi => $option)
                                <div wire:key="q-{{ $qi }}-o-{{ $oi }}" class="flex items-center gap-2">
                                    <input type="radio" name="correct-{{ $qi }}" wire:click="setCorrect({{ $qi }}, {{ $oi }})"
                                        @checked($option['is_correct']) class="text-brand-600 focus:ring-brand-500">
                                    <input type="text" wire:model="questions.{{ $qi }}.options.{{ $oi }}.text"
                                        placeholder="Opsi {{ $oi + 1 }}"
                                        class="flex-1 rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                    <button type="button" wire:click="removeOption({{ $qi }}, {{ $oi }})"
                                        class="text-xs text-gray-400 hover:text-brand-600">✕</button>
                                </div>
                                @error("questions.{$qi}.options.{$oi}.text")
                                    <p class="text-sm text-brand-600">{{ $message }}</p>
                                @enderror
                            @endforeach
                            <button type="button" wire:click="addOption({{ $qi }})" class="text-sm text-brand-700 underline">+ Opsi</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-4">
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Simpan Perubahan</button>
                <a href="{{ route('quizzes.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </x-card>
</div>
