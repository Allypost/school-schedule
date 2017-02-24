<?php

$app->any('/pause', function () use ($app) {
    sleep(3);
    $app->o->say('misc', (array) $_REQUEST);
})->name('api:misc:pause');
