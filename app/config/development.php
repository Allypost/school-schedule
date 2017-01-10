<?php

return [
    'app'    => [
        'url'  => 'https://ibrahim-is-useful.ally-net.xyz',
        'hash' => [
            'algo'       => PASSWORD_BCRYPT,
            'cost'       => 8,
            'secret_key' => '92d0ee5d0e7cc1703fe937772c313721fdb86cdce8e6575abf6102af53ec283f7f14b89e304fa602',
            'secret_iv'  => '',
        ],
    ],
    'db'     => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'ibrahim-is-useful',
        'username'  => 'ibrahim-prj1',
        'password'  => 'Hnbtd5nuctIwgroiFvaNonTQoGvGlLrcVUi2mQWww7v1dJPqjDBhT1Ji6hdoWqC3H0YyLDbEuoEMfL1q9Zxhj6ME34BS3u4X9oMR',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ],
    'auth'   => [
        'session'      => 'session',
        'remember'     => 'remember_me',
        'remember_for' => '1 month',
        'domain'       => '.ibrahim-is-useful.ally-net.xyz',
        'login'        => [
            'attemptsMax'    => 10,
            'attemptsExpire' => '5 minutes',
        ],
    ],
    'twig'   => [
        'debug' => TRUE,
    ],
    'csrf'   => [
        'key' => 'csrf_token',
    ],
    'google' => [
        'recaptcha' => [
            'site_key'   => '6LdIYBEUAAAAAC1Wda7C2xKQsw_ZCxcuA9quB9sR',
            'secret_key' => '6LdIYBEUAAAAAJEE2YBRTtS0jA4B1s2SlOc5O-mg',
        ],
    ],
];
