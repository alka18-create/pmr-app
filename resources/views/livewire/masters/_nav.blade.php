<div class="flex flex-wrap gap-2 text-sm">
    <a
        href="{{ route('admin.master.batches') }}"
        class="rounded-lg px-3 py-1.5 {{ request()->routeIs('admin.master.batches') ? 'bg-brand-600 font-medium text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
    >Angkatan</a>
    <a
        href="{{ route('admin.master.classes') }}"
        class="rounded-lg px-3 py-1.5 {{ request()->routeIs('admin.master.classes') ? 'bg-brand-600 font-medium text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
    >Kelas</a>
    <a
        href="{{ route('admin.master.positions') }}"
        class="rounded-lg px-3 py-1.5 {{ request()->routeIs('admin.master.positions') ? 'bg-brand-600 font-medium text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
    >Jabatan</a>
</div>
