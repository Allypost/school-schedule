<?php

use Allypost\User\User;

$app->post('/signup', $guest(), function () use ($app) {
    $u = new User();
    $r = $app->request;

    $uuid    = $r->post('user');
    $code    = $r->post('code');
    $pass    = $r->post('password');
    $passRep = $r->post('repeat-password');

    $validCode = $u->activateCheck($uuid, $code, TRUE);

    if (!$validCode)
        err('user signup', [ 'Invitation is not valid' ]);

    if (empty($pass) || empty($passRep))
        err('user signup', [ 'Password must be filled in' ]);

    $user = $validCode;

    $validPass = $u->passwordCheck($pass, $passRep, $user);

    if (!$validPass[ 'match' ])
        err('user signup', [ 'The passwords must match' ]);

    if (!$validPass[ 'length' ])
        err('user signup', [ 'The password must be 6 or more characters long' ]);

    if (!$validPass[ 'name' ])
        err('user signup', [ 'The password is too similar to your name' ]);

    if (!$validPass[ 'id' ])
        err('user signup', [ 'The password is too similar to your User ID' ]);

    if (!$validPass[ 'email' ])
        err('user signup', [ 'The password is too similar to your email' ]);

    $success = $user->activate($user, $pass);

    if (!$success)
        err('user signup', [ 'Something went wrong' ]);

    say('user signup', $user->toArray(), 'goto:/');
})->name('api:user:signup');
