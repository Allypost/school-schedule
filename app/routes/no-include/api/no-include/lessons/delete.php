<?php

use Allypost\Lessons\Lesson;

$app->post('/delete', function () use ($app) {
    $l = new Lesson();
    $r = $app->request;
    $u = $app->auth;

    $id = (int) $r->post('subject', 0);

    $lesson = $l->where('id', $id)->first();

    if (!$lesson)
        $app->o->err('lessons delete', [ 'That lesson doesn\'t exist' ]);

    if ($lesson->owner != $u->id)
        $app->o->err('lessons delete', [ 'You don\'t teach that lesson' ]);

    $lesson->schedule()->delete();
    $deleted = $lesson->delete();

    $app->log->log('lessons delete', [ 'old' => $lesson->toArray() ]);

    $app->o->say('lessons delete', compact('deleted'));
})->name('api:lessons:delete');
