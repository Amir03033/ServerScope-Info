@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $t = $isEn ? [
        'title' => 'Contact — ServerScope',
        'headline' => 'Get in touch',
        'intro' => 'Have a question or a suggestion? Send a message and we will get back to you.',
        'name' => 'Name', 'email' => 'Email address', 'message' => 'Message',
        'send' => 'Send message',
        'success' => 'Message sent. Thank you, we will get back to you soon.',
        'errors' => 'Please check the highlighted fields.',
        'aside_title' => 'Before you write',
        'aside' => 'For how ServerScope works, the documentation is usually the quickest answer.',
        'docs' => 'Open the documentation',
    ] : [
        'title' => 'Contact — ServerScope',
        'headline' => 'Neem contact op',
        'intro' => 'Een vraag of suggestie? Stuur een bericht en je krijgt snel antwoord.',
        'name' => 'Naam', 'email' => 'E-mailadres', 'message' => 'Bericht',
        'send' => 'Bericht versturen',
        'success' => 'Bericht verzonden. Bedankt, we reageren zo snel mogelijk.',
        'errors' => 'Controleer de gemarkeerde velden.',
        'aside_title' => 'Voordat je schrijft',
        'aside' => 'Voor hoe ServerScope werkt is de documentatie meestal het snelste antwoord.',
        'docs' => 'Open de documentatie',
    ];
    $field = 'mt-2 block w-full rounded-xl border bg-[#0e1726] px-4 py-3 text-sm text-white placeholder-slate-500 transition focus:outline-none focus:ring-2 focus:ring-sky-400/60';
@endphp

@section('title', $t['title'])

@section('content')
    <section class="mx-auto grid max-w-6xl gap-12 px-4 pb-4 pt-20 sm:px-6 lg:grid-cols-[1.3fr_1fr]">
        <div>
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $t['headline'] }}</h1>
            <p class="mt-4 max-w-lg text-lg text-slate-400">{{ $t['intro'] }}</p>

            @if (session('success'))
                <div role="status" class="mt-8 flex items-start gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">
                    <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" /> {{ session('success') ?: $t['success'] }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mt-8 rounded-xl border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-200">{{ $t['errors'] }}</div>
            @endif

            <form method="POST" action="{{ url($lang . '/contact') }}" class="mt-8 space-y-5" novalidate>
                @csrf

                @foreach ([['name', 'text', 'name'], ['email', 'email', 'email']] as [$id, $type, $key])
                    <div>
                        <label for="{{ $id }}" class="text-sm font-medium text-slate-200">{{ $t[$key] }}</label>
                        <input id="{{ $id }}" name="{{ $id }}" type="{{ $type }}" value="{{ old($id) }}" required
                               autocomplete="{{ $id }}"
                               @error($id) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
                               class="{{ $field }} {{ $errors->has($id) ? 'border-rose-400/60' : 'border-white/10' }}">
                        @error($id)<p id="{{ $id }}-error" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                @endforeach

                <div>
                    <label for="message" class="text-sm font-medium text-slate-200">{{ $t['message'] }}</label>
                    <textarea id="message" name="message" rows="6" required
                              @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                              class="{{ $field }} {{ $errors->has('message') ? 'border-rose-400/60' : 'border-white/10' }}">{{ old('message') }}</textarea>
                    @error('message')<p id="message-error" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <x-button type="submit">{{ $t['send'] }}</x-button>
            </form>
        </div>

        <aside class="h-fit rounded-2xl border border-white/10 bg-slate-900/50 p-6">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400"><x-icon name="book" /></span>
            <h2 class="mt-5 text-lg font-semibold text-white">{{ $t['aside_title'] }}</h2>
            <p class="mt-2 leading-relaxed text-slate-400">{{ $t['aside'] }}</p>
            <x-button :href="url($lang . '/docs')" variant="secondary" class="mt-6">{{ $t['docs'] }}</x-button>
        </aside>
    </section>
@endsection
