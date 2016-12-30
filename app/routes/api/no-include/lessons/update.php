<?php

use Allypost\Lessons\Schedule;

$app->post('/', function () use ($app) {
    $s = new Schedule();
    $r = $app->request;
    $u = $app->auth;

    $lessonID = (int) $r->post('lesson_id') ?: $r->post('subject');
    $location = [
        'week'   => (int) $r->post('week'),
        'day'    => $r->post('day'),
        'period' => (int) $r->post('period'),
    ];

    $entry  = $s->where($location)->with('lesson')->first() ?: new Schedule($location);
    $lesson = $entry->lesson ?? (object) [ 'id' => FALSE, 'owner' => FALSE ];
    $old    = $entry->toArray();

    if ($lesson->owner && $lesson->owner != $u->id)
        err('lessons not owned', [ 'You don\'t teach that lesson' ]);

    if ($lesson->id && $lesson->id == $lessonID) {
        $new = $entry->toArray();
        say('lessons update', compact('old', 'new'));
    }

    $entry->lesson_id = $lessonID;

    if ($lessonID < 1)
        $entry->delete();
    else
        $entry->save();

    $new = $entry->toArray();
    say('lessons update', compact('old', 'new'));
})->name('api:lessons:update');
