@props(['locale' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.' . $key, [], $locale);
    $app = config('serverscope.app_url');
    // Only documents with a published version.
    $legal = rescue(fn () => \App\Models\LegalDocument::query()->where('locale', $locale)->orderBy('sort')->get()->filter(fn ($d) => $d->currentVersion() !== null), collect(), report: false);
    $pages = rescue(fn () => \App\Models\Page::query()->visible()->where('locale', $locale)->orderBy('sort')->get(['key', 'locale', 'slug', 'title']), collect(), report: false);
    $about = $pages->filter(fn ($p) => (\App\Models\Page::PAGES[$p->key][3] ?? null) === 'about');
    $useCases = $pages->filter(fn ($p) => (\App\Models\Page::PAGES[$p->key][3] ?? null) === 'landing');
    $cols = [
        $t('footer.product')   => ['features', 'pricing', 'changelog', 'status'],
        $t('footer.resources') => ['docs', 'blog', 'contact'],
    ];
@endphp
<footer class="mt-28 border-t border-line">
    <div class="ss-container grid gap-10 py-14 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr_1fr_1fr]">
        <div>
            <a href="{{ url($locale) }}" class="flex items-center gap-2 text-fg">
                <x-ss-icon name="pulse" class="h-5 w-5 text-accent" />
                <span class="text-sm font-semibold tracking-tight">ServerScope</span>
            </a>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted">{{ $t('footer.tagline') }}</p>
            <x-status-badge :locale="$locale" class="mt-5" />
        </div>

        @foreach ($cols as $heading => $slugs)
            <div>
                <h2 class="text-sm font-semibold text-fg">{{ $heading }}</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($slugs as $slug)
                        <li><a href="{{ url($locale . '/' . $slug) }}" class="text-sm text-muted transition-colors hover:text-fg">{{ $t('nav.' . $slug) }}</a></li>
                    @endforeach
                    @if ($loop->last)
                        @foreach ($about as $page)
                            <li><a href="{{ url($page->path()) }}" class="text-sm text-muted transition-colors hover:text-fg">{{ $page->title }}</a></li>
                        @endforeach
                    @endif
                </ul>
            </div>
        @endforeach

        @if ($useCases->isNotEmpty())
            <div>
                <h2 class="text-sm font-semibold text-fg">{{ $t('footer.use_cases') }}</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($useCases as $page)
                        <li><a href="{{ url($page->path()) }}" class="text-sm text-muted transition-colors hover:text-fg">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($legal->isNotEmpty())
            <div>
                <h2 class="text-sm font-semibold text-fg">{{ $t('footer.legal_title') }}</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($legal as $document)
                        <li><a href="{{ url($document->path()) }}" class="text-sm text-muted transition-colors hover:text-fg">{{ $document->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <h2 class="text-sm font-semibold text-fg">{{ $t('footer.app') }}</h2>
            <ul class="mt-4 space-y-2.5">
                <li><a href="{{ $app }}/login" class="text-sm text-muted transition-colors hover:text-fg" data-app-link>{{ $t('nav.login') }}</a></li>
                <li><a href="{{ $app }}" class="text-sm text-muted transition-colors hover:text-fg" data-app-link>{{ $t('nav.get_started') }}</a></li>
                <li><a href="{{ url($locale . '/docs/getting-started') }}" class="text-sm text-muted transition-colors hover:text-fg">{{ $t('docs.guides') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-line">
        <div class="ss-container flex flex-wrap items-center justify-between gap-3 py-6 text-xs text-subtle">
            <p>© {{ date('Y') }} ServerScope. {{ $t('footer.rights') }}</p>
            <p>{{ $t('footer.legal') }}</p>
        </div>
    </div>
</footer>
