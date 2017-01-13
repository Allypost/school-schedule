<?php

$app->any('/pause', function () use ($app) {
    sleep(3);
    say('misc', (array) $_REQUEST);
})->name('api:misc:pause');
