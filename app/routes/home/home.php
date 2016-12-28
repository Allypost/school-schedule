<?php

$app->get('/', function () use ($app) {
    $u = $app->auth;

    if (!$u) {
        $app->render('home/home.twig');

        return;
    }

    $classes = $u->schedule();
    $week    = ((int) (date('W'))) % 2 == 0 ? 'a' : 'b';
    $days    = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri' ];

    $app->render('home/home.twig', compact('classes', 'week', 'days'));
});
