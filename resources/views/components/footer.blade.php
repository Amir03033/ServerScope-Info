@props(['locale' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.' . $key, [], $locale);
    $app = config('serverscope.app_url');
    $cols = [
        $t('footer.product')   => ['features', 'pricing', 'changelog', 'status'],
        $t('footer.resources') => ['docs', 'blog', 'contact'],
    ];
@endphp
<footer class="mt-28 border-t border-line">
    <div class="ss-container grid gap-10 py-14 md:grid-cols-[1.5fr_1fr_1fr_1fr]">
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
                </ul>
            </div>
        @endforeach

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
