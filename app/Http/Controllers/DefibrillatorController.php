<?php

namespace App\Http\Controllers;

use App\Models\Defibrillator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DefibrillatorController extends Controller
{

    protected function applyFilters($defibrillators)
    {
        $request = request();
        $basicInfo = $request->query('basic', false);

        if ($basicInfo == 'true') {
            // Hide everything except coordinates, id, osm id and access
            $defibrillators = $defibrillators->map(function ($defibrillator) {
                return $defibrillator->only(['id', 'osm_id', 'latitude', 'longitude', 'access']);
            });
        }

        return $defibrillators;
    }

    /**
     * Get a specific defibrillator by ID.
     * @param Request $request
     * @param string $id The UUID of the defibrillator.
     * @return JsonResponse The defibrillator, or an error response.
     */
    public function getOne(Request $request, $id): JsonResponse
    {
        if (!isValidUuid($id)) {
            return response()->json(['message' => 'Invalid UUID'], 400);
        }

        $defibrillator = Defibrillator::find($id);

        if (!$defibrillator) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $defibrillator->load('operator');
        $defibrillator->makeHidden(['raw_osm', 'operator_id', 'created_at', 'updated_at']);
        $defibrillator->operator->makeHidden(['created_at', 'updated_at']);

        return response()->json($defibrillator);
    }

    /**
     * Find all defibrillators within a given radius of a point.
     * @param Request $request
     * @param float $latitude The latitude of the point.
     * @param float $longitude The longitude of the point.
     * @param integer $radius The radius in metres.
     * @return JsonResponse The defibrillators, or an error response.
     */
    public function getNearby(Request $request, $latitude, $longitude, $radius): JsonResponse
    {
        if (!is_numeric($latitude) || !is_numeric($longitude) || !is_numeric($radius)) {
            return response()->json(['message' => 'Invalid coordinates or radius'], 400);
        }

        $defibrillators = Defibrillator::whereRaw(
            'ST_DistanceSphere(ST_MakePoint(longitude, latitude), ST_MakePoint(?, ?)) < ?',
            [$longitude, $latitude, $radius]
        )->get();

        $defibrillators->load('operator');
        $defibrillators->makeHidden(['raw_osm', 'operator_id', 'created_at', 'updated_at']);
        $defibrillators->each(function ($defibrillator) use ($latitude, $longitude) {
            $defibrillator->operator->makeHidden(['created_at', 'updated_at']);
            $defibrillator->distance = $defibrillator->distanceFromPoint($latitude, $longitude);
        });

        $defibrillators = $defibrillators->sortBy('distance')->values();

        return response()->json($this->applyFilters($defibrillators));
    }

    /**
     * Get all defibrillators within an area of 3 or more points.
     * @param Request $request
     * @return JsonResponse The defibrillators, or an error response.
     */
    public function getInArea(Request $request): JsonResponse
    {
        $body = $request->json()->all();
        $points = $body['points'] ?? null;

        if (
            in_array(false, array_map(function ($point) {
                return is_array($point) && count($point) === 2 && is_numeric($point[0]) && is_numeric($point[1]);
            }, $points))
        ) {
            return response()->json(['message' => 'Invalid coordinates'], 400);
        }

        if (!is_array($points) || count($points) < 3) {
            return response()->json(['message' => 'Invalid points. At least 3 are needed.'], 400);
        }

        if ($points[0] != end($points)) {
            return response()->json(['message' => 'Invalid area. First and last point must be the same.'], 400);
        }

        $points = array_map(function ($point) {
            return array_map('floatval', $point);
        }, $points);

        // Invert points to be in the format 'longitude latitude'
        $points = array_map(function ($point) {
            return [$point[1], $point[0]];
        }, $points);

        $polygon = 'POLYGON((' . implode(', ', array_map(function ($point) {
            return implode(' ', $point);
        }, $points)) . '))';

        $defibrillators = Defibrillator::whereRaw(
            'ST_Within(ST_MakePoint(longitude, latitude), ST_GeomFromText(?))',
            [$polygon]
        )->get();

        $defibrillators->load('operator');
        $defibrillators->makeHidden(['raw_osm', 'operator_id', 'created_at', 'updated_at']);
        $defibrillators->each(function ($defibrillator) {
            if ($defibrillator->operator) {
                $defibrillator->operator->makeHidden(['created_at', 'updated_at']);
            }
        });

        return response()->json($this->applyFilters($defibrillators));
    }

    /**
     * Get all defibrillators.
     * @param Request $request
     * @return JsonResponse The defibrillators.
     */
    public function getAll(Request $request): JsonResponse
    {
        $accessToken = $request->attributes->get('access_token');
        if (!$accessToken->hasScope('export')) {
            return response()->json(['message' => 'Insufficient scope access'], 403);
        }

        $defibrillators = Defibrillator::all();

        $defibrillators->load('operator');
        $defibrillators->makeHidden(['raw_osm', 'operator_id', 'created_at', 'updated_at']);
        $defibrillators->each(function ($defibrillator) {
            if ($defibrillator->operator) {
                $defibrillator->operator->makeHidden(['created_at', 'updated_at']);
            }
        });

        return response()->json($this->applyFilters($defibrillators));
    }

}