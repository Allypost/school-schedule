<?php

namespace Allypost\Lessons;

use Carbon\Carbon;
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
        'status',
        'hasClass',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'owned'     => 'boolean',
        'hasClass'  => 'boolean',
        'attending' => 'boolean',
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

    public static function checkDue($due) {
        $date = Carbon::createFromFormat('Y-m-d', $due);

        return Carbon::now()->diffInDays($date, TRUE) >= 3;
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

    public function notify($message) {
        $this->notifications()->create([ 'lesson_id' => $this->id, 'message' => $message ]);
    }

    public function formatDate($due = NULL) {
        return Carbon::createFromFormat('Y-m-d', $due ?? $this->due)->format('dS M Y');
    }

    public function app() {
        return Slim::getInstance();
    }
}
