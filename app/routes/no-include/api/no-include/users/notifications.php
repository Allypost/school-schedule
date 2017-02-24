<?php

use Allypost\Lessons\Attendee;
use Allypost\Lessons\Lesson;
use Allypost\User\User;
use Allypost\User\UserData;

$app->get('/notifications', function () use ($app) {
    $u = new User();

    $attendeesTable = (new Attendee())->getTable();
    $userTable      = (new User())->getTable();
    $userDataTable  = (new UserData())->getTable();
    $lessonTable    = (new Lesson())->getTable();

    $users = $u
        ->distinct()
        ->select("{$userTable}.id", "{$userTable}.name", "{$userDataTable}.notification_seen as seen")
        ->join($userDataTable, "{$userDataTable}.user_id", '=', "users.id")
        ->join($attendeesTable, "{$attendeesTable}.user_id", '=', "users.id")
        ->whereIn("{$attendeesTable}.lesson_id", function ($query) use ($lessonTable, $app) {
            return $query->select('id')
                         ->from($lessonTable)
                         ->where('owner', $app->auth->id);
        })
        ->get()->toArray();

    $return = [];

    foreach ($users as $user) {
        $return[ $user[ 'id' ] ] = array_except($user, 'id');
    }

    $app->o->say('user notifications', $return);
})->name('api:users:notifications:seen');
