<?php

$app->get('/', function () use ($app) {
    $app->render('notifications/home.twig');
})->name('notifications:view');
