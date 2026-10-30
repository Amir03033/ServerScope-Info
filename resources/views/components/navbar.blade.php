@props(['locale' => null, 'switchUrl' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $other = $locale === 'en' ? 'nl' : 'en';
    $switchUrl ??= url($other);
    $t = fn (string $key) => trans('site.nav.' . $key, [], $locale);
    $app = config('serverscope.app_url');
    // slug => path below /{locale}; '' is the home page.
    $links = ['home' => '', 'features' => 'features', 'docs' => 'docs', 'pricing' => 'pricing', 'changelog' => 'changelog', 'contact' => 'contact'];
    $href = fn (string $path) => url($locale . ($path === '' ? '' : '/' . $path));
    $isActive = fn (string $path) => $path === '' ? request()->is($locale) : request()->is($locale . '/' . $path, $locale . '/' . $path . '/*');
@endphp
<header class="sticky top-0 z-40 border-b border-line bg-bg/80 backdrop-blur-md supports-[backdrop-filter]:bg-bg/70">
    <nav class="ss-container flex h-16 items-center justify-between gap-3" aria-label="{{ $t('label') }}">
        <a href="{{ url($locale) }}" class="flex items-center gap-2 text-fg" aria-label="ServerScope — {{ $t('home') }}">
            <x-ss-icon name="pulse" class="h-6 w-6 text-accent" />
            <span class="text-[15px] font-semibold tracking-tight">ServerScope</span>
        </a>

        <ul class="hidden items-center gap-0.5 lg:flex">
            @foreach ($links as $slug => $path)
                <li>
                    <a href="{{ $href($path) }}"
                       @if ($isActive($path)) aria-current="page" @endif
                       class="rounded-lg px-3 py-2 text-sm transition-colors {{ $isActive($path) ? 'font-semibold text-fg' : 'text-muted hover:text-fg' }}">
                        {{ $t($slug) }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-1.5">
            <button type="button" data-search-open
                    class="hidden h-9 items-center gap-2 rounded-lg border border-line bg-surface pl-2.5 pr-1.5 text-sm text-muted transition-colors hover:border-line-strong hover:text-fg sm:flex"
                    aria-label="{{ $t('search_hint') }}" aria-keyshortcuts="Control+K Meta+K">
                <x-ss-icon name="search" class="h-4 w-4" />
                <span class="hidden xl:inline">{{ $t('search') }}</span>
                <kbd class="ss-kbd" data-shortcut-label>Ctrl K</kbd>
            </button>
            <button type="button" data-search-open class="flex h-9 w-9 items-center justify-center rounded-lg text-muted hover:text-fg sm:hidden" aria-label="{{ $t('search_hint') }}">
                <x-ss-icon name="search" class="h-[18px] w-[18px]" />
            </button>

            <button type="button" data-theme-toggle
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:text-fg"
                    aria-label="{{ $t('theme') }}: {{ $t('theme_system') }}"
                    data-label-prefix="{{ $t('theme') }}"
                    data-labels='@json(['system' => $t('theme_system'), 'light' => $t('theme_light'), 'dark' => $t('theme_dark')])'>
                <x-ss-icon name="monitor" class="h-[18px] w-[18px]" data-theme-icon="system" />
                <x-ss-icon name="sun" class="hidden h-[18px] w-[18px]" data-theme-icon="light" />
                <x-ss-icon name="moon" class="hidden h-[18px] w-[18px]" data-theme-icon="dark" />
            </button>

            <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}" aria-label="{{ $t('switch_to') }}"
               class="hidden h-9 items-center rounded-lg px-2 text-xs font-semibold uppercase tracking-wide text-muted transition-colors hover:text-fg lg:flex">
                {{ $other }}
            </a>

            <div class="hidden items-center gap-1.5 lg:flex">
                <x-button :href="$app . '/login'" variant="ghost" class="!min-h-9 !px-3 !py-1.5" data-app-link title="{{ $t('app_hint') }}">{{ $t('login') }}</x-button>
                <x-button :href="$app" class="!min-h-9 !px-3.5 !py-1.5" data-app-link title="{{ $t('app_hint') }}">{{ $t('get_started') }} <x-ss-icon name="arrow" class="ss-arrow h-3.5 w-3.5" /></x-button>
            </div>

            {{-- Mobile menu (works without JavaScript) --}}
            <details class="group relative lg:hidden" data-mobile-menu>
                <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-line text-fg [&::-webkit-details-marker]:hidden" aria-label="{{ $t('menu') }}">
                    <x-ss-icon name="menu" class="h-[18px] w-[18px] group-open:hidden" />
                    <x-ss-icon name="x" class="hidden h-[18px] w-[18px] group-open:block" />
                </summary>
                <div class="absolute right-0 mt-3 w-[min(18rem,calc(100vw-2rem))] rounded-xl border border-line bg-surface p-2 shadow-lift">
                    @foreach ($links as $slug => $path)
                        <a href="{{ $href($path) }}"
                           @if ($isActive($path)) aria-current="page" @endif
                           class="block rounded-lg px-3 py-2.5 text-sm {{ $isActive($path) ? 'bg-surface-2 font-semibold text-fg' : 'text-muted hover:bg-surface-2 hover:text-fg' }}">{{ $t($slug) }}</a>
                    @endforeach
                    <div class="mt-1 grid gap-2 border-t border-line px-1 pb-1 pt-3">
                        <x-button :href="$app . '/login'" variant="secondary" class="w-full" data-app-link>{{ $t('login') }}</x-button>
                        <x-button :href="$app" class="w-full" data-app-link>{{ $t('get_started') }}</x-button>
                    </div>
                    <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}" class="mt-1 block border-t border-line px-3 py-2.5 text-sm text-accent">{{ $t('switch_to') }}</a>
                </div>
            </details>
        </div>
    </nav>
</header>
