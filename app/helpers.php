<?php
use Illuminate\Support\Facades\Http;

if (!function_exists('isValidUuid')) {
    /**
     * Check if a given string is a valid UUID
     * 
     * @param   mixed  $uuid   The string to check
     * @return  boolean
     */
    function isValidUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid);
    }
}

if (!function_exists('reverse_photon')) {
    /**
     * Reverse lookup an address for coordinates using Photon API
     */
    function reverse_photon(float $lat, float $lon): array
    {
        $url = config('app.photon.url') . '/reverse?lat=' . urlencode($lat) . '&lon=' . urlencode($lon) . '&limit=1';
        $response = Http::get($url);
        $json = $response->json();
        return $json['features'][0]['properties'] ?? [];
    }
}