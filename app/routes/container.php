<?php

$isApi = $_SERVER[ 'IS_API' ] ?? '0' == '1';

if (!$isApi) {

    $app->group('/api', function () use ($app, $loggedIn, $guest, $teacher, $student) {
        require_once 'no-include/api/container.php';
    });

    require_once 'no-include/home/container.php';
    require_once 'no-include/lessons/container.php';
    require_once 'no-include/notifications/container.php';
    require_once 'no-include/user/container.php';

} else {

    require_once 'no-include/api/container.php';

}
