<?php

use Allypost\User\User;

$app->get('/signup/:user/:code', $guest(), function ($user, $code = NULL) use ($app) {
    $u    = new User();
    $data = $u->activateCheck($user, $code, TRUE);

    if (!$data)
        err('user signup', [ 'Invalid code' ]);

    $app->render('user/signup.twig', compact('data', 'code'));
})->name('user:signup');
