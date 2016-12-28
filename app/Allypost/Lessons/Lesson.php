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
        'status',
        'due',
        'period',
        'day',
        'week',
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
        'due'
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

    public function app() {
        return Slim::getInstance();
    }
}
