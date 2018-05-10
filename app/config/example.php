<?php

return [
    'app' => [
        'url' => 'https://example.com',
        'hash' => [
            'algo' => PASSWORD_BCRYPT,
            'cost' => 8,
            'secret_key' => 'THIS IS THE SECRET KEY',
        ],
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'DB NAME',
        'username' => 'DB USERNAME',
        'password' => 'DB PASSWORD',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ],
    'auth' => [
        'session' => 'SESSION NAME',
        'remember' => 'REMEMBER COOKIE NAME',
        'remember_for' => '1 month',
        'domain' => 'AUTH COOKIES DOMAIN',
        'login' => [
            'attemptsMax' => 10,
            'attemptsExpire' => '5 minutes',
        ],
    ],
    'twig' => [
        'debug' => false,
    ],
    'csrf' => [
        'key' => 'CSRF KEY NAME',
    ],
    'google' => [
        'recaptcha' => [
            'site_key' => '',
            'secret_key' => '',
        ],
    ],
];
