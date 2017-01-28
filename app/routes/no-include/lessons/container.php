<?php

$app->group('/lessons', $loggedIn(), function () use ($app, $loggedIn, $guest, $teacher, $student) {

    require_once 'no-include/lessons.php';

});
