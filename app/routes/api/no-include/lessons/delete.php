<?php

use Allypost\Lessons\Lesson;

$app->post('/delete', function () use ($app) {
    $l = new Lesson();
    $r = $app->request;
    $u = $app->auth;

    $id = (int) $r->post('subject', 0);

    $lesson = $l->where('id', $id)->first();

    if (!$lesson)
        err('lessons delete', [ 'That lesson doesn\'t exist' ]);

    if ($lesson->owner != $u->id)
        err('lessons delete', [ 'You don\'t teach that lesson' ]);

    $deleted = $lesson->delete();

    say('lessons delete', compact('deleted'));
})->name('api:lessons:delete');
