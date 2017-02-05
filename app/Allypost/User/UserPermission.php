<?php

namespace Allypost\User;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserPermission extends Eloquent {

    const TYPES         = [
        'student' => 1,
        'teacher' => 2,
    ];
    const TYPES_STUDENT = 1;
    const TYPES_TEACHER = 2;

    public static $teacher = [
        'type' => 2,
    ];

    public static $student = [
        'type' => 1,
    ];

    protected $table = 'users_permissions';

    # <PRESETS>
    protected $fillable = [
        'type',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    # </PRESETS>

    /* ######################################### */
    /* # <Wrappers for Eloquent relationships> # */
    /* ######################################### */
    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'id', 'user_id');
    }
    /* ######################################### */
    /* # </Wrappers for Eloquent relationships> # */
    /* ######################################### */
}
