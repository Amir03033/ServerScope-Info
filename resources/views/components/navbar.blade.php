@props(['locale' => null, 'switchUrl' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $other = $locale === 'en' ? 'nl' : 'en';
    $switchUrl ??= url($other);
    $t = fn (string $key) => trans('site.nav.' . $key, [], $locale);
    $app = config('serverscope.app_url');
    // label key => [path below /{locale}, paths that count as active]
    $links = [
        'features' => ['features', ['features']],
        'docs' => ['docs', ['docs', 'docs/*']],
        'api' => ['docs/api-reference', []],
        'blog' => ['blog', ['blog', 'blog/*']],
        'changelog' => ['changelog', ['changelog']],
        'contact' => ['contact', ['contact']],
    ];
    $href = fn (string $path) => url($locale . '/' . $path);
    $isActive = function (string $key, array $patterns) use ($locale): bool {
        // The API link points into the docs; it is active on that page only,
        // and then the general docs link is not.
        $apiPage = request()->is($locale . '/docs/api-reference');

        if ($key === 'api') {
            return $apiPage;
        }

        if ($key === 'docs' && $apiPage) {
            return false;
        }

        return $patterns !== [] && request()->is(...array_map(fn ($p) => $locale . '/' . $p, $patterns));
    };
@endphp
<header data-header class="ss-header sticky top-0 z-50 border-b border-transparent backdrop-blur-md">
    <nav class="ss-container flex h-16 items-center justify-between gap-3" aria-label="{{ $t('label') }}">
        <a href="{{ url($locale) }}" class="-m-1 rounded-lg p-1" aria-label="ServerScope — {{ $t('home') }}">
            <x-logo />
        </a>

        <ul class="hidden items-center gap-0.5 lg:flex">
            @foreach ($links as $key => [$path, $patterns])
                {{-- The API link also sits in the docs sidebar; it needs the room of xl screens. --}}
                <li @class(['hidden xl:block' => $key === 'api'])>
                    <a href="{{ $href($path) }}" class="ss-nav-link" @if ($isActive($key, $patterns)) aria-current="page" @endif>{{ $t($key) }}</a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-1.5">
            <a href="{{ url($locale . '/docs') }}" data-search-open
               class="hidden h-9 items-center gap-2 rounded-lg border border-white/10 bg-white/[.03] pl-2.5 pr-1.5 text-sm text-slate-400 transition hover:border-white/20 hover:text-slate-200 sm:inline-flex">
                <x-ss-icon name="search" class="h-4 w-4" />
                <span class="hidden whitespace-nowrap xl:inline" aria-hidden="true">{{ trans('site.docs.search_button', [], $locale) }}…</span>
                <span class="sr-only">{{ $t('search') }}</span>
                <kbd class="ss-kbd ml-1 hidden xl:inline-flex">/</kbd>
            </a>

            <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}" title="{{ $t('switch_to') }}"
               class="hidden h-9 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold uppercase tracking-wider text-slate-400 transition hover:bg-white/5 hover:text-white lg:inline-flex">
                <x-ss-icon name="language" class="h-4 w-4" />
                <span aria-hidden="true">{{ $other }}</span>
                <span class="sr-only">{{ $t('switch_to') }}</span>
            </a>

            <x-button :href="$app . '/login'" variant="ghost" size="sm" class="hidden lg:inline-flex" data-app-link title="{{ $t('app_hint') }}">{{ $t('login') }}</x-button>
            <x-button :href="url($locale . '/docs/getting-started')" size="sm" class="hidden sm:inline-flex" :arrow="true">{{ $t('get_started') }}</x-button>

            {{-- Mobile menu toggle: without JavaScript the panel below is a plain list (see noscript). --}}
            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu"
                    class="group inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/10 text-slate-200 transition hover:bg-white/5 lg:hidden">
                <span class="sr-only">{{ $t('menu') }}</span>
                <x-ss-icon name="menu" class="h-5 w-5 group-aria-expanded:hidden" />
                <x-ss-icon name="x" class="hidden h-5 w-5 group-aria-expanded:block" />
            </button>
        </div>
    </nav>

    <div id="mobile-menu" class="ss-mobile-panel absolute inset-x-0 top-full border-b border-white/10 bg-ink-900 shadow-2xl shadow-black/50 lg:hidden">
        <div class="ss-container max-h-[calc(100dvh-4rem)] overflow-y-auto py-4">
            <ul class="grid gap-1">
                @foreach ($links as $key => [$path, $patterns])
                    <li>
                        <a href="{{ $href($path) }}" @if ($isActive($key, $patterns)) aria-current="page" @endif
                           class="flex min-h-11 items-center justify-between rounded-lg px-3 text-[15px] font-medium text-slate-300 hover:bg-white/5 hover:text-white aria-[current=page]:bg-sky-400/10 aria-[current=page]:text-sky-200">
                            {{ $t($key) }} <x-ss-icon name="chevron" class="h-4 w-4 text-slate-600" />
                        </a>
                    </li>
                @endforeach
                <li>
                    <a href="{{ url($locale . '/docs') }}" data-search-open class="flex min-h-11 items-center gap-2 rounded-lg px-3 text-[15px] font-medium text-slate-300 hover:bg-white/5 hover:text-white">
                        <x-ss-icon name="search" class="h-4 w-4" /> {{ $t('search') }}
                    </a>
                </li>
            </ul>
            <div class="mt-4 grid gap-2 border-t border-white/10 pt-4 sm:grid-cols-2">
                <x-button :href="$app . '/login'" variant="secondary" class="w-full" data-app-link>{{ $t('login') }}</x-button>
                <x-button :href="url($locale . '/docs/getting-started')" class="w-full">{{ $t('get_started') }}</x-button>
            </div>
            <a href="{{ $switchUrl }}" hreflang="{{ $other }}" lang="{{ $other }}"
               class="mt-3 flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm text-slate-400 hover:bg-white/5 hover:text-white">
                <x-ss-icon name="language" class="h-4 w-4" /> {{ $t('switch_to') }}
            </a>
        </div>
    </div>
    <noscript>
        <style>#mobile-menu { position: static; visibility: visible; opacity: 1; transform: none; } [data-menu-toggle] { display: none; }</style>
    </noscript>
</header>

<x-search-dialog :locale="$locale" />
