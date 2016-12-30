<?php

namespace Allypost\Lessons;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class Schedule extends Eloquent {

    protected $table = 'schedule';

    # <PRESETS>
    protected $fillable = [
        'lesson_id',
        'day',
        'week',
        'period',
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
        'id',
    ];

    protected $casts = [
        'hasClass' => 'boolean',
    ];

    # </PRESETS>

    public function lesson() {
        return $this->belongsTo('Allypost\Lessons\Lesson');
    }

    public function user() {
        return $this->hasOne('Allypost\User\User', 'id', 'user_id');
    }

    public function app() {
        return Slim::getInstance();
    }
}
