<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Edit Anggota</h1>
        <p class="mt-1 text-sm text-gray-600">Ubah data <strong>{{ $member->full_name }}</strong>.</p>
    </div>

    <x-card>
        <form wire:submit="save" class="max-w-3xl space-y-6">
            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-gray-700">Data Publik</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                        <input id="full_name" type="text" wire:model="full_name" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('full_name') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-gray-700">Jenis Kelamin</label>
                        <select id="gender" wire:model="gender" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @foreach ($genders as $item)
                                <option value="{{ $item->value }}">{{ $item->value }}</option>
                            @endforeach
                        </select>
                        @error('gender') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="batch_id" class="block text-sm font-semibold text-gray-700">Angkatan</label>
                        <select id="batch_id" wire:model="batch_id" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <option value="">Pilih angkatan</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                            @endforeach
                        </select>
                        @error('batch_id') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="class_id" class="block text-sm font-semibold text-gray-700">Kelas</label>
                        <select id="class_id" wire:model="class_id" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="position_id" class="block text-sm font-semibold text-gray-700">Jabatan</label>
                        <select id="position_id" wire:model="position_id" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <option value="">Tanpa jabatan</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                        @error('position_id') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="joined_at" class="block text-sm font-semibold text-gray-700">Bergabung Sejak</label>
                        <input id="joined_at" type="date" wire:model="joined_at" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('joined_at') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                        <select id="status" wire:model="status" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @foreach ($statuses as $item)
                                <option value="{{ $item->value }}">{{ $item->value }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4 border-t border-gray-100 pt-4">
                <legend class="text-sm font-semibold text-gray-700">Data Internal (RAHASIA)</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nis" class="block text-sm font-semibold text-gray-700">NIS</label>
                        <input id="nis" type="text" wire:model="nis" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('nis') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="nisn" class="block text-sm font-semibold text-gray-700">NISN</label>
                        <input id="nisn" type="text" wire:model="nisn" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('nisn') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="birth_date" class="block text-sm font-semibold text-gray-700">Tanggal Lahir</label>
                        <input id="birth_date" type="date" wire:model="birth_date" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('birth_date') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="birth_place" class="block text-sm font-semibold text-gray-700">Tempat Lahir</label>
                        <input id="birth_place" type="text" wire:model="birth_place" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('birth_place') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700">No. HP</label>
                        <input id="phone" type="text" wire:model="phone" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('phone') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email_pribadi" class="block text-sm font-semibold text-gray-700">Email Pribadi</label>
                        <input id="email_pribadi" type="email" wire:model="email_pribadi" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('email_pribadi') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="blood_type" class="block text-sm font-semibold text-gray-700">Gol. Darah</label>
                        <select id="blood_type" wire:model="blood_type" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <option value="">—</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                        @error('blood_type') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="parent_name" class="block text-sm font-semibold text-gray-700">Nama Orang Tua</label>
                        <input id="parent_name" type="text" wire:model="parent_name" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('parent_name') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="parent_phone" class="block text-sm font-semibold text-gray-700">No. HP Orang Tua</label>
                        <input id="parent_phone" type="text" wire:model="parent_phone" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        @error('parent_phone') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-gray-700">Alamat</label>
                        <textarea id="address" wire:model="address" rows="2" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                        @error('address') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-sm font-semibold text-gray-700">Catatan</label>
                        <textarea id="notes" wire:model="notes" rows="2" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
                        @error('notes') <p class="mt-1 text-sm text-brand-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            <div class="flex gap-3 border-t border-gray-100 pt-4">
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan&hellip;</span>
                </button>
                <a href="{{ route('members.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </x-card>
</div>
