<?php

use Allypost\Lessons\Lesson;

$app->post('/modify', function () use ($app) {
    $l = new Lesson();
    $r = $app->request;
    $u = $app->auth;

    $name = $r->post('name');
    $due  = $r->post('due');
    $id   = (int) $r->post('subject', 0);

    if (empty($name))
        err('lessons modify', [ 'The name is required' ]);

    if (!$id)
        $lesson = $l;
    else
        $lesson = $l->where('id', $id)->first();

    if (!$lesson)
        err('lessons modify', [ 'That lesson doesn\'t exist' ]);

    if ($name != $lesson->name && $l->where('name', $name)->first())
        err('lessons modify', [ 'That name is taken' ]);

    $lesson->owner = $u->id;
    $lesson->name  = $name;

    if ($due)
        if ($lesson::checkDue($due))
            $lesson->due = $due;
        else
            err('lessons modify', [ 'That date is not valid' ]);
    else
        $lesson->due = NULL;

    if (!$lesson->subject)
        $lesson->subject = $name;

    $lesson->save();

    $data = [
        'name' => $lesson->name,
        'id'   => $lesson->id,
    ];

    say('lessons modify', $data);
})->name('api:lessons:modify');
