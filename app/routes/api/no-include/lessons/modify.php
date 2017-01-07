<?php

use Allypost\Lessons\Lesson;

$app->post('/modify', function () use ($app) {
    $l       = new Lesson();
    $r       = $app->request;
    $u       = $app->auth;
    $message = '';

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

    $oldLesson = $lesson;

    $lesson->owner = $u->id;
    $lesson->name  = $name;

    if ($due) {

        if (!$lesson::checkDue($due))
            err('lessons modify', [ 'That date is not valid' ]);

        if ($lesson->due != $due) {
            $lesson->due = $due;
            $message     = sprintf('Class `%s` is due on %s', $lesson->name, $lesson->formatDate());
        }

    } elseif ($lesson->due) {
        $message     = sprintf('Class `%s` is no longer due', $lesson->name);
        $lesson->due = NULL;
    }

    if ($message)
        $lesson->notify($message);

    if (!$lesson->subject)
        $lesson->subject = $name;

    $lesson->save();

    $app->log->log('lessons modify', [ 'old' => $oldLesson->toArray(), 'new' => $lesson->toArray() ]);

    $data = [
        'name' => $lesson->name,
        'id'   => $lesson->id,
    ];

    say('lessons modify', $data);
})->name('api:lessons:modify');
