<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'id',
        'started_at',
        'finished_at',
        'status',
        'defibrillators',
        'is_full_import'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'defibrillators' => 'integer'
    ];

    protected $keyType = 'string';
    public $incrementing = false;
}
