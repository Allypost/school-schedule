<?php

$app->group('/auth', function () use ($app, $loggedIn, $guest, $teacher, $student) {
    require_once 'no-include/auth/register.php';
    require_once 'no-include/auth/login.php';
    require_once 'no-include/auth/logout.php';
});

$app->group('/misc', function () use ($app, $loggedIn, $guest, $teacher, $student) {
    require_once 'no-include/misc/pause.php';
    require_once 'no-include/misc/error.php';
});

$app->group('/lessons', $loggedIn(), function () use ($app, $loggedIn, $guest, $teacher, $student) {
    require_once 'no-include/lessons/list.php';
    require_once 'no-include/lessons/update.php';
    require_once 'no-include/lessons/modify.php';
    require_once 'no-include/lessons/delete.php';
});

$app->group('/notifications', $loggedIn(), function () use ($app, $loggedIn, $guest, $teacher, $student) {
    require_once 'no-include/notifications/list.php';
    require_once 'no-include/notifications/seen.php';
});

$app->group('/user', function () use ($app, $loggedIn, $guest, $teacher, $student) {
    require_once 'no-include/user/invite.php';
    require_once 'no-include/user/signup.php';
});
