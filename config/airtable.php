<?php

return [

    'key' => env('AIRTABLE_KEY'),
    'base' => env('AIRTABLE_BASE'),

    'default' => 'default',

    'tables' => [
        'default' => [
            'name' => env('AIRTABLE_TABLE'),
        ],
        'company-profiles' => [
            'name' => 'All Received Profiles',
            'base' => 'appz13BqzoJvDXvvf',
        ],
    ],

    'typecast' => env('AIRTABLE_TYPECAST', true),

    'delay_between_requests' => env('AIRTABLE_DELAY_BETWEEN_REQUESTS', 200000),
];
