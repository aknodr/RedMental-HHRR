<?php
/**
 * Smoke-test the Super Admin client CRUD end to end.
 * Usage: php scripts/smoke.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());

use App\Http\Controllers\SuperAdmin\ClientController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$pass = 0; $fail = 0;
function t(string $label, callable $fn) {
    global $pass, $fail;
    echo str_pad($label, 55, '.');
    try { $fn(); echo " OK\n"; $pass++; }
    catch (\Throwable $e) { echo " FAIL\n  ↳ " . $e->getMessage() . "\n"; $fail++; }
}

$super = User::where('email', 'superadmin@tesis.local')->first();
Auth::login($super);
$c = new ClientController();

t('Clients list renders', function () use ($c) {
    $r = new Request();
    $c->index($r)->render();
});

t('Create form renders', function () use ($c) {
    $c->create()->render();
});

t('Store creates client + admin atomically', function () use ($c) {
    $req = Request::create('/', 'POST', [
        'name' => 'Smoke Test Clinic',
        'legal_name' => 'Smoke Test LLC',
        'phone' => '305-555-1111',
        'email' => 'info@smoke.local',
        'admin_name' => 'Smoke Admin',
        'admin_email' => 'smoke+' . rand(1000, 9999) . '@test.local',
        'admin_password' => 'password123',
        'admin_password_confirmation' => 'password123',
    ]);
    $response = $c->store($req);
    if ($response->getStatusCode() !== 302) throw new Exception('expected redirect');
    $client = \App\Models\Client::latest()->first();
    if ($client->name !== 'Smoke Test Clinic') throw new Exception('client name not saved');
    $admin = $client->users->first();
    if (!$admin || !$admin->hasRole('Client Admin')) throw new Exception('admin user or role missing');
});

$clinic = \App\Models\Client::where('name', 'Smoke Test Clinic')->first();

t('Show renders client details', function () use ($c, $clinic) {
    $c->show($clinic)->render();
});

t('Edit form renders', function () use ($c, $clinic) {
    $c->edit($clinic)->render();
});

t('Update saves new fields', function () use ($c, $clinic) {
    $req = Request::create('/', 'PUT', [
        'name' => 'Smoke Test Clinic (edited)',
        'phone' => '305-555-9999',
        'active' => '1',
    ]);
    $c->update($req, $clinic);
    $clinic->refresh();
    if ($clinic->name !== 'Smoke Test Clinic (edited)') throw new Exception('update not saved');
});

t('Destroy removes client', function () use ($c, $clinic) {
    $c->destroy($clinic);
    if (\App\Models\Client::find($clinic->id)) throw new Exception('client still exists');
});

echo "\nPASS: $pass · FAIL: $fail\n";
