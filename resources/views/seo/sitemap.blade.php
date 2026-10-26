{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $url)
@foreach (['nl', 'en'] as $locale)
@isset($url[$locale])
    <url>
        <loc>{{ $url[$locale] }}</loc>
@isset($url['lastmod'])
        <lastmod>{{ $url['lastmod'] }}</lastmod>
@endisset
@if (isset($url['nl'], $url['en']))
        <xhtml:link rel="alternate" hreflang="nl" href="{{ $url['nl'] }}"/>
        <xhtml:link rel="alternate" hreflang="en" href="{{ $url['en'] }}"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $url['nl'] }}"/>
@endif
    </url>
@endisset
@endforeach
@endforeach
</urlset>
