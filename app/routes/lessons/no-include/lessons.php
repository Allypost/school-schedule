<?php

$app->get('/', function () use ($app) {
    if ($app->auth->isTeacher())
        $app->render('lessons/edit.twig');
    else
        $app->render('lessons/home.twig');
})->name('lessons:home');
