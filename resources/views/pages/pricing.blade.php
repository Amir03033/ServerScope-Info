@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.pricing.' . $key, [], $lang);
    $c = fn (string $key) => trans('site.common.' . $key, [], $lang);
    $site = app(\App\Services\Site\SiteData::class);
    $pricing = $site->pricing();
    $plans = $pricing['plans'];
    $rows = $pricing['rows'];
    $values = $pricing['values'];
    $registrationOpen = (bool) \App\Models\Setting::get('registration_open');
    $toggle = (bool) \App\Models\Setting::get('billing_toggle') && $plans->contains(fn ($p) => $p->price_yearly !== null);
    $yearlyNote = (array) \App\Models\Setting::get('yearly_note');
    $money = function ($amount, string $currency) use ($lang): string {
        $formatted = number_format((float) $amount, fmod((float) $amount, 1.0) === 0.0 ? 0 : 2, $lang === 'en' ? '.' : ',', $lang === 'en' ? ',' : '.');

        return ($currency === 'USD' ? '$' : '€') . ' ' . $formatted;
    };
    $cell = function ($row, $value) use ($t) {
        if ($value === null || $value === '') {
            return ['text' => $t('follows'), 'muted' => true];
        }
        if ($row->type === 'boolean') {
            return ['text' => $value === '1' ? $t('yes') : $t('no'), 'bool' => $value === '1'];
        }

        return ['text' => $value];
    };
@endphp

@section('title', $t('title'))
@section('description', $t('intro'))

