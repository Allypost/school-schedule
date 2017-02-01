<?php

$app->get('/', function () use ($app) {
    $u = $app->auth;

    if (!$u) {
        $app->render('home/home.twig');

        return;
    }

    $app->render('home/home.twig');
})->name('home:home');
