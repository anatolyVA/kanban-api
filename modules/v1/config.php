<?php

return [
    'components' => [
        'jwt' => [
            'class' => \kaabar\jwt\Jwt::class,
            'key' => 'hDyWWaYg!lh9J$D3qZW87EwMa.rZsCdE',  //typically a long random string
        ],
    ],
    'params' => [
        'jwt' => [
            'issuer' => 'https://api.example.com',  //name of your project (for information only)
            'audience' => 'https://example.com',  //description of the audience, eg. the website using the authentication (for info only)
            'id' => 'AMqey0yAVrqmhR82RMlWB3zqMpvRP0zaaOheEeq2tmmcEtRYNj',  //a unique identifier for the JWT, typically a random string
            'expire' => '+1 hour',  //the short-lived JWT token is here set to expire after 1 Hours.
            'request_time' => '+1 seconds', //the time between the two requests. (optional)
        ],
    ]
];
