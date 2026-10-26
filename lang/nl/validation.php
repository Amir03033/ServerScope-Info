<?php

// Dutch messages for the validation rules used on the public site (contact
// form). Rules without a Dutch message fall back to Laravel's English text.

return [
    'required' => 'Vul :attribute in.',
    'string' => ':Attribute moet tekst zijn.',
    'email' => 'Vul een geldig e-mailadres in.',
    'max' => [
        'string' => ':Attribute mag maximaal :max tekens bevatten.',
    ],
    'min' => [
        'string' => ':Attribute moet minimaal :min tekens bevatten.',
    ],
];
