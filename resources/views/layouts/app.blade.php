@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $other = $locale === 'en' ? 'nl' : 'en';

    // Same page in the other language. Controllers pass $switchUrl when the
    // path differs or the page has no translation ($translated = false).
    if (! isset($switchUrl)) {
        $segments = request()->segments();
        $segments = count($segments) ? array_merge([$other], array_slice($segments, 1)) : [$other];
        $switchUrl = url(implode('/', $segments));
    }
    $translated ??= true;

    $canonical = url()->current();
    $alternates = [$locale => $canonical, $other => $switchUrl];
    $ogLocales = ['nl' => 'nl_NL', 'en' => 'en_GB'];
    $defaultDescription = trans('site.meta.description', [], $locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ServerScope')</title>
    <meta name="description" content="@yield('description', $defaultDescription)">
    <meta name="theme-color" content="#0a0f1c">
    <meta name="color-scheme" content="dark">
    @hasSection('robots')<meta name="robots" content="@yield('robots')">@endif

    <link rel="canonical" href="{{ $canonical }}">
    @if ($translated)
        @foreach (['nl', 'en'] as $code)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ $alternates[$code] }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $alternates['nl'] }}">
    @endif

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="ServerScope">
    <meta property="og:title" content="@yield('title', 'ServerScope')">
    <meta property="og:description" content="@yield('description', $defaultDescription)">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ trans('site.meta.og_alt', [], $locale) }}">
    <meta property="og:locale" content="{{ $ogLocales[$locale] }}">
    @if ($translated)<meta property="og:locale:alternate" content="{{ $ogLocales[$other] }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @stack('meta')

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-ink-900 font-sans text-slate-300 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-lg focus:bg-sky-400 focus:px-4 focus:py-2 focus:font-semibold focus:text-slate-950">
        {{ trans('site.skip', [], $locale) }}
    </a>

    <x-navbar :locale="$locale" :switch-url="$switchUrl" />

    <main id="main" tabindex="-1" class="outline-none">
        @yield('content')
    </main>

    <x-footer :locale="$locale" :switch-url="$switchUrl" />

    @stack('end')
</body>
</html>
