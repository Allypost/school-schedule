<?php

namespace Allypost\Lessons;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class Attendee extends Eloquent {

    protected $table = 'lessons_attendees';

    # <PRESETS>
    protected $fillable = [
        'owner',
        'name',
        'subject',
        'status',
        'due',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'id',
    ];

    # </PRESETS>

    public function lesson() {
        return $this->belongsTo('Allypost\Lessons\Lesson', 'id', 'lesson_id');
    }

    public function user() {
        return $this->hasOne('Allypost\User\User', 'id', 'user_id');
    }

    public function app() {
        return Slim::getInstance();
    }
}