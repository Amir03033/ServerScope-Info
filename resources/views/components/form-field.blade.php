{{-- Labelled text input or textarea (rows => textarea) with old() value, hint and accessible error text. --}}
@props(['name', 'label', 'type' => 'text', 'rows' => null, 'autocomplete' => null, 'value' => null, 'hint' => null, 'maxlength' => null])
@php
    $hasError = $errors->has($name);
    $describedBy = trim(($hint ? "{$name}-hint " : '') . ($hasError ? "{$name}-error" : ''));
    $current = old($name, $value);
@endphp
<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-200">{{ $label }}</label>
    @if ($hint)<p id="{{ $name }}-hint" class="mt-1 text-[13px] text-slate-500">{{ $hint }}</p>@endif
    @if ($rows)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" required
                  @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                  @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                  @if ($hasError) aria-invalid="true" @endif
                  class="ss-input mt-2 min-h-[9rem] resize-y leading-relaxed">{{ $current }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" required
               autocomplete="{{ $autocomplete ?? $name }}"
               @if ($maxlength) maxlength="{{ $maxlength }}" @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               @if ($hasError) aria-invalid="true" @endif
               class="ss-input mt-2">
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-2 flex items-start gap-1.5 text-sm text-rose-300">
            <x-ss-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" /> <span>{{ $message }}</span>
        </p>
    @enderror
</div>
