<?php

$app->group('/js', function () use ($app, $loggedIn, $guest, $teacher, $student) {
    require_once 'js/get.php';
    require_once 'js/minify.php';
});
