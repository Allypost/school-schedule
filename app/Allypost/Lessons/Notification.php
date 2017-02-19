<?php

namespace Allypost\Lessons;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class Notification extends Eloquent {

    protected $table = 'notifications';

    # <PRESETS>
    protected $fillable = [
        'lesson_id',
        'message',
        'seen',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'updated_at',
        'id',
    ];

    protected $casts = [
        'seen' => 'boolean',
    ];

    # </PRESETS>

    /* ######################################### */
    /* # <Wrappers for Eloquent relationships> # */
    /* ######################################### */
    public function lesson() {
        return $this->belongsTo('Allypost\Lessons\Lesson');
    }
    /* ######################################### */
    /* # </Wrappers for Eloquent relationships> # */
    /* ######################################### */

    /**
     * Get the current instance of the App
     *
     * @return Slim The instance
     */
    public function app() {
        return Slim::getInstance();
    }


    /**
     * Return all unread Notifications for the current user
     *
     * @param bool $all Whether to return ALL Notifications or just unseen ones
     *
     * @return Builder The Query Builder object
     */
    public function mine(bool $all = FALSE) {
        $u = $this->app()->auth;

        $query = $this
            ->distinct()
            ->select('notifications.message', 'notifications.created_at as date', 'lessons.name', 'lessons.id as subject', 'lessons.due')
            ->join('lessons', 'notifications.lesson_id', 'lessons.id')
            ->join('lessons_attendees', 'lessons.id', 'lessons_attendees.lesson_id')
            ->where('lessons_attendees.user_id', $u->id)
            ->orderBy('notifications.created_at', 'DESC');

        if ($all)
            $query = $query->addSelect(DB::raw('IF(notifications.created_at < ?, 1, 0) as seen'))->addBinding($u->data->notification_seen, 'select');
        else
            $query = $query->where('notifications.created_at', '>', $u->data->notification_seen);

        return $query;
    }
}
