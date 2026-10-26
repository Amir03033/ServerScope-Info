<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ServerScope application
    |--------------------------------------------------------------------------
    |
    | The monitoring application is a separate Laravel app with its own
    | accounts. This site only links to it (log in, get started, deep links
    | from the documentation); it never shares sessions or user data.
    |
    */

    'app_url' => rtrim((string) env('SERVERSCOPE_APP_URL', 'https://serverscope.nl'), '/'),

    /*
    |--------------------------------------------------------------------------
    | Service status
    |--------------------------------------------------------------------------
    |
    | Slug of a *public* status page in the ServerScope application that
    | describes ServerScope itself. When set, /{locale}/status shows the live
    | data from {app_url}/status/{slug}.json (cached briefly). When empty,
    | the page says that no live status is connected; it never shows
    | invented status data.
    |
    */

    'status_page' => env('SERVERSCOPE_STATUS_PAGE') ?: null,

    'status_cache_seconds' => (int) env('SERVERSCOPE_STATUS_CACHE', 60),

    /*
    |--------------------------------------------------------------------------
    | Contact form
    |--------------------------------------------------------------------------
    |
    | Messages from the contact form are e-mailed to this address with the
    | configured mailer (MAIL_*). Without a recipient the form is not shown,
    | so visitors are never told a message was sent when it was not.
    |
    */

    'contact_recipient' => env('CONTACT_RECIPIENT') ?: null,

];
