<?php

namespace Allypost\Lessons;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Lessons extends Eloquent {

    protected $table = 'lessons';

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
    ];

    # </PRESETS>

    public function owner() {
        return $this->belongsTo('Allypost\User\User', 'id', 'owner');
    }
}
