@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $other = $locale === 'en' ? 'nl' : 'en';
    $segments = request()->segments();
    $segments = count($segments) ? array_merge([$other], array_slice($segments, 1)) : [$other];
    $switchUrl = url(implode('/', $segments));
    $canonical = url()->current();
    $alternates = [$locale => $canonical, $other => $switchUrl];
    $ogLocales = ['nl' => 'nl_NL', 'en' => 'en_GB'];
    $defaultDescription = trans('site.meta.description', [], $locale);
    $noindex = config('serverscope.noindex') || $__env->hasSection('noindex');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ServerScope')</title>
    <meta name="description" content="@yield('description', $defaultDescription)">
    <meta name="theme-color" content="#fafafa" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0a0b" media="(prefers-color-scheme: dark)">
    <meta name="color-scheme" content="light dark">
    @if ($noindex)
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Apply the saved theme before the first paint (no flash). --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('ss-theme');
                if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    <link rel="canonical" href="{{ $canonical }}">
    @foreach (['nl', 'en'] as $code)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $alternates[$code] }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternates['nl'] }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ServerScope">
    <meta property="og:title" content="@yield('title', 'ServerScope')">
    <meta property="og:description" content="@yield('description', $defaultDescription)">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $ogLocales[$locale] }}">
    <meta property="og:locale:alternate" content="{{ $ogLocales[$other] }}">
    <meta name="twitter:card" content="summary">

    @php $interFont = rescue(fn () => \Illuminate\Support\Facades\Vite::asset('resources/fonts/inter-latin-wght.woff2'), null, report: false); @endphp
    @if (filled($interFont))
        <link rel="preload" href="{{ $interFont }}" as="font" type="font/woff2" crossorigin>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-bg font-sans text-fg antialiased" data-locale="{{ $locale }}">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-lg focus:bg-accent focus:px-4 focus:py-2 focus:text-on-accent">
        {{ trans('site.skip', [], $locale) }}
    </a>

    <x-staging-banner />

    <x-navbar :locale="$locale" :switch-url="$switchUrl" />

    <main id="main" tabindex="-1" class="focus:outline-none">
        @yield('content')
    </main>

    <x-footer :locale="$locale" />

    <x-search-dialog :locale="$locale" />
</body>
</html>
