<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $fillable = [
        'id',
        'type',
        'description',
        'data',
        'access_token'
    ];

    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function accessToken()
    {
        return $this->belongsTo(AccessToken::class, 'access_token', 'id');
    }
}