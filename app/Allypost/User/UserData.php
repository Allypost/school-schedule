<?php

namespace Allypost\User;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserData extends Eloquent {

    protected $table = 'users_data';

    protected $fillable = [
        'active',
        'activation_code',
        'banned',
        'banned_until',
        'dob',
        'sex',
        'notification_seen',
    ];

    protected $casts  = [
        'active'            => 'boolean',
        'banned'            => 'boolean',
        'banned_until'      => 'datetime',
        'notification_seen' => 'datetime',
    ];
    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /*  <PRESETS>    */

    public static $default = [
        'active' => FALSE,
        'banned' => FALSE,
    ];


    /*  </PRESETS>   */

    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'id', 'user_id');
    }

    public function user() {
        return $this->owner();
    }

    public function permissions() {
        return $this->hasOne('Allypost\User\UserPermission', 'user_id', 'user_id');
    }

}
