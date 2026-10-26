<ol class="space-y-0.5 {{ $class }}">
    @foreach ($others as $item)
        @php $slug = data_get($item, 'slug'); @endphp
        <li>
            <a href="{{ url($lang . '/docs/' . $slug) }}"
               @if ($slug === $current) aria-current="page" @endif
               class="flex gap-2.5 rounded-lg px-3 py-2 text-sm transition {{ $slug === $current ? 'bg-sky-400/10 font-semibold text-sky-300' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span class="w-4 shrink-0 text-right tabular-nums text-slate-600">{{ $loop->iteration }}</span>
                <span>{{ data_get($item, 'title', \Illuminate\Support\Str::headline($slug)) }}</span>
            </a>
        </li>
    @endforeach
</ol>
