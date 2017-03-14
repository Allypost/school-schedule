<?php

use Allypost\Lessons\Attendee;
use Allypost\Lessons\Lesson;
use Allypost\Lessons\Schedule;

$app->post('/', function () use ($app) {
    $s = new Schedule();
    $r = $app->request;
    $u = $app->auth;

    $lessonID = (int) $r->post('lesson_id') ?: $r->post('subject');
    $location = [
        'week' => (int) $r->post('week'),
        'day' => $r->post('day'),
        'period' => (int) $r->post('period'),
    ];

    $entry = $s->where($location)->with('lesson')->first() ?: new Schedule($location);
    $lesson = $entry->lesson ?? new Lesson([ 'id' => $lessonID, 'owner' => $u->id ]);
    $old = array_collapse([ $entry->toArray() ]);

    if ($entry->hasClass == '1' && $lesson->owner != $u->id)
        $app->o->err('lessons not owned', [ 'You don\'t teach that lesson' ]);

    $hasClass = $lessonID > 0;

    if ($hasClass)
        $entry->lesson_id = $lessonID;

    $entry->hasClass = (string) ((int) $hasClass);
    $entry->status = '';
    $entry->save();

    $newData = $entry->toArray();
    $new = array_collapse([ $newData[ 'lesson' ], array_except($newData, 'lesson') ]);

    $new[ 'owned' ] = ($new[ 'owner' ] ?? $u->id) == $u->id;

    $data = compact('old', 'new');
    $app->log->log('lessons ' . (($lessonID < 1) ? 'delete' : 'update'), $data);

    $app->o->say('lessons update', $data);
})->name('api:lessons:update');

$app->post('/status', function () use ($app) {
    $s = new Schedule();
    $r = $app->request;
    $u = $app->auth;

    $status = $r->post('status');
    $location = [
        'week' => (int) $r->post('week'),
        'day' => $r->post('day'),
        'period' => (int) $r->post('period'),
    ];

    $entry = $s->where($location)->with('lesson')->first();
    $old = $entry->toArray();

    if (!$entry)
        $app->o->err('lessons status not found', [ 'The lesson does not exist' ]);

    $lesson = $entry->lesson;

    if ($lesson->owner != $u->id)
        $app->o->err('lessons status not owned', [ 'You don\'t teach that lesson' ]);

    if ($entry->status == $status) {
        $new = $entry->toArray();
        $app->o->say('lessons update', compact('old', 'new'));
    }

    $entry->status = $status;
    if (strtolower($status) == 'cancelled') {
        $nf = new NumberFormatter('en_GB', NumberFormatter::ORDINAL);
        $message = sprintf('Class `%s` (W%d %s %s period) has been cancelled', $lesson->name, $location[ 'week' ], ucfirst($location[ 'day' ]), $nf->format($location[ 'period' ]));
        $lesson->notify($message);
    }

    $entry->save();
    $new = $entry->toArray();

    $data = compact('old', 'new');
    $app->log->log('lessons status update', $data);

    $app->o->say('lessons status update', $data);
})->name('api:lessons:update:status');

$app->any('/attending', function () use ($app) {
    $r = $app->request;
    $u = $app->auth;

    $lessons = $r->post('lessons', []);

    $valid = Attendee::checkData($lessons);

    if (!$valid)
        $app->o->err('lessons attending update', [ 'Something went wrong while updating your preferences' ]);

    foreach ($lessons as $lesson) {
        $id = (int) $lesson[ 'id' ];
        $attending = $lesson[ 'attending' ] === 'true';
        $lessn = $u->attendee()->where('lesson_id', $id)->where('user_id', $u->id)->first();

        if ($attending && !$lessn) {
            Attendee::create([ 'user_id' => $u->id, 'lesson_id' => $id ]);
            continue;
        }

        if (!$attending && $lessn) {
            $lessn->delete();
            continue;
        }

    }

    $app->o->say('lessons attending update', [ 'new' => $u->attending()->get()->toArray() ]);
})->name('api:lessons:update:attending');
