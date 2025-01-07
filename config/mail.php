<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),

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
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'mileleskillsage@gmail.com',
            'password' => 'nrnccamdshesmulk', 
            'auth_mode' => null,
            'timeout' => null,
            'local_domain' => null,
            'verify_peer' => false,
        ],
        
        'gmail' => [
            'transport' => 'gmail',
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
        'address' => 'mileleskillsage@gmail.com',
        'name' => 'Milele SkillSage',
    ],

];