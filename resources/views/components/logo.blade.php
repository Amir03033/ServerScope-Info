{{-- ServerScope mark + wordmark. --}}
@props(['size' => 'md'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 text-white']) }}>
    <span class="relative flex {{ $size === 'sm' ? 'h-7 w-7' : 'h-8 w-8' }} items-center justify-center rounded-lg border border-sky-400/30 bg-gradient-to-b from-sky-400/20 to-sky-400/5 text-sky-300">
        <x-ss-icon name="pulse" class="{{ $size === 'sm' ? 'h-4 w-4' : 'h-[18px] w-[18px]' }}" />
    </span>
    <span class="{{ $size === 'sm' ? 'text-[15px]' : 'text-base' }} font-semibold tracking-tight">ServerScope</span>
</span>
