<?php

use Carbon\Carbon;

$app->post('/seen', function () use ($app) {
    $u = $app->auth;

    $u->data->update([ 'notification_seen' => Carbon::now() ]);

    $app->o->say('notifications seen', [ 'done' => TRUE ]);
})->name('api:notifications:seen');
