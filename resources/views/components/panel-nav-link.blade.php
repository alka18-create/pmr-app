@props(['label', 'route', 'active' => false, 'icon' => 'home', 'nested' => false])

{{--
    Tautan navigasi sidebar panel (gelap). Dipakai langsung maupun
    sebagai anak grup lipat <details> (prop `nested`).
--}}

<a
    href="{{ route($route) }}"
    @if ($active) aria-current="page" @endif
    class="flex items-center gap-3 rounded-xl px-3 transition {{ $nested ? 'py-2 text-[13px]' : 'py-2.5' }} {{ $active ? 'bg-brand-600 font-semibold text-white shadow-sm' : 'font-medium text-gray-300 hover:bg-white/5 hover:text-white' }}"
><x-icon name="{{ $icon }}" class="h-5 w-5 shrink-0 {{ $active ? '' : 'text-gray-500' }}" />{{ $label }}</a>
