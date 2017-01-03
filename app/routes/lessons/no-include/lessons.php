<?php

$app->get('/', function () use ($app) {
    $app->render('lessons/home.twig');
})->name('lessons:home');
