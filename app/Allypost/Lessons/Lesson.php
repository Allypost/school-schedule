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
        'dueToday',
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
        'dueToday'  => 'boolean',
    ];

    # </PRESETS>

    # <RELATIONSHIPS>

    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'owner');
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

    # </RELATIONSHIPS>

    /**
     * Get the current instance of the App
     *
     * @return Slim The instance
     */
    public function app(): Slim {
        return Slim::getInstance();
    }


    /**
     * Return all Lessons that belong to the user
     *
     * @param bool $withAttendees Whether to add attendees and owner relationships
     *
     * @return array The Lessons that the current user owns
     */
    public function mine($withAttendees = FALSE) {
        $app = $this->app();

        if (!$app->auth)
            return [];

        $lessons = $this->where('owner', $app->auth->id);

        if ($withAttendees)
            $lessons = $lessons->with([ 'attendees', 'owner' ]);

        return $lessons->get()->toArray();
    }

    /**
     * Check whether the $due date is a valid due date
     *
     * @param string $due The due date in the format Y-m-d
     *
     * @return bool Is date valid
     */
    public static function checkDue(string $due) {
        $date = Carbon::createFromFormat('Y-m-d', $due);

        return Carbon::now()->diffInDays($date, TRUE) >= 3;
    }

    /**
     * Create new notification with message $message
     *
     * @param string $message The message for the notification
     */
    public function notify(string $message) {
        $this->notifications()->create([ 'lesson_id' => $this->id, 'message' => $message ]);
    }

    /**
     * Format date into view displayable format
     *
     * @param string $due The date in the format Y-m-d
     *
     * @return string The formatted date
     */
    public function formatDate($due = NULL) {
        return Carbon::createFromFormat('Y-m-d', $due ?? $this->due)->format('dS M Y');
    }
}
