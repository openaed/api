<?php

namespace App\Http\Controllers;

use App\Models\Defibrillator;
use App\Models\Operator;
use App\Models\Import;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportController extends Controller {
    /**
     * Import defibrillators from OpenStreetMap
     *
     * @param bool doFullImport Whether to do a full import - import ALL Defibrillators, regardless of update time
     *
     * @return Import The import object
     */
    public static function importDefibrillators(bool $doFullImport = false, string $overrideRegion = null): Import
    {

        $import = Import::create([
            'id' => Str::uuid(),
            'status' => 'started',
            'defibrillators' => 0,
            'is_full_import' => $doFullImport
        ]);

        $lastSyncDateTime = Defibrillator::max('last_synced_at');
        $queryMinDate = null;
        if($lastSyncDateTime && !$doFullImport) {
            $lastSyncDateTime = Carbon::parse($lastSyncDateTime);
            $year = $lastSyncDateTime->year;
            $month = $lastSyncDateTime->format('m');
            $day = $lastSyncDateTime->format('d');

            $queryMinDate = "%28newer%3A%22{$year}-{$month}-{$day}T00%3A00%3A00Z%22%29";
        }

        try {
            if($overrideRegion) {
                $region = $overrideRegion;
            } else {
                $region = config('app.import.region');
            }

            if(strpos($region, ';') !== false) {
                $region = explode(';', $region);
                $region = implode(',', $region);
            }

            $overpassUrl = "https://overpass-api.de/api/interpreter?data=%5Bout%3Ajson%5D%5Btimeout%3A25%5D%3B%0Aarea%28id%3A{$region}%29-%3E.searchArea%3B%0Anode%5B%22emergency%22%3D%22defibrillator%22%5D%28area.searchArea%29{$queryMinDate}%3B%0Aout%20geom%3B%0A";

            $import->update(['status' => 'requesting']);

            $response = Http::withHeaders([
                'User-Agent' => 'OpenAED/1.0'
            ])->get($overpassUrl);

            $import->update(['status' => 'processing']);

            $defibrillators = $response->json()['elements'];

            $import->defibrillators = count($defibrillators);

            $import->update(['status' => 'updating']);

            foreach($defibrillators as $defibrillator) {
                if($defibrillator['type'] !== 'node') {
                    continue;
                }

                $node = [
                    'osm_id' => $defibrillator['id'],
                    'latitude' => $defibrillator['lat'],
                    'longitude' => $defibrillator['lon'],
                ];

                static::handleDefibrillator($node, $defibrillator['tags']);
            }

            $import->update(['status' => 'finished', 'finished_at' => now()]);

        } catch (\Exception $e) {
            $import->update(['status' => 'errored']);
            throw $e;
        }

        return $import;
    }

    private static function handleDefibrillator(array $node, array $tags): void
    {
        if(array_key_exists('indoor', $tags)) {
            $indoor = $tags['indoor'] == 'yes' ? true : ($tags['indoor'] == 'no' ? false : null);
        }

        if(array_key_exists('locked', $tags)) {
            $locked = $tags['locked'] == 'yes' ? true : ($tags['locked'] == 'no' ? false : null);
        }

        if(array_key_exists('access', $tags) && $tags['access'] == 'unknown') {
            $tags['access'] = null;
        }

        $defibrillator = Defibrillator::updateOrCreate(
            ['osm_id' => $node['osm_id']],
            [
            'id' => Str::uuid(),
            'osm_id' => $node['osm_id'],
            'latitude' => $node['latitude'],
            'longitude' => $node['longitude'],

            'raw_osm' => $tags,
            'access' => $tags['access'] ?? null,
            'indoor' => $indoor ?? null,
            'locked' => $locked ?? null,
            'location' => $tags['defibrillator:location'] ?? null,
            'manufacturer' => $tags['manufacturer'] ?? null,
            'model' => $tags['model'] ?? null,
            'opening_hours' => $tags['opening_hours'] ?? null,
            'image' => $tags['image'] ?? null,
            'last_synced_at' => now()
        ]);

        if(array_key_exists('operator', $tags) && !$defibrillator->operator_id) {
            $operator = Operator::where('name', $tags['operator'])->first();
            if(!$operator) {
                $operator = Operator::create([
                    'id' => Str::uuid(),
                    'name' => $tags['operator'],
                    'website' => $tags['operator:website'] ?? null,
                    'email' => $tags['operator:email'] ?? null,
                    'phone' => $tags['operator:phone'] ?? null,
                ]);
            }

            $defibrillator->operator()->associate($operator);
            $defibrillator->save();
        }
    }
}
