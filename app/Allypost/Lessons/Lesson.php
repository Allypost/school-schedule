<?php

namespace Allypost\Lessons;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class Lesson extends Eloquent {

    protected $table = 'lessons';

    # <PRESETS>
    protected $fillable = [
        'owner',
        'name',
        'subject',
        'due',
        'period',
        'day',
        'week',
        'owned',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'due',
    ];

    protected $casts = [
        'owned' => 'boolean',
    ];

    # </PRESETS>

    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'owner');
    }

    public function mine($withAttendees = FALSE) {
        $app = $this->app();

        if (!$app->auth)
            return [];

        $lessons = $this->where('owner', $app->auth->id);

        if ($withAttendees)
            $lessons = $lessons->with([ 'attendees', 'owner' ]);

        return $lessons->get()->toArray();
    }

    public function attendees() {
        return $this->hasMany('Allypost\Lessons\Attendee')->with('user');
    }

    public function schedule() {
        return $this->hasMany('Allypost\Lessons\Schedule');
    }

    public function notifications() {
        $app = $this->app();

        return $this->hasMany('Allypost\Lessons\Notification')->where('created_at', '>', $app->auth->data->notification_seen);
    }

    public function app() {
        return Slim::getInstance();
    }
}
