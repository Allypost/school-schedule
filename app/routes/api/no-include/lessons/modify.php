<?php

use Allypost\Lessons\Lesson;

$app->post('/modify', function () use ($app) {
    $l = new Lesson();
    $r = $app->request;
    $u = $app->auth;

    $name = $r->post('name');
    $id   = (int) $r->post('subject', 0);

    if (empty($name))
        err('lessons modify', [ 'The name is required' ]);

    if (!$id)
        $lesson = $l;
    else
        $lesson = $l->where('id', $id)->first();

    if (!$lesson)
        err('lessons modify', [ 'That lesson doesn\'t exist' ]);

    if ($l->where('name', $name)->first())
        err('lessons modify', [ 'That name is taken' ]);

    $lesson->owner   = $u->id;
    $lesson->name    = $name;
    $lesson->subject = $name;

    $lesson->save();

    $data = [
        'name'    => $lesson->name,
        'subject' => $lesson->id,
    ];

    say('lessons modify', $data);
})->name('api:lessons:modify');
