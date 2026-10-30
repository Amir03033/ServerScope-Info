<div class="space-y-4 text-sm">
    @foreach (['Titel' => $title, 'Omschrijving' => $description] as $label => [$old, $new])
        @if ($old !== $new)
            <div>
                <p class="font-semibold">{{ $label }}</p>
                <p style="background:rgba(220,38,38,.12)" class="mt-1 rounded px-2 py-1"><span aria-hidden="true">− </span><span class="sr-only">Was: </span>{{ $old ?? '—' }}</p>
                <p style="background:rgba(22,163,74,.12)" class="mt-1 rounded px-2 py-1"><span aria-hidden="true">+ </span><span class="sr-only">Nu: </span>{{ $new ?? '—' }}</p>
            </div>
        @endif
    @endforeach

    <div>
        <p class="font-semibold">Tekst</p>
        @if ($lines === [])
            <p class="mt-1 text-gray-500">Geen verschil met de huidige tekst.</p>
        @else
            <pre class="mt-1 max-h-[60vh] overflow-auto rounded-lg border border-gray-200 p-0 font-mono text-xs leading-5 dark:border-white/10">@foreach ($lines as [$type, $line])<span style="display:block;padding:0 .75rem;{{ $type === '+' ? 'background:rgba(22,163,74,.12)' : ($type === '-' ? 'background:rgba(220,38,38,.12)' : '') }}">{{ $type === '=' ? ' ' : $type }} {{ $line }}</span>@endforeach</pre>
            <p class="mt-1 text-xs text-gray-500">− = in deze revisie, + = in de huidige versie.</p>
        @endif
    </div>
</div>
