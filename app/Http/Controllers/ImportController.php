<?php

namespace App\Http\Controllers;

use App\Mail\ImportErrored;
use App\Mail\ImportSuccess;
use App\Models\Defibrillator;
use App\Models\Import;
use App\Models\Operator;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ImportController extends Controller
{
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

        // Get the last finished_at date from the Imports table
        $lastSyncDateTime = Import::where('status', 'finished')
            ->orderBy('finished_at', 'desc')
            ->first()
                ?->finished_at;
        $queryMinDate = null;
        if ($lastSyncDateTime && !$doFullImport) {
            $lastSyncDateTime = Carbon::parse($lastSyncDateTime);
            $year = $lastSyncDateTime->year;
            $month = $lastSyncDateTime->format('m');
            $day = $lastSyncDateTime->format('d');

            $queryMinDate = "%28newer%3A%22{$year}-{$month}-{$day}T00%3A00%3A00Z%22%29";
        }

        try {
            if ($overrideRegion) {
                $region = $overrideRegion;
            } else {
                $region = config('app.import.region');
            }

            if (strpos($region, ';') !== false) {
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

            foreach ($defibrillators as $defibrillator) {
                if ($defibrillator['type'] !== 'node') {
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
            if (!empty(config('mail.monitoring_recipient'))) {
                Mail::to(config('mail.monitoring_recipient'))->send(new ImportSuccess($import));
            }

        } catch (\Exception $e) {
            $import->update(['status' => 'errored']);
            if (!empty(config('mail.monitoring_recipient'))) {
                Mail::to(config('mail.monitoring_recipient'))->send(new ImportErrored($import, $e->getMessage()));
            }
            throw $e;
        }

        return $import;
    }

    private static function handleDefibrillator(array $node, array $tags): void
    {
        if (array_key_exists('indoor', $tags)) {
            $indoor = $tags['indoor'] == 'yes' ? true : ($tags['indoor'] == 'no' ? false : null);
        }

        if (array_key_exists('locked', $tags)) {
            $locked = $tags['locked'] == 'yes' ? true : ($tags['locked'] == 'no' ? false : null);
        }

        if (array_key_exists('access', $tags) && $tags['access'] == 'unknown') {
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
            ]
        );

        static::updateNominatim($defibrillator, $node['latitude'], $node['longitude']);

        if (array_key_exists('operator', $tags) && !$defibrillator->operator_id) {
            $operator = Operator::where('name', $tags['operator'])->first();

            // Manual corrections
            if (array_key_exists('email', $tags) && !array_key_exists('operator:email', $tags)) {
                $tags['operator:email'] = $tags['email'];
            }

            if (array_key_exists('phone', $tags) && !array_key_exists('operator:phone', $tags)) {
                $tags['operator:phone'] = $tags['phone'];
            }

            if (array_key_exists('website', $tags) && !array_key_exists('operator:website', $tags)) {
                $tags['operator:website'] = $tags['website'];
            }

            if (!$operator) {
                $operator = Operator::create([
                    'id' => Str::uuid(),
                    'name' => $tags['operator'],
                    'website' => $tags['operator:website'] ?? null,
                    'email' => $tags['operator:email'] ?? null,
                    'phone' => $tags['operator:phone'] ?? null,
                ]);
            } else {
                $operator->website = $operator->website ?? $tags['operator:website'] ?? null;
                $operator->email = $operator->email ?? $tags['operator:email'] ?? null;
                $operator->phone = $operator->phone ?? $tags['operator:phone'] ?? null;
                $operator->save();
            }

            $defibrillator->operator()->associate($operator);
            $defibrillator->save();
        }
    }

    public static function updateNominatim(Defibrillator $defibrillator, $newLat = null, $newLon = null): array|null
    {
        $hasLocationChanged = false;
        if (!$newLat || $newLon) {
            $newLat = $defibrillator->latitude;
            $newLon = $defibrillator->longitude;
        }

        if ($newLat && $newLon) {
            $hasLocationChanged = ($defibrillator->latitude != $newLat || $defibrillator->longitude != $newLon);
        }

        if ($defibrillator->address && $hasLocationChanged) {
            return ['full_address' => 'test1', 'address' => null];
        }

        $nominatimUrl = config('app.nominatim.url') . '/reverse?format=json&lat=' . $newLat . '&lon=' . $newLon . '&layer=address';

        $response = Http::get($nominatimUrl);

        if ($response->successful()) {
            $data = $response->json();
            $address = $data['address'] ?? null;
            if ($address) {
                $defibrillator->address = json_encode(
                    [
                        'full_address' => $data['display_name'] ?? null,
                        'address' => $address,
                    ]
                );
                $defibrillator->save();

                return [
                    'full_address' => $data['display_name'] ?? null,
                    'address' => $address,
                ];
            }
        }

        return null;
    }
}