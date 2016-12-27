<?php

$app->group('/lessons', function () use ($app, $loggedIn, $admin, $guest, $cache) {

    require_once 'no-include/lessons.php';

});
