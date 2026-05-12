<?php
/**
 * Smoke test for the new HHRR additions: invoices CRUD + route planner.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/'));

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = App\Models\User::where('email', 'admin@demo-bh.local')->first();
if (! $user) { fwrite(STDERR, "Seed first: php artisan migrate:fresh --seed\n"); exit(1); }
Auth::login($user);

$pass = 0; $fail = 0;
function t(string $label, callable $fn): void {
    global $pass, $fail;
    try { $fn(); echo str_pad($label, 60, '.') . " OK\n"; $pass++; }
    catch (Throwable $e) { echo str_pad($label, 60, '.') . " FAIL\n  ↳ " . $e->getMessage() . "\n"; $fail++; }
}

$inv = new App\Http\Controllers\Hhrr\InvoiceController();
t('Invoices index renders',          fn () => $inv->index(new Request())->render());
t('Invoices create form renders',    fn () => $inv->create()->render());

$invoice = App\Models\Hhrr\Invoice::with('lines')->first();
if ($invoice) {
    t('Invoice show renders', fn () => $inv->show($invoice)->render());
    t('Invoice edit renders', fn () => $inv->edit($invoice)->render());
}

// Confirm seeded invoices have realistic totals
t('Invoices seed has lines + totals', function () {
    $totalLines = App\Models\Hhrr\InvoiceLine::count();
    $invs       = App\Models\Hhrr\Invoice::count();
    if ($invs < 5) throw new Exception("Expected ≥5 invoices, got {$invs}");
    if ($totalLines < 5) throw new Exception("Expected lines, got {$totalLines}");
    $any = App\Models\Hhrr\Invoice::has('lines')->first();
    if ((float) $any->total <= 0) throw new Exception('Invoice total is zero — recalculation failed.');
});

$rt = new App\Http\Controllers\Hhrr\RouteController();
t('Route planner default renders', fn () => $rt->index(new Request())->render());
t('Route planner with date today renders', fn () => $rt->index(new Request(['date' => now()->toDateString()]))->render());

t('Route planner picks coords for at least one stop', function () use ($rt) {
    $req  = new Request(['date' => now()->toDateString()]);
    $view = $rt->index($req);
    $data = $view->getData();
    $stops = $data['amPlan']['stops']->count() + $data['pmPlan']['stops']->count();
    if ($stops === 0) throw new Exception('No stops planned — check appointments + patient lat/lng seed');
});

t('Route planner totals are non-negative', function () use ($rt) {
    $view = $rt->index(new Request(['date' => now()->toDateString()]));
    $data = $view->getData();
    if ($data['totalMiles'] < 0)  throw new Exception('Negative total miles');
    if ($data['totalFuel']  < 0)  throw new Exception('Negative fuel cost');
    if ($data['totalStops'] !== ($data['amPlan']['stops']->count() + $data['pmPlan']['stops']->count())) {
        throw new Exception('Stop count mismatch');
    }
});

// Confirm seeded patients (intake_date set in seeder) all have lat/lng.
// Smoke-created patients without intake_date are excluded.
t('Seeded patients have lat/lng',  function () {
    $missing = App\Models\Hhrr\Patient::whereNotNull('intake_date')
        ->where(fn ($q) => $q->whereNull('latitude')->orWhereNull('longitude'))
        ->count();
    if ($missing > 0) throw new Exception("{$missing} seeded patients missing coordinates");
});

// OSRM service: verify it returns real road geometry (or graceful fallback).
t('OSRM service returns real road geometry', function () {
    $coords = [[25.7617, -80.1918], [25.8576, -80.2781], [25.6793, -80.3173], [25.7617, -80.1918]];
    $r = App\Services\OsrmRouteService::route($coords);
    if (! is_array($r) || ! isset($r['source'], $r['geometry'], $r['leg_miles'], $r['total_miles'])) {
        throw new Exception('OSRM service returned malformed payload');
    }
    if ($r['source'] === 'osrm' || $r['source'] === 'cache') {
        if (count($r['geometry']) < 10) throw new Exception('OSRM geometry suspiciously short: ' . count($r['geometry']));
        if ($r['total_miles'] <= 0) throw new Exception('OSRM total_miles non-positive');
    } elseif ($r['source'] === 'haversine-fallback') {
        // Acceptable fallback if OSRM is offline; the test still passes.
        echo "(OSRM unreachable, using haversine fallback) ";
    } else {
        throw new Exception('Unexpected OSRM source: ' . $r['source']);
    }
});

t('Route planner integrates OSRM geometry', function () {
    $u = App\Models\User::where('email', 'admin@demo-bh.local')->first();
    Illuminate\Support\Facades\Auth::login($u);
    $ctrl = new App\Http\Controllers\Hhrr\RouteController();
    $view = $ctrl->index(new Illuminate\Http\Request(['date' => date('Y-m-d')]));
    $data = $view->getData();
    foreach (['amPlan', 'pmPlan'] as $k) {
        $p = $data[$k];
        foreach (['geometry', 'source', 'duration_minutes'] as $key) {
            if (! array_key_exists($key, $p)) throw new Exception("{$k} missing key {$key}");
        }
        if ($p['stops']->count() > 0 && empty($p['geometry'])) {
            throw new Exception("{$k} has stops but no geometry");
        }
    }
});

echo "\nPASS: {$pass} · FAIL: {$fail}\n";
exit($fail > 0 ? 1 : 0);
