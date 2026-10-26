{{-- Plain-text e-mail; Blade escaping is not applied to text mail, so nothing here is rendered as HTML. --}}
Nieuw bericht via het contactformulier van {{ config('app.url') }} ({{ strtoupper($siteLocale) }}).

Naam:      {!! $data['name'] !!}
E-mail:    {!! $data['email'] !!}
Onderwerp: {!! $data['subject'] !!}

{!! $data['message'] !!}
