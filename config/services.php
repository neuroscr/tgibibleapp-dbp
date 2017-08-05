<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'github' => [
	    'client_id'     => env('GITHUB_CLIENT_ID'),
	    'client_secret' => env('GITHUB_CLIENT_SECRET'),
	    'redirect'      => env('APP_URL').'/login/github/callback',
    ],

];
