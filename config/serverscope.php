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
    | The company behind ServerScope, printed in the terms and the privacy
    | statement (content/legal). Use the same values as SERVERSCOPE_COMPANY_*
    | in the application. While name, KvK number, address or e-mail is
    | missing, the legal pages show a draft warning and are not indexed.
    */

    'company' => [
        'name' => env('SERVERSCOPE_COMPANY_NAME'),
        'kvk' => env('SERVERSCOPE_COMPANY_KVK'),
        'vat_id' => env('SERVERSCOPE_COMPANY_VAT_ID'),
        'address' => env('SERVERSCOPE_COMPANY_ADDRESS'),
        'email' => env('SERVERSCOPE_COMPANY_EMAIL'),
        'hosting_provider' => env('SERVERSCOPE_HOSTING_PROVIDER'),
    ],

    // Date of the current version of the terms and privacy statement (YYYY-MM-DD).
    'legal_updated' => env('SERVERSCOPE_LEGAL_UPDATED', '2026-11-01'),

    /*
    | Timezone used to show times coming from the ServerScope status page
    | (the JSON carries UTC). Keep identical to ServerScope's
    | SERVERSCOPE_DISPLAY_TIMEZONE so both status pages show the same time.
    */

    'display_timezone' => env('SERVERSCOPE_DISPLAY_TIMEZONE', 'Europe/Amsterdam'),

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

];
