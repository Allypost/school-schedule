<?php

$app->get('/logout', $loggedIn(), function () use ($app) {
    $u = $app->user;

    $u->logout('/');
})->name('auth:logout');
