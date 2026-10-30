@php $grouped = $others->groupBy(fn ($a) => data_get($a, 'category.name') ?? ''); @endphp
<div class="space-y-5 {{ $class }}">
    @foreach ($grouped as $category => $items)
        <div>
            @if ($category !== '')
                <p class="px-3 text-xs font-semibold uppercase tracking-[.08em] text-subtle">{{ $category }}</p>
            @endif
            <ol class="mt-1.5 space-y-0.5">
                @foreach ($items as $item)
                    @php $slug = data_get($item, 'slug'); @endphp
                    <li>
                        <a href="{{ url($lang . '/docs/' . $slug) }}"
                           @if ($slug === $current) aria-current="page" @endif
                           class="block rounded-lg px-3 py-1.5 text-sm transition-colors {{ $slug === $current ? 'bg-surface-2 font-semibold text-fg' : 'text-muted hover:text-fg' }}">
                            {{ data_get($item, 'title', \Illuminate\Support\Str::headline($slug)) }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    @endforeach
</div>
