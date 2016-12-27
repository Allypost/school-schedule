<?php

$app->any('/error', function () use ($app) {
    err('generic error', (array) $_REQUEST);
})->name('api:misc:error');
