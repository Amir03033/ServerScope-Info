{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($entries as $entry)
@foreach ($entry['alternates'] as $locale => $loc)
    <url>
        <loc>{{ $loc }}</loc>
@if ($entry['lastmod'])
        <lastmod>{{ $entry['lastmod']->toAtomString() }}</lastmod>
@endif
@if (count($entry['alternates']) > 1)
@foreach ($entry['alternates'] as $altLocale => $altLoc)
        <xhtml:link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altLoc }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $entry['alternates']['nl'] ?? $loc }}"/>
@endif
    </url>
@endforeach
@endforeach
</urlset>
