<?php

use Allypost\Lessons\Lesson;

$app->get('/notifications', $loggedIn(), $teacher(), function () use ($app) {
    $n = new Lesson();

    say('lesson notifications', $n->allNotifications());
})->name('api:lessons:notifications');
