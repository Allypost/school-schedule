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
        'profile_pic',
        'notification_seen',
    ];

    protected $casts  = [
        'active'            => 'boolean',
        'banned'            => 'boolean',
        'banned_until'      => 'datetime',
        'profile_pic'       => 'array',
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
        'active'      => FALSE,
        'banned'      => FALSE,
        'profile_pic' => [
            'url'  => [
                'original' => '',
                'small'    => '',
                'medium'   => '',
                'big'      => '',
            ],
            'path' => [
                'isLocal'  => '',
                'absolute' => '',
                'filename' => '',
                'location' => '',
            ],
        ],
    ];


    /*  </PRESETS>   */

    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'id', 'user_id');
    }

}
