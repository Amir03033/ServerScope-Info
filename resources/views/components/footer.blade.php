@props(['locale' => null, 'switchUrl' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $other = $locale === 'en' ? 'nl' : 'en';
    $t = fn (string $key) => trans('site.' . $key, [], $locale);
    $app = config('serverscope.app_url');
    $docs = collect(app(\App\Services\DocumentationService::class)->all($locale))->keyBy('slug');
    $doc = fn (string $slug) => [url($locale . '/docs/' . $slug), $docs[$slug]['title'] ?? \Illuminate\Support\Str::headline($slug)];
    $cols = [
        $t('footer.product') => [
            [url($locale . '/features'), $t('nav.features')],
            [url($locale . '/changelog'), $t('nav.changelog')],
            [url($locale . '/status'), $t('nav.status')],
            [url($locale . '/security'), $t('nav.security')],
        ],
        $t('footer.docs') => array_values(array_filter([
            $doc('getting-started'), $doc('server-monitoring'), $doc('domain-monitoring'), $doc('api-reference'),
        ])),
        $t('footer.resources') => [
            [url($locale . '/docs'), $t('nav.docs')],
            [url($locale . '/blog'), $t('nav.blog')],
            [url($locale . '/contact'), $t('nav.contact')],
        ],
    ];
@endphp
<footer class="mt-28 border-t border-white/[.07] bg-ink-950/60">
    <div class="ss-container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr]">
        <div class="max-w-sm">
            <a href="{{ url($locale) }}" class="-m-1 inline-flex rounded-lg p-1"><x-logo size="sm" /></a>
            <p class="mt-4 text-sm leading-relaxed text-slate-400">{{ $t('footer.tagline') }}</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <x-button :href="$app . '/login'" variant="secondary" size="sm" data-app-link>{{ $t('nav.login') }}</x-button>
                <a href="{{ $switchUrl ?? url($other) }}" hreflang="{{ $other }}" lang="{{ $other }}"
                   class="ss-btn ss-btn-ghost ss-btn-sm"><x-ss-icon name="language" class="h-4 w-4" /> {{ $t('nav.switch_to') }}</a>
            </div>
        </div>

        @foreach ($cols as $heading => $links)
            <div>
                <h2 class="font-mono text-xs font-medium uppercase tracking-wider text-slate-500">{{ $heading }}</h2>
                <ul class="mt-4 space-y-1">
                    @foreach ($links as [$url, $label])
                        <li><a href="{{ $url }}" class="inline-flex min-h-8 items-center text-sm text-slate-400 transition hover:text-white">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div class="border-t border-white/[.05]">
        <div class="ss-container flex flex-col gap-2 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} ServerScope. {{ $t('footer.rights') }}</p>
            <p class="max-w-xl sm:text-right">{{ $t('footer.note') }}</p>
        </div>
    </div>
</footer>
