<?php

namespace Allypost\Lessons;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class Notification extends Eloquent {

    protected $table = 'notifications';

    # <PRESETS>
    protected $fillable = [
        'lesson_id',
        'message',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
    ];

    # </PRESETS>

    public function lesson() {
        return $this->belongsTo('Allypost\Lessons\Lesson');
    }

    public function mine($all = FALSE) {
        $query = $this->select('notifications.message', 'notifications.created_at as date', 'lessons.name', 'lessons.id as subject', 'lessons.due')
                      ->join('lessons', 'notifications.lesson_id', 'lessons.id')
                      ->orderBy('notifications.created_at', 'DESC');

        if (!$all)
            $query = $query->where('notifications.created_at', '>', $this->app()->auth->data->notification_seen);

        return $query;
    }

    public function app() {
        return Slim::getInstance();
    }
}
