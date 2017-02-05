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
        'user_id',
        'lesson_id',
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

    /* ######################################### */
    /* # <Wrappers for Eloquent relationships> # */
    /* ######################################### */
    public function lesson() {
        return $this->belongsTo('Allypost\Lessons\Lesson', 'id', 'lesson_id');
    }

    public function user() {
        return $this->hasOne('Allypost\User\User', 'id', 'user_id');
    }
    /* ######################################### */
    /* # </Wrappers for Eloquent relationships> # */
    /* ######################################### */

    /**
     * Get the current instance of the App
     *
     * @return Slim The instance
     */
    public function app(): Slim {
        return Slim::getInstance();
    }


    /**
     * Check whether the data is a valid Attendee entry (from POST data)
     *
     * @param array $data The data to be checked
     *
     * @return bool Result of validation
     */
    public static function checkData(array $data): bool {
        foreach ($data as $datum)
            if (
                !isset($datum[ 'attending' ], $datum[ 'id' ]) ||
                empty($datum[ 'attending' ]) || empty($datum[ 'id' ])
            )
                return FALSE;

        return TRUE;
    }
}
