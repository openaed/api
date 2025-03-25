<?php

namespace Database\Seeders;

use App\Models\Defibrillator;
use App\Models\Operator;
use App\Models\AccessToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testAccessToken = AccessToken::create([
            'token' => Str::uuid(),
            'expires_at' => null,
            'assigned_to' => 'Test User',
            'scope' => ['read'],
            'assignee_email' => 'test@test.com',
            'is_active' => true
        ]);

        $testOperator = Operator::create([
            'id' => Str::uuid(),
            'name' => 'Test Operator',
            'website' => 'https://example.com',
            'email' => 'info@operator.com',
            'phone' => '0123456789',
        ]);

        Defibrillator::create([
            'id' => Str::uuid(),
            'osm_id' => null,
            'latitude' => 51.829859452273375,
            'longitude' => 5.801144976892242,
            'raw_osm' => [],
            'operator_id' => $testOperator->id,
            'access' => 'public',
            'indoor' => false,
            'locked' => false,
            'location' => 'In front of the building',
            'manufacturer' => 'Zoll',
            'model' => 'AED Plus',
            'opening_hours' => '24/7',
            'image' => 'https://example.com/image.jpg',
        ]);

        Defibrillator::create([
            'id' => Str::uuid(),
            'osm_id' => null,
            'latitude' => 51.83088590526459,
            'longitude' => 5.799905126801341,
            'raw_osm' => [],
            'operator_id' => $testOperator->id,
            'access' => 'public',
            'indoor' => false,
            'locked' => false,
            'location' => 'Past the entrance',
            'manufacturer' => 'Zoll',
            'model' => 'AED Plus',
            'opening_hours' => '24/7',
            'image' => 'https://example.com/image.jpg',
        ]);

        Defibrillator::create([
            'id' => Str::uuid(),
            'osm_id' => null,
            'latitude' => 51.829090398863904,
            'longitude' => 5.796182165356441,
            'raw_osm' => [],
            'operator_id' => $testOperator->id,
            'access' => 'public',
            'indoor' => false,
            'locked' => false,
            'location' => 'Next to the front door',
            'manufacturer' => 'Zoll',
            'model' => 'AED Plus',
            'opening_hours' => '24/7',
            'image' => 'https://example.com/image.jpg',
        ]);


    }
}