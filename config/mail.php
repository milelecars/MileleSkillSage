<?php

return [
    'default' => env('MAIL_MAILER', 'log'),

   /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [
        // 'smtp' => [
        //     'transport' => 'smtp',
        //     'scheme' => env('MAIL_SCHEME', 'smtp'),
        //     'url' => env('MAIL_URL'),
        //     'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        //     'port' => env('MAIL_PORT', 465),
        //     'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        //     'username' => env('MAIL_USERNAME', 'skillsage.milele@gmail.com'),
        //     'password' => env('MAIL_PASSWORD', 'rbpamzmdbrantplm'),
        //     'timeout' => null,
        //     'local_domain' => env('MAIL_EHLO_DOMAIN'),
        //     'verify_peer' => false,
        //     'verify_peer_name' => false,
        // ],

        'smtp' => [
            'transport' => 'smtp',
            'url' => null,
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'skillsage.milele@gmail.com',
            'password' => "rbpamzmdbrantplm",
            'timeout' => null,
            'local_domain' => null,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'skillsage.milele@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Milele SkillSage'),
    ],

];