{{-- ⌘K / Ctrl+K documentation search. Without JavaScript the search buttons are inert; /docs?q= keeps working. --}}
@props(['locale' => 'nl'])
@php $t = fn (string $key) => trans('site.docs2.' . $key, [], $locale); @endphp
<dialog class="ss-dialog" data-search-dialog aria-label="{{ $t('search_open') }}"
        data-index-url="{{ url($locale . '/docs/search.json') }}"
        data-labels='@json(['none' => $t('no_results'), 'hint' => $t('type_to_search')])'>
    <form method="GET" action="{{ url($locale . '/docs') }}" role="search" class="flex items-center gap-3 border-b border-line px-4">
        <x-ss-icon name="search" class="h-[18px] w-[18px] shrink-0 text-subtle" />
        <label for="ss-search-input" class="sr-only">{{ $t('search_open') }}</label>
        <input id="ss-search-input" name="q" type="search" autocomplete="off" maxlength="100"
               class="h-14 min-w-0 flex-1 bg-transparent text-base text-fg placeholder:text-subtle focus:outline-none"
               placeholder="{{ trans('site.docs.search_placeholder', [], $locale) }}"
               role="combobox" aria-expanded="true" aria-controls="ss-search-results" aria-autocomplete="list" data-search-input>
        <button type="button" class="ss-kbd !text-xs" data-search-close aria-label="{{ trans('site.nav.close', [], $locale) }}">Esc</button>
    </form>
    <ul id="ss-search-results" role="listbox" class="max-h-[22rem] overflow-y-auto p-2" data-search-results aria-label="{{ trans('site.docs.results', [], $locale) }}"></ul>
    <p class="sr-only" aria-live="polite" data-search-status></p>
    <div class="flex items-center gap-4 border-t border-line px-4 py-2.5 text-xs text-subtle">
        <span><kbd class="ss-kbd">↑</kbd> <kbd class="ss-kbd">↓</kbd> {{ $t('navigate') }}</span>
        <span><kbd class="ss-kbd">↵</kbd> {{ $t('open') }}</span>
        <span><kbd class="ss-kbd">Esc</kbd> {{ $t('close') }}</span>
    </div>
</dialog>
