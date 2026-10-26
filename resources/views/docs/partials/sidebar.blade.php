{{-- Documentation navigation grouped by category. Expects $groups, $lang and $current. --}}
@php
    $labels = trans('site.docs.categories', [], $lang);
@endphp
<div class="space-y-6">
    @foreach ($groups as $category => $items)
        {{-- Categories start open; readers can collapse the ones they do not need. --}}
        <details class="group/cat" open>
            <summary class="flex cursor-pointer list-none items-center justify-between rounded-md px-3 py-1 font-mono text-[11px] font-medium uppercase tracking-wider text-slate-500 hover:text-slate-300 [&::-webkit-details-marker]:hidden">
                {{ $labels[$category] ?? $category }}
                <x-ss-icon name="chevron" class="h-3.5 w-3.5 rotate-90 transition group-open/cat:-rotate-90" />
            </summary>
            <ul class="mt-1.5 space-y-px border-l border-white/[.07] pl-0">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ url($lang . '/docs/' . $item['slug']) }}"
                           @if ($item['slug'] === $current) aria-current="page" @endif
                           class="-ml-px flex min-h-9 items-center border-l border-transparent py-1.5 pl-4 pr-3 text-sm text-slate-400 transition hover:border-white/30 hover:text-white aria-[current=page]:border-sky-400 aria-[current=page]:font-medium aria-[current=page]:text-sky-200">
                            {{ $item['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
    @endforeach
</div>
