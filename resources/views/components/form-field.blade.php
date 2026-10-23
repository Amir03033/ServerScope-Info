{{-- Labelled text input or textarea (rows => textarea) with old() value and accessible error text. --}}
@props(['name', 'label', 'type' => 'text', 'rows' => null, 'autocomplete' => null])
@php
    $hasError = $errors->has($name);
    $classes = 'mt-2 block w-full rounded-xl border bg-[#0e1726] px-4 py-3 text-sm text-white placeholder-slate-500 transition focus:outline-none focus:ring-2 focus:ring-sky-400/60 '
        . ($hasError ? 'border-rose-400/60' : 'border-white/10');
@endphp
<div>
    <label for="{{ $name }}" class="text-sm font-medium text-slate-200">{{ $label }}</label>
    @if ($rows)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" required
                  @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
                  class="{{ $classes }}">{{ old($name) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}" required
               autocomplete="{{ $autocomplete ?? $name }}"
               @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
               class="{{ $classes }}">
    @endif
    @error($name)<p id="{{ $name }}-error" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>@enderror
</div>
