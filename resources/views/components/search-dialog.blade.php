{{-- Instant documentation search (enhanced by resources/js/app.js). The form is the no-JS/Enter fallback. --}}
@props(['locale'])
@php
    $t = fn (string $key) => trans('site.docs.' . $key, [], $locale);
@endphp
<dialog id="docs-search-dialog" aria-labelledby="docs-search-dialog-label"
        data-endpoint="{{ url($locale . '/docs/search.json') }}"
        data-hint="{{ $t('search_hint') }}" data-loading="{{ $t('search_loading') }}"
        data-empty="{{ $t('no_results') }}" data-error="{{ $t('search_error') }}"
        class="m-0 mx-auto mt-[10vh] w-[calc(100%-2rem)] max-w-xl overflow-hidden rounded-2xl border border-white/12 bg-ink-850 p-0 text-slate-300 shadow-2xl shadow-black/60 backdrop:bg-ink-950/70 backdrop:backdrop-blur-sm">
    <form method="GET" action="{{ url($locale . '/docs') }}" role="search">
        <div class="flex items-center gap-3 border-b border-white/10 px-4">
            <x-ss-icon name="search" class="h-5 w-5 shrink-0 text-slate-500" />
            <label id="docs-search-dialog-label" for="docs-search-dialog-input" class="sr-only">{{ $t('search_label') }}</label>
            <input id="docs-search-dialog-input" type="search" name="q" maxlength="100" autocomplete="off" spellcheck="false"
                   role="combobox" aria-expanded="true" aria-controls="docs-search-dialog-results" aria-autocomplete="list"
                   placeholder="{{ $t('search_placeholder') }}"
                   class="h-14 min-w-0 flex-1 bg-transparent text-[15px] text-white placeholder:text-slate-500 focus:outline-none focus-visible:outline-none [&::-webkit-search-cancel-button]:hidden">
            <button type="button" data-search-close class="ss-kbd h-6 cursor-pointer px-1.5 hover:text-white">Esc<span class="sr-only"> — {{ $t('close') }}</span></button>
        </div>
    </form>
    <div class="max-h-[min(60vh,28rem)] overflow-y-auto p-2">
        <div id="docs-search-dialog-results" role="listbox" aria-label="{{ $t('search_label') }}" data-search-results class="grid gap-0.5"></div>
        <p data-search-status role="status" aria-live="polite" class="px-3 py-3 text-sm text-slate-500 data-count:sr-only">{{ $t('search_hint') }}</p>
    </div>
</dialog>
