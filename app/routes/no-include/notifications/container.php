<?php

$app->group('/notifications', $loggedIn(), function () use ($app, $loggedIn, $guest, $teacher, $student) {

    require_once 'no-include/home.php';

});
