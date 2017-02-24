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
        $app->o->err('lessons modify', [ 'The name is required' ]);

    if (!$id)
        $lesson = $l;
    else
        $lesson = $l->where('id', $id)->first();

    if (!$lesson)
        $app->o->err('lessons modify', [ 'That lesson doesn\'t exist' ]);

    if ($name != $lesson->name && $l->where('name', $name)->first())
        $app->o->err('lessons modify', [ 'That name is taken' ]);

    $oldLesson = $lesson;

    $lesson->owner = $u->id;
    $lesson->name  = $name;

    if ($due) {

        if (!$lesson::checkDue($due))
            $app->o->err('lessons modify', [ 'That date is not valid' ]);

        if ($lesson->due != $due) {
            $lesson->due = $due;
            $message     = sprintf('Class `%s` is due on %s', $lesson->name, $lesson->formatDate());
        }

    } elseif ($lesson->due) {
        $message     = sprintf('Class `%s` is no longer due', $lesson->name);
        $lesson->due = NULL;
    }

    if (!$lesson->subject)
        $lesson->subject = $name;

    $lesson->save();

    $app->log->log('lessons modify', [ 'old' => $oldLesson->toArray(), 'new' => $lesson->toArray() ]);

    if ($message)
        $lesson->notify($message);

    $data = $lesson->toArray();

    $app->o->say('lessons modify', $data);
})->name('api:lessons:modify');
