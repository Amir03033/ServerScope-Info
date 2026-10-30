{{--
    Terminal that "types" a real command. The full text is in the HTML
    (readable and copyable without JavaScript); app.js only animates it
    when motion is allowed.
--}}
@props(['command', 'title' => 'bash', 'copyLabel' => 'Copy', 'copiedLabel' => 'Copied', 'label' => null])
<figure {{ $attributes->merge(['class' => 'ss-terminal']) }} data-terminal>
    <figcaption class="ss-terminal-bar">
        <span class="flex gap-1.5" aria-hidden="true">
            <span class="h-2.5 w-2.5 rounded-full bg-[#3f3f46]"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-[#3f3f46]"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-[#3f3f46]"></span>
        </span>
        <span class="ml-1 truncate">{{ $title }}</span>
        <button type="button" class="ml-auto inline-flex items-center gap-1.5 rounded-md border border-[#2e2e33] px-2 py-1 text-[11px] font-semibold text-[#d4d4d8] transition-colors hover:border-[#52525b] hover:text-white"
                data-copy-text="{{ $command }}" data-copy-label="{{ $copyLabel }}" data-copied-label="{{ $copiedLabel }}">
            <x-ss-icon name="copy" class="h-3.5 w-3.5" /><span>{{ $copyLabel }}</span>
        </button>
    </figcaption>
    <pre @if ($label) aria-label="{{ $label }}" @endif><span class="ss-prompt">$ </span><code data-typing>{{ $command }}</code><span class="ss-caret" aria-hidden="true"></span>{{ $slot }}</pre>
</figure>
