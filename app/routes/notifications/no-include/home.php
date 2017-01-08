<?php

use Allypost\Lessons\Notification;

$app->get('/', function () use ($app) {
    $n = new Notification();

    dd($n->mine()->get()->toArray());
})->name('notifications:view');
