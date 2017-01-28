<?php

$app->group('/user', function () use ($app, $loggedIn, $guest, $teacher, $student) {

    require_once 'no-include/invite.php';
    require_once 'no-include/signup.php';

});
