<?php

use Carbon\Carbon;

$app->group('/list', function () use ($app, $loggedIn, $guest, $teacher, $student) {

    $app->get('/attending', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $data = $u->attending()->orderBy('id', 'asc')->get()->toArray();

        say('lessons attending', $data);
    })->name('api:lessons:attending');

    $app->get('/teaching', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $data = $u->lessons()->get()->toArray();

        say('lessons teaching', $data);
    })->name('api:lessons:teaching');

    $app->get('/schedule', $loggedIn(), function () use ($app) {
        $u = $app->auth;

        $data = $u->schedule()->get()->toArray();

        say('lessons schedule', $data);
    })->name('api:lessons:schedule');

    $app->get('/schedule/:time', $loggedIn(), function ($time) use ($app) {
        $u    = $app->auth;
        $time = Carbon::createFromTimestampUTC($time);

        $data = $u->schedule()->where('schedule.updated_at', '>', $time)->get()->toArray();

        say('lessons schedule', $data);
    })->name('api:lessons:schedule:recent');

});
