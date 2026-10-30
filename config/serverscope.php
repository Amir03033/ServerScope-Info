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
    | Where the contact form is delivered. Without an address the contact
    | page says the form is unavailable instead of silently dropping messages.
    */

    'contact_mail_to' => env('CONTACT_MAIL_TO'),

    /*
    |--------------------------------------------------------------------------
    | Staging
    |--------------------------------------------------------------------------
    |
    | STAGING=true puts the whole site behind HTTP Basic Auth and marks every
    | page noindex. In production this shows a red banner on every page and
    | in the admin panel and writes a critical log entry, so it cannot stay
    | on unnoticed. Outside production pages are always noindex.
    |
    */

    'staging' => [
        'enabled' => (bool) env('STAGING', false),
        'user' => env('STAGING_USER'),
        'password' => env('STAGING_PASSWORD'),
    ],

    'noindex' => (bool) env('STAGING', false) || env('APP_ENV', 'production') !== 'production',

    /*
    | Share images per page, drawn with GD (storage/app/og). Without GD, or
    | with OG_IMAGES=false, every page uses public/images/og-default.png.
    */
    'og_images' => (bool) env('OG_IMAGES', true),

];
