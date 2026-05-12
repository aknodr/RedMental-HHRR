<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Appointment;
use App\Models\Hhrr\Clinic;
use App\Services\OsrmRouteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Daily provider-route planner.
 *
 * Given a date and a starting point (one of the client's clinics or a custom
 * lat/lng), pulls all scheduled appointments for that day, groups them into
 * morning (<12:00) and afternoon (≥12:00) shifts, runs a nearest-neighbor TSP
 * on each group (using haversine for ordering — fast), then asks OSRM for the
 * real road geometry and per-leg distances of the optimized order.
 *
 * If OSRM is unreachable the service falls back to haversine × 1.30 with
 * straight-line geometry so the page still renders.
 */
class RouteController extends Controller
{
    private const EARTH_RADIUS_MI = 3958.7613;

    public function index(Request $request): View
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : now()->toDateString();

        $clinics = Clinic::where('active', true)->orderBy('name')->get();

        $startingClinicId = $request->query('start_clinic_id');
        $startingClinic   = $startingClinicId ? $clinics->firstWhere('id', (int) $startingClinicId) : $clinics->first();

        // Allow custom start coords as override.
        $startLat = $request->query('start_lat') !== null && $request->query('start_lat') !== ''
            ? (float) $request->query('start_lat')
            : (float) ($startingClinic->latitude ?? 25.7617);  // default Miami
        $startLng = $request->query('start_lng') !== null && $request->query('start_lng') !== ''
            ? (float) $request->query('start_lng')
            : (float) ($startingClinic->longitude ?? -80.1918);

        $mpg            = max(1, (float) $request->query('mpg', 24));
        $fuelPrice      = max(0, (float) $request->query('fuel_price', 3.50));

        $appointments = Appointment::query()
            ->with(['patient', 'provider', 'clinic'])
            ->whereDate('scheduled_at', $date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->get();

        // Group AM / PM by hour-of-day and only keep ones with patient coords.
        [$am, $pm] = [collect(), collect()];
        $missingCoords = collect();
        foreach ($appointments as $appt) {
            $patient = $appt->patient;
            if (! $patient || $patient->latitude === null || $patient->longitude === null) {
                $missingCoords->push($appt);
                continue;
            }
            $hour = $appt->scheduled_at->hour;
            ($hour < 12 ? $am : $pm)->push($appt);
        }

        $amPlan = $this->planRoute($startLat, $startLng, $am, $mpg, $fuelPrice);
        $pmPlan = $this->planRoute($startLat, $startLng, $pm, $mpg, $fuelPrice);

        return view('hhrr.routes.index', [
            'date'           => $date,
            'clinics'        => $clinics,
            'startingClinic' => $startingClinic,
            'startLat'       => $startLat,
            'startLng'       => $startLng,
            'mpg'            => $mpg,
            'fuelPrice'      => $fuelPrice,
            'amPlan'         => $amPlan,
            'pmPlan'         => $pmPlan,
            'missingCoords'  => $missingCoords,
            'totalMiles'     => $amPlan['miles'] + $pmPlan['miles'],
            'totalFuel'      => $amPlan['fuel_cost'] + $pmPlan['fuel_cost'],
            'totalStops'     => $amPlan['stops']->count() + $pmPlan['stops']->count(),
        ]);
    }

    /**
     * Order the appointments by nearest-neighbor TSP (cheap haversine), then
     * fetch the real road geometry + per-leg distances from OSRM in one call.
     *
     * @return array{
     *     stops: \Illuminate\Support\Collection,
     *     miles: float,
     *     fuel_cost: float,
     *     leg_miles: array<int,float>,
     *     geometry: array<int, array{0: float, 1: float}>,
     *     duration_minutes: float,
     *     source: string,
     *     warning: ?string,
     * }
     */
    private function planRoute(float $startLat, float $startLng, $appointments, float $mpg, float $fuelPrice): array
    {
        if ($appointments->isEmpty()) {
            return [
                'stops'            => collect(),
                'miles'            => 0.0,
                'fuel_cost'        => 0.0,
                'leg_miles'        => [],
                'geometry'         => [],
                'duration_minutes' => 0.0,
                'source'           => 'empty',
                'warning'          => null,
            ];
        }

        // good enough — running OSRM N² times to optimize order is wasteful.
        $stops = collect();
        $remaining = $appointments->values()->all();
        [$curLat, $curLng] = [$startLat, $startLng];
        while (! empty($remaining)) {
            $bestIdx = 0; $bestDist = INF;
            foreach ($remaining as $i => $appt) {
                $d = $this->haversineMiles($curLat, $curLng, (float) $appt->patient->latitude, (float) $appt->patient->longitude);
                if ($d < $bestDist) { $bestDist = $d; $bestIdx = $i; }
            }
            $next = $remaining[$bestIdx];
            $stops->push($next);
            $curLat = (float) $next->patient->latitude;
            $curLng = (float) $next->patient->longitude;
            array_splice($remaining, $bestIdx, 1);
        }

        // real road route. Service falls back to haversine if OSRM is down.
        $waypoints = [[$startLat, $startLng]];
        foreach ($stops as $s) {
            $waypoints[] = [(float) $s->patient->latitude, (float) $s->patient->longitude];
        }
        $waypoints[] = [$startLat, $startLng];

        $route = OsrmRouteService::route($waypoints);

        $miles = (float) $route['total_miles'];
        $fuel  = round(($miles / $mpg) * $fuelPrice, 2);

        return [
            'stops'            => $stops,
            'miles'            => round($miles, 2),
            'fuel_cost'        => $fuel,
            'leg_miles'        => $route['leg_miles'],
            'geometry'         => $route['geometry'],
            'duration_minutes' => round($route['duration_seconds'] / 60),
            'source'           => $route['source'],
            'warning'          => $route['error'] ?? null,
        ];
    }

    private function haversineMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $rLat1 = deg2rad($lat1);
        $rLat2 = deg2rad($lat2);
        $dLat  = deg2rad($lat2 - $lat1);
        $dLng  = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos($rLat1) * cos($rLat2) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return self::EARTH_RADIUS_MI * $c;
    }
}
