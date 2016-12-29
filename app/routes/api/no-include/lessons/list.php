<?php

$app->group('/list', function () use ($app, $loggedIn, $admin, $guest, $cache) {

    $app->get('/', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $teaching  = $u->lessons()->get()->toArray();
        $attending = $u->attending()->toArray();

        say('lessons all', compact('teaching', 'attending'));
    })->name('api:lessons:all');

    $app->get('/attending', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $data = $u->attending()->toArray();

        say('lessons attending', $data);
    })->name('api:lessons:attending');

    $app->get('/teaching', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $data = $u->lessons()->get()->toArray();

        say('lessons teaching', $data);
    })->name('api:lessons:teaching');

    $app->get('/schedule', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $data = $u->schedule()->get()->toArray();

        say('lessons schedule', $data);
    })->name('api:lessons:schedule');

});
