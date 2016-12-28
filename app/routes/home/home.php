<?php

$app->get('/', function () use ($app) {
    $u = $app->auth;

    if (!$u) {
        $app->render('home/home.twig');

        return;
    }

    $week = ((int) (date('W'))) % 2 == 0 ? 'a' : 'b';
    $days = [ 'mon', 'tue', 'wed', 'thu', 'fri' ];

    $app->render('home/home.twig', compact('week', 'days'));
});
