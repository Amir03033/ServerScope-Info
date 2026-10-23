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
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ServerScope')</title>
    <meta name="description" content="@yield('description', $defaultDescription)">
    <meta name="theme-color" content="#0b1220">

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

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif; }
        body { background: #0b1220; }
        .ss-grid {
            background-image:
                linear-gradient(to right, rgba(148,163,184,.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148,163,184,.06) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse at top, #000 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse at top, #000 30%, transparent 75%);
        }
        .ss-prose { color: #cbd5e1; line-height: 1.75; max-width: 70ch; }
        .ss-prose h1, .ss-prose h2, .ss-prose h3 { color: #f8fafc; font-weight: 700; letter-spacing: -.01em; margin: 2em 0 .6em; }
        .ss-prose h1 { font-size: 2rem; margin-top: 0; }
        .ss-prose h2 { font-size: 1.5rem; }
        .ss-prose h3 { font-size: 1.2rem; }
        .ss-prose p, .ss-prose ul, .ss-prose ol, .ss-prose pre { margin: 0 0 1.1em; }
        .ss-prose ul { list-style: disc; padding-left: 1.4em; }
        .ss-prose ol { list-style: decimal; padding-left: 1.4em; }
        .ss-prose a { color: #38bdf8; text-decoration: underline; text-underline-offset: 3px; }
        .ss-prose code { background: rgba(255,255,255,.08); border-radius: .375rem; padding: .1em .4em; font-size: .9em; }
        .ss-prose pre { background: #060b14; border: 1px solid rgba(255,255,255,.1); border-radius: .75rem; padding: 1rem 1.25rem; overflow-x: auto; }
        .ss-prose pre code { background: none; padding: 0; }
        @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
    </style>
</head>
<body class="min-h-screen bg-[#0b1220] font-sans text-slate-300 antialiased selection:bg-sky-400/30">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-sky-400 focus:px-4 focus:py-2 focus:text-slate-950">
        {{ trans('site.skip', [], $locale) }}
    </a>

    <x-navbar :locale="$locale" :switch-url="$switchUrl" />

    <main id="main">
        @yield('content')
    </main>

    <x-footer :locale="$locale" />
</body>
</html>
