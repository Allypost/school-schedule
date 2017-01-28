<?php

$app->get('/invitation', $loggedIn(), $teacher(), function () use ($app) {
    $app->render('user/invite.twig');
})->name('user:invite');
