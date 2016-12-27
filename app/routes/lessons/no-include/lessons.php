<?php

$app->get('/', function () use ($app) {
    try {
        $u         = $app->auth;
        $attending = $u->attending();
        $teaching  = $u->lessons()->get();

        dd(compact('attending', 'teaching'));
    } catch (\Throwable $e) {
        dd($e);
    }
})->name('lessons:home');
