<?php

namespace Allypost\User;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserData extends Eloquent {

    public static $default  = [
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
    protected     $table    = 'users_data';
    protected     $fillable = [
        'active',
        'activation_code',
        'banned',
        'banned_until',
        'profile_pic',
    ];
    protected     $casts    = [
        'active'       => 'boolean',
        'banned'       => 'boolean',
        'banned_until' => 'datetime',
        'profile_pic'  => 'array',
    ];

    /*  <PRESETS>    */
    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /*  </PRESETS>   */

    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'id', 'user_id');
    }

}
