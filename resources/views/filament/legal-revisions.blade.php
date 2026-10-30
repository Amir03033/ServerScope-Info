<div class="space-y-3 text-sm">
    @foreach ($revisions as $revision)
        @php $lines = \App\Support\TextDiff::compact($revision->body, $current); @endphp
        <details class="rounded-lg border border-gray-200 p-3 dark:border-white/10" @if ($loop->first) open @endif>
            <summary class="cursor-pointer font-medium">
                {{ $revision->created_at?->format('d-m-Y H:i') }} · {{ $revision->user?->name ?? 'Systeem' }} · {{ $revision->reason }}
                @if (($revision->meta['legal_reviewed'] ?? false)) · gecontroleerd @endif
            </summary>
            @if ($lines === [])
                <p class="mt-2 text-gray-500">Gelijk aan de huidige tekst.</p>
            @else
                <pre class="mt-2 max-h-80 overflow-auto font-mono text-xs leading-5">@foreach ($lines as [$type, $line])<span style="display:block;padding:0 .5rem;{{ $type === '+' ? 'background:rgba(22,163,74,.12)' : ($type === '-' ? 'background:rgba(220,38,38,.12)' : '') }}">{{ $type === '=' ? ' ' : $type }} {{ $line }}</span>@endforeach</pre>
                <p class="mt-1 text-xs text-gray-500">− = in deze revisie, + = in de huidige tekst.</p>
            @endif
        </details>
    @endforeach
</div>
