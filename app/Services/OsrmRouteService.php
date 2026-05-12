<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Real-road routing via OSRM (Open Source Routing Machine).
 *
 * Uses the public demo server at router.project-osrm.org by default — free,
 * no API key, but rate-limited. Override with OSRM_BASE_URL in .env if you
 * run a self-hosted OSRM instance.
 *
 * Returned geometry is the LineString of [lat, lng] points along the road,
 * ready to drop into Leaflet's polyline. Per-leg distances are in **miles**
 * to match the rest of the route planner.
 */
class OsrmRouteService
{
    private const METERS_PER_MILE = 1609.344;
    private const CACHE_TTL_SECONDS = 86400; // 24h — OSM road data changes slowly

    /**
     * Route through the given ordered points (start → stops in order → start).
     *
     * @param  array<int, array{0: float, 1: float}>  $coords  ordered [lat, lng] pairs (≥ 2)
     * @return array{
     *     ok: bool,
     *     source: string,
     *     geometry: array<int, array{0: float, 1: float}>,
     *     leg_miles: array<int, float>,
     *     total_miles: float,
     *     duration_seconds: float,
     *     error?: string,
     * }
     */
    public static function route(array $coords): array
    {
        if (count($coords) < 2) {
            return [
                'ok'              => true,
                'source'          => 'empty',
                'geometry'        => $coords,
                'leg_miles'       => [],
                'total_miles'     => 0.0,
                'duration_seconds'=> 0.0,
            ];
        }

        $base = rtrim(config('services.osrm.base_url') ?: env('OSRM_BASE_URL', 'https://router.project-osrm.org'), '/');
        $waypoints = collect($coords)
            ->map(fn ($p) => sprintf('%.6F,%.6F', (float) $p[1], (float) $p[0])) // OSRM expects lng,lat
            ->implode(';');
        $url = "{$base}/route/v1/driving/{$waypoints}?overview=full&geometries=geojson&steps=false";

        $cacheKey = 'osrm:' . md5($url);

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) return $cached + ['source' => 'cache'];

        try {
            $response = Http::timeout(8)->get($url);
            if (! $response->successful()) {
                return self::failure("OSRM HTTP {$response->status()}", $coords);
            }
            $data = $response->json();
            if (($data['code'] ?? null) !== 'Ok' || empty($data['routes'][0])) {
                return self::failure('OSRM rejected the request: ' . ($data['message'] ?? $data['code'] ?? 'unknown'), $coords);
            }

            $route = $data['routes'][0];

            // GeoJSON LineString: array of [lng, lat] — flip for Leaflet's [lat, lng].
            $geometry = collect($route['geometry']['coordinates'] ?? [])
                ->map(fn ($pair) => [(float) $pair[1], (float) $pair[0]])
                ->all();

            $legMiles = collect($route['legs'] ?? [])
                ->map(fn ($leg) => round(((float) ($leg['distance'] ?? 0)) / self::METERS_PER_MILE, 3))
                ->all();

            $result = [
                'ok'               => true,
                'source'           => 'osrm',
                'geometry'         => $geometry,
                'leg_miles'        => $legMiles,
                'total_miles'      => round(((float) ($route['distance'] ?? 0)) / self::METERS_PER_MILE, 3),
                'duration_seconds' => (float) ($route['duration'] ?? 0),
            ];

            Cache::put($cacheKey, $result, self::CACHE_TTL_SECONDS);
            return $result;
        } catch (\Throwable $e) {
            Log::warning('OSRM route failed: ' . $e->getMessage());
            return self::failure('OSRM unreachable: ' . $e->getMessage(), $coords);
        }
    }

    /**
     * Fallback when OSRM is down — returns straight-line geometry + haversine
     * distances × 1.30 (the same road-factor we used before adding OSRM).
     */
    private static function failure(string $error, array $coords): array
    {
        $legMiles = [];
        $total = 0.0;
        for ($i = 0; $i < count($coords) - 1; $i++) {
            $d = self::haversineMiles((float) $coords[$i][0], (float) $coords[$i][1], (float) $coords[$i + 1][0], (float) $coords[$i + 1][1]) * 1.30;
            $legMiles[] = round($d, 3);
            $total += $d;
        }
        return [
            'ok'               => false,
            'source'           => 'haversine-fallback',
            'geometry'         => $coords,
            'leg_miles'        => $legMiles,
            'total_miles'      => round($total, 3),
            'duration_seconds' => 0.0,
            'error'            => $error,
        ];
    }

    private static function haversineMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 3958.7613;
        $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lng2 - $lng1) / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
