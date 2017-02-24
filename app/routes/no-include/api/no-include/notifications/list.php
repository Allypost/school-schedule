<?php

use Allypost\Lessons\Notification;

$app->get('/count', function () use ($app) {
    $n = new Notification();

    $count = $n->mine()->count();

    $app->o->say('notifications list count', compact('count'));
})->name('api:notifications:count');

$app->get('/', function () use ($app) {
    $n = new Notification();

    $data = $n->mine()->get()->toArray();

    $app->o->say('notifications list', $data);
})->name('api:notifications:list');

$app->get('/all', function () use ($app) {
    $n = new Notification();

    $data = $n->mine(TRUE)->get()->toArray();

    $old = array_filter($data, function ($el) {
        return $el[ 'seen' ];
    });

    $new = array_diff_key($data, $old);
    $old = array_values($old);

    $list = [
        'new' => $new,
        'old' => $old,
    ];

    $app->o->say('notifications list', $list);
})->name('api:notifications:list:all');

$app->get('/old', function () use ($app) {
    $n = new Notification();

    $data = $n->mine(TRUE)->where('notifications.created_at', '<=', $app->auth->data->notification_seen)->get()->toArray();

    $app->o->say('notifications list', $data);
})->name('api:notifications:list:old');
