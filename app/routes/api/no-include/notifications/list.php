<?php

use Allypost\Lessons\Notification;

$app->get('/count', function () use ($app) {
    $n = new Notification();

    $count = $n->mine()->count();

    say('notifications list count', compact('count'));
})->name('api:notifications:count');

$app->get('/', function () use ($app) {
    $n = new Notification();

    $data = $n->mine()->get()->toArray();

    say('notifications list', $data);
})->name('api:notifications:list');
