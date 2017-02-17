<?php

use Allypost\Lessons\Lesson;

$app->group('/attendees', $loggedIn(), $teacher(), function () use ($app, $loggedIn, $guest, $teacher, $student) {

    $app->get('/', function () use ($app) {
        $u = $app->auth;
        $l = new Lesson();

        $lessons = $l->where('owner', $u->id)
                     ->with('attendees')
                     ->get()->toArray();

        if (empty($lessons))
            err('lessons attendees', [ 'You don\'t have any lessons' ]);

        $users = [];

        foreach ($lessons as $lesson) {
            $attendees = $lesson[ 'attendees' ];

            $users[ (int) $lesson[ 'id' ] ] = array_column($attendees, 'user');
        }

        say('lessons attendees', $users);
    })->name('api:lessons:attendees:all');

    $app->get('/:lesson', function ($id) use ($app) {
        $u = $app->auth;
        $l = new Lesson();

        $lesson = $l->where('id', $id)
                    ->where('owner', $u->id)
                    ->with('attendees')
                    ->get()->toArray();

        if (empty($lesson))
            err('lessons attendees', [ 'The lesson doesn\'t exist' ]);

        $users = array_column(array_column($lesson, 'attendees')[ 0 ], 'user');

        say('lessons attendees', $users);
    })->name('api:lessons:attendees');

});
