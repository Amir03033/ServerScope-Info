@props(['locale' => null, 'switchUrl' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $other = $locale === 'en' ? 'nl' : 'en';
    $switchUrl ??= url($other);
    $t = fn (string $key) => trans('site.nav.' . $key, [], $locale);
    $links = ['features', 'docs', 'blog', 'changelog', 'status'];
    $isActive = fn (string $slug) => request()->is($locale . '/' . $slug . '*');
@endphp
<header class="sticky top-0 z-40 border-b border-white/10 bg-[#0b1220]/80 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6" aria-label="{{ $t('label') }}">
        <a href="{{ url($locale) }}" class="flex items-center gap-2.5 text-white">
            <x-ss-icon name="pulse" class="h-7 w-7 text-sky-400" />
            <span class="text-lg font-extrabold tracking-tight">ServerScope</span>
        </a>

        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($links as $slug)
                <li>
                    <a href="{{ url($locale . '/' . $slug) }}"
                       @if ($isActive($slug)) aria-current="page" @endif
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $isActive($slug) ? 'bg-sky-400/10 text-sky-300' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        {{ $t($slug) }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}" aria-label="{{ $t('switch_to') }}"
               class="rounded-lg border border-white/10 px-2.5 py-1.5 text-xs font-bold uppercase tracking-wide text-slate-300 transition hover:bg-white/5 hover:text-white">
                {{ $other }}
            </a>
            <x-button :href="url($locale . '/contact')" class="!px-4 !py-2">{{ $t('contact') }}</x-button>
        </div>

        {{-- Mobile menu --}}
        <details class="group relative lg:hidden">
            <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-white/10 text-slate-200 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/70 [&::-webkit-details-marker]:hidden" aria-label="{{ $t('menu') }}">
                <x-ss-icon name="menu" />
            </summary>
            <div class="absolute right-0 mt-3 w-64 rounded-2xl border border-white/10 bg-slate-900 p-2 shadow-2xl">
                @foreach (array_merge($links, ['contact']) as $slug)
                    <a href="{{ url($locale . '/' . $slug) }}"
                       @if ($isActive($slug)) aria-current="page" @endif
                       class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ $isActive($slug) ? 'bg-sky-400/10 text-sky-300' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">{{ $t($slug) }}</a>
                @endforeach
                <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}" class="mt-1 block rounded-lg border-t border-white/10 px-3 py-2.5 text-sm text-sky-300">{{ $t('switch_to') }}</a>
            </div>
        </details>
    </nav>
</header>