@section('content')
    <section class="relative border-b border-line">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-container relative pb-14 pt-16 text-center sm:pt-24">
            <div class="mx-auto max-w-2xl">
                <x-page-header :title="$t('headline')" :intro="$t('intro')" intro-class="mx-auto" />
            </div>

            @if ($toggle)
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <div class="ss-segmented" role="group" aria-label="{{ $t('billing') }}" data-billing-toggle>
                        <button type="button" aria-pressed="true" data-billing="monthly">{{ $t('monthly') }}</button>
                        <button type="button" aria-pressed="false" data-billing="yearly">{{ $t('yearly') }}</button>
                    </div>
                    @if (filled($yearlyNote[$lang] ?? null))
                        <span class="ss-badge ss-badge-soon">{{ $yearlyNote[$lang] }}</span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    @if ($plans->isEmpty())
        <section class="ss-container mt-14">
            <x-empty-state icon="list" :title="$t('headline')">{{ $t('price_follows') }}</x-empty-state>
        </section>
    @else
        <section class="ss-container mt-14">
            @unless ($registrationOpen)
                <p class="mb-6 flex items-center justify-center gap-2 text-center text-sm text-muted"><x-ss-icon name="clock" class="h-4 w-4" /> {{ $t('waitlist_note') }}</p>
            @endunless

            <div class="grid gap-5 {{ match (min(3, $plans->count())) { 1 => 'mx-auto max-w-md', 2 => 'lg:grid-cols-2', default => 'lg:grid-cols-3' } }}" data-reveal-group>
                @foreach ($plans as $plan)
                    <article data-reveal class="ss-card ss-card-hover flex flex-col p-7 {{ $plan->is_recommended ? 'ss-plan-recommended' : '' }}">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-fg">{{ $plan->translate('name', $lang) }}</h2>
                            @if ($plan->is_recommended)
                                <span class="ss-badge ss-badge-soon">{{ $t('recommended') }}</span>
                            @endif
                        </div>
                        @if (filled($plan->translate('tagline', $lang)))
                            <p class="mt-2 text-sm text-muted">{{ $plan->translate('tagline', $lang) }}</p>
                        @endif

                        <div class="mt-6 min-h-[4.5rem]">
                            @if ($plan->price_monthly === null && $plan->price_yearly === null)
                                <p class="text-2xl font-semibold text-fg">{{ $t('price_follows') }}</p>
                            @else
                                <p data-price="monthly" class="{{ $plan->price_monthly === null ? 'text-2xl font-semibold text-fg' : '' }}">
                                    @if ($plan->price_monthly !== null)
                                        <span class="text-4xl font-semibold tracking-tight text-fg tabular-nums">{{ $money($plan->price_monthly, $plan->currency) }}</span>
                                        <span class="text-sm text-muted">{{ $t('per_month') }}</span>
                                    @else
                                        {{ $t('price_follows') }}
                                    @endif
                                </p>
                                @if ($toggle)
                                    <p data-price="yearly" hidden class="{{ $plan->price_yearly === null ? 'text-2xl font-semibold text-fg' : '' }}">
                                        @if ($plan->price_yearly !== null)
                                            <span class="text-4xl font-semibold tracking-tight text-fg tabular-nums">{{ $money($plan->price_yearly, $plan->currency) }}</span>
                                            <span class="text-sm text-muted">{{ $t('per_year') }}</span>
                                        @else
                                            {{ $t('price_follows') }}
                                        @endif
                                    </p>
                                @endif
                            @endif
                            @if (filled($plan->translate('price_note', $lang)))
                                <p class="mt-1 text-xs text-subtle">{{ $plan->translate('price_note', $lang) }}</p>
                            @endif
                        </div>

                        <x-button :href="$site->planUrl($plan, $lang)" :variant="$plan->is_recommended ? 'primary' : 'secondary'" class="mt-6 w-full">
                            {{ $plan->translate('cta_label', $lang) }}
                        </x-button>

                        @php $limits = $rows->flatten()->where('type', '!=', 'boolean')->take(5); @endphp
                        @if ($limits->isNotEmpty())
                            <ul class="mt-7 space-y-2.5 border-t border-line pt-6 text-sm">
                                @foreach ($limits as $row)
                                    @php $v = $cell($row, $values[$plan->id][$row->id] ?? null); @endphp
                                    <li class="flex justify-between gap-4">
                                        <span class="text-muted">{{ $row->translate('label', $lang) }}</span>
                                        <span class="text-right font-medium {{ ($v['muted'] ?? false) ? 'text-subtle' : 'text-fg' }}">{{ $v['text'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        @if ($rows->isNotEmpty())
            <section class="ss-container mt-24" aria-labelledby="compare-title">
                <h2 id="compare-title" data-reveal class="text-2xl font-semibold tracking-[-0.02em] text-fg sm:text-3xl">{{ $t('compare') }}</h2>
                <div class="mt-8 overflow-x-auto rounded-2xl border border-line" tabindex="0" role="region" aria-labelledby="compare-title">
                    <table class="ss-compare w-full min-w-[36rem] border-collapse text-sm">
                        <thead class="sticky top-0 bg-surface">
                            <tr>
                                <th scope="col" class="w-2/5 border-b border-line px-5 py-4 text-left font-semibold text-muted">{{ $t('feature') }}</th>
                                @foreach ($plans as $plan)
                                    <th scope="col" class="border-b border-line px-5 py-4 text-left font-semibold text-fg {{ $plan->is_recommended ? 'bg-accent-soft' : '' }}">{{ $plan->translate('name', $lang) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        @foreach ($rows as $groupRows)
                            <tbody>
                                <tr><th scope="colgroup" colspan="{{ $plans->count() + 1 }}" class="bg-surface-2 px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-[.08em] text-subtle">{{ $groupRows->first()->translate('group', $lang) }}</th></tr>
                                @foreach ($groupRows as $row)
                                    <tr class="transition-colors hover:bg-surface-2">
                                        <th scope="row" class="border-t border-line px-5 py-3 text-left text-fg">
                                            {{ $row->translate('label', $lang) }}
                                            @if ($row->feature?->isComingSoon())
                                                <span class="ss-badge ss-badge-soon ml-2 !py-0 !text-[11px]">{{ $c('coming_soon') }}</span>
                                            @endif
                                            @if (filled($row->translate('hint', $lang)))
                                                <span class="mt-0.5 block text-xs font-normal text-subtle">{{ $row->translate('hint', $lang) }}</span>
                                            @endif
                                        </th>
                                        @foreach ($plans as $plan)
                                            @php $v = $cell($row, $values[$plan->id][$row->id] ?? null); @endphp
                                            <td class="border-t border-line px-5 py-3 {{ $plan->is_recommended ? 'bg-accent-soft/50' : '' }}">
                                                @if (array_key_exists('bool', $v))
                                                    <span class="inline-flex items-center gap-1.5 {{ $v['bool'] ? 'text-fg' : 'text-subtle' }}">
                                                        <x-ss-icon :name="$v['bool'] ? 'check' : 'x'" class="h-4 w-4 {{ $v['bool'] ? 'text-accent' : '' }}" />
                                                        <span class="sr-only">{{ $v['text'] }}</span>
                                                    </span>
                                                @else
                                                    <span class="{{ ($v['muted'] ?? false) ? 'text-subtle' : 'text-fg' }}">{{ $v['text'] }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </section>
        @endif
    @endif
@endsection
