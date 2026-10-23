{{--
    Single place where pre-rendered article HTML is printed unescaped.

    SECURITY: $html MUST be trusted or sanitised upstream (e.g. Str::markdown() with
    ['html_input' => 'strip', 'allow_unsafe_links' => false] or an HTML purifier).
    The controller/service that produces it was not part of the reviewed upload; see
    REFACTOR_NOTES.md. Centralising the raw output here means a sanitiser can be
    added in exactly one place.
--}}
@props(['html' => ''])
<div {{ $attributes->merge(['class' => 'ss-prose']) }}>{!! $html !!}</div>
