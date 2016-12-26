<?php

$app->group('/api', function () use ($app, $loggedIn, $admin, $guest, $cache) {

    $app->group('/auth', function () use ($app, $loggedIn, $admin, $guest, $cache) {
        require_once 'no-include/auth/register.php';
        require_once 'no-include/auth/login.php';
        require_once 'no-include/auth/logout.php';
    });

    $app->group('/misc', function () use ($app, $loggedIn, $admin, $guest, $cache) {
        require_once 'no-include/misc/pause.php';
    });

});
