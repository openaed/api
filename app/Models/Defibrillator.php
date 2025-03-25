<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defibrillator extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'osm_id',
        'latitude',
        'longitude',
        'raw_osm', // JSON
        'operator_id',
        'access',
        'indoor',
        'locked',
        'location',
        'manufacturer',
        'model',
        'opening_hours',
        'image',
    ];

    protected $casts = [
        'raw_osm' => 'array',
        'indoor' => 'boolean',
        'locked' => 'boolean',
    ];

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the amount of metres between this defibrillator and a given point.
     * @param float $latitude
     * @param float $longitude
     * @return float The distance in metres.
     */
    public function distanceFromPoint($latitude, $longitude)
    {
        $earthRadius = 6371000; // metres
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}