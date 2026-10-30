{{-- Labelled text input or textarea (rows => textarea) with old() value and accessible error text. --}}
@props(['name', 'label', 'type' => 'text', 'rows' => null, 'autocomplete' => null, 'value' => null])
@php
    $hasError = $errors->has($name);
    $classes = 'ss-input mt-2 block' . ($hasError ? ' !border-bad' : '');
@endphp
<div>
    <label for="{{ $name }}" class="text-sm font-medium text-fg">{{ $label }}</label>
    @if ($rows)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" required
                  @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
                  class="{{ $classes }}">{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" required
               autocomplete="{{ $autocomplete ?? $name }}"
               @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
               class="{{ $classes }}">
    @endif
    @error($name)<p id="{{ $name }}-error" class="mt-1.5 text-sm text-bad">{{ $message }}</p>@enderror
</div>
