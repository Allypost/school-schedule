<?php

$app->any('/error', function () use ($app) {
    err('generic error', [ 'Generic error first', 'Generic error second' ]);
})->name('api:misc:error');
