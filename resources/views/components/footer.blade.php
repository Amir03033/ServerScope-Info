@props(['locale' => null])
@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.' . $key, [], $locale);
    $cols = [
        $t('footer.product')   => ['features', 'status', 'changelog'],
        $t('footer.resources') => ['docs', 'blog', 'contact'],
    ];
@endphp
<footer class="mt-24 border-t border-white/10 bg-[#080e1a]">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
        <div>
            <a href="{{ url($locale) }}" class="flex items-center gap-2.5 text-white">
                <x-ss-icon name="pulse" class="h-6 w-6 text-sky-400" />
                <span class="text-base font-extrabold tracking-tight">ServerScope</span>
            </a>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-slate-400">{{ $t('footer.tagline') }}</p>
        </div>

        @foreach ($cols as $heading => $slugs)
            <div>
                <h2 class="text-sm font-semibold text-white">{{ $heading }}</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($slugs as $slug)
                        <li><a href="{{ url($locale . '/' . $slug) }}" class="text-sm text-slate-400 transition hover:text-sky-300">{{ $t('nav.' . $slug) }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div class="border-t border-white/5">
        <p class="mx-auto max-w-6xl px-4 py-6 text-xs text-slate-500 sm:px-6">© {{ date('Y') }} ServerScope. {{ $t('footer.rights') }}</p>
    </div>
</footer>
