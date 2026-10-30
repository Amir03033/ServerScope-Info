{{-- Visible breadcrumb plus the matching BreadcrumbList (same source). Items: [name, url|null]; the last is the current page. --}}
@props(['items', 'class' => 'mb-8'])
@php \App\Support\StructuredData::push(\App\Support\StructuredData::breadcrumbs($items)); @endphp
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => $class . ' text-sm text-muted']) }}>
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $i => [$name, $url])
            @if (! $loop->first)
                <li aria-hidden="true" class="text-subtle">/</li>
            @endif
            @if ($loop->last)
                <li aria-current="page" class="text-fg">{{ $name }}</li>
            @elseif ($url)
                <li><a href="{{ $url }}" class="hover:text-fg">{{ $name }}</a></li>
            @else
                <li class="text-muted">{{ $name }}</li>
            @endif
        @endforeach
    </ol>
</nav>
