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

];
