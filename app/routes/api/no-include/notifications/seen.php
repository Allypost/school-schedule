<?php

use Illuminate\Database\Capsule\Manager as DB;

$app->post('/seen', function () use ($app) {
    $u = $app->auth;

    $u->data->update([ 'notification_seen' => DB::raw('NOW()') ]);

    say('notifications seen', [ 'done' => TRUE ]);
})->name('api:notifications:seen');
