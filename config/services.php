<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => '',
        'secret' => '',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key'    => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => '',
        'secret' => '',
    ],

    'Google' => [
            'client_id'     => env('742314275451-6fpu8924872ni2q1ps32076lerpv0ulf.apps.googleusercontent.com'),
            'client_secret' => env('GuoG0AauEzTxLGXvFMCrIZW4'),
            //'scope' => ['profile', 'https://www.google.com/m8/feeds/'],
        ]
];
