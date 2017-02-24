<?php

$app->get('/login', $guest(), function () use ($app) {
    $app->render('auth/login.twig');
})->name('auth:login');

$app->post('/login', $guest(), function () use ($app) {
    if ($app->siteSettings[ 'login_enabled' ] != 'on')
        $app->o->err('authentication login', [ 'Logins have been disabled' ]);

    $r = $app->request;
    $u = $app->user;

    $username = $r->post('identifier') ?? '';
    $password = $r->post('password') ?? '';

    if (!$u->loginValidateData($username, $password))
        $app->o->err('authentication credentials missing', [ 'Username and password must be filled in!' ]);

    $login = $u->login($username, $password, TRUE);

    if (!$login[ 'passed' ])
        $app->o->err('authentication login', [ 'errors' => $login[ 'reasons' ], 'remaining' => $login[ 'loginsRemaining' ] ?? 0 ]);

    $redirect = $r->post('redirect_to');

    if ($redirect)
        $app->response->redirect($redirect);

    $app->flash('global', "You're logged in now!");

    $app->o->say('authentication login', [ 'You have been logged in successfully' ]);
})->name('auth:login.post');
