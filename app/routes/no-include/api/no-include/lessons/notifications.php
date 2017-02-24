<?php

use Allypost\Lessons\Lesson;

$app->get('/notifications', $loggedIn(), $teacher(), function () use ($app) {
    $n = new Lesson();

    $app->o->say('lesson notifications', $n->allNotifications());
})->name('api:lessons:notifications');
