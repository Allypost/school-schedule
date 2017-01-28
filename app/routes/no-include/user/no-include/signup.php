<?php

use Allypost\User\User;

$app->get('/signup/:user/(:code)', function ($user, $code = NULL) {
    $u = new User();

    if (!$code)
        dd('Enter code');

    $data = $u->activateCheck($user, $code, TRUE);

    if (!$data)
        err('user signup', [ 'Invalid code' ]);

    dd($code, $data->toArray());
})->name('user:signup');
