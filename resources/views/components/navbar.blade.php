@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $other = $isEn ? 'nl' : 'en';
    $segments = request()->segments();
    $segments = count($segments) ? array_merge([$other], array_slice($segments, 1)) : [$other];
    $switchUrl = url(implode('/', $segments));

    $links = [
        ['features', $isEn ? 'Features' : 'Functionaliteiten'],
        ['docs',     $isEn ? 'Documentation' : 'Documentatie'],
        ['blog',     'Blog'],
        ['changelog', 'Updates'],
        ['status',   'Status'],
    ];
@endphp
<header class="sticky top-0 z-40 border-b border-white/10 bg-[#0b1220]/80 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6" aria-label="{{ $isEn ? 'Main navigation' : 'Hoofdnavigatie' }}">
        <a href="{{ url($locale) }}" class="flex items-center gap-2.5 text-white">
            <x-icon name="pulse" class="h-7 w-7 text-sky-400" />
            <span class="text-lg font-extrabold tracking-tight">ServerScope</span>
        </a>

        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($links as [$slug, $label])
                @php $active = request()->is($locale . '/' . $slug . '*'); @endphp
                <li>
                    <a href="{{ url($locale . '/' . $slug) }}"
                       @if($active) aria-current="page" @endif
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-sky-400/10 text-sky-300' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}"
               class="rounded-lg border border-white/10 px-2.5 py-1.5 text-xs font-bold uppercase tracking-wide text-slate-300 transition hover:bg-white/5 hover:text-white">
                {{ $other }}
            </a>
            <x-button :href="url($locale . '/contact')" class="!px-4 !py-2">Contact</x-button>
        </div>

        {{-- Mobiel menu --}}
        <details class="group relative lg:hidden">
            <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-white/10 text-slate-200 hover:bg-white/5 [&::-webkit-details-marker]:hidden" aria-label="Menu">
                <x-icon name="menu" />
            </summary>
            <div class="absolute right-0 mt-3 w-64 rounded-2xl border border-white/10 bg-slate-900 p-2 shadow-2xl">
                @foreach ($links as [$slug, $label])
                    <a href="{{ url($locale . '/' . $slug) }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">{{ $label }}</a>
                @endforeach
                <a href="{{ url($locale . '/contact') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">Contact</a>
                <a href="{{ $switchUrl }}" class="mt-1 block rounded-lg border-t border-white/10 px-3 py-2.5 text-sm text-sky-300">{{ $isEn ? 'Nederlands' : 'English' }}</a>
            </div>
        </details>
    </nav>
</header>
