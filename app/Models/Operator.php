<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'website',
        'email',
        'phone',
    ];

    public function defibrillators()
    {
        return $this->hasMany(Defibrillator::class);
    }
}