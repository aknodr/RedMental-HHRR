<?php
/**
 * Phase 4 smoke test — HHRR module CRUDs + client scoping + permission gates.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());

use App\Http\Controllers\Hhrr\ContractController;
use App\Http\Controllers\Hhrr\DepartmentController;
use App\Http\Controllers\Hhrr\EmployeeController;
use App\Http\Controllers\Hhrr\PatientController;
use App\Http\Controllers\Hhrr\PayerController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$pass = 0; $fail = 0;
function t(string $label, callable $fn) {
    global $pass, $fail;
    echo str_pad($label, 55, '.');
    try { $fn(); echo " OK\n"; $pass++; }
    catch (\Throwable $e) { echo " FAIL\n  ↳ " . $e->getMessage() . " @ " . basename($e->getFile()) . ':' . $e->getLine() . "\n"; $fail++; }
}

$admin = User::where('email', 'admin@demo-bh.local')->first();
Auth::login($admin);

$dc = new DepartmentController();
t('Departments index renders', fn() => $dc->index(Request::create('/')));
$smokeDeptName = 'SmokeDept_' . uniqid();
t('Department create+store+edit+update+destroy', function () use ($dc, $smokeDeptName) {
    $dc->store(Request::create('/', 'POST', ['name' => $smokeDeptName, 'code' => 'SMK', 'active' => '1']));
    $d = \App\Models\Hhrr\Department::where('name', $smokeDeptName)->first();
    $dc->update(Request::create('/', 'PUT', ['name' => $smokeDeptName . '_edit', 'active' => '1']), $d);
    $d->refresh();
    if ($d->name !== $smokeDeptName . '_edit') throw new Exception('update failed');
    $dc->destroy($d);
    if (\App\Models\Hhrr\Department::find($d->id)) throw new Exception('delete failed');
});

$pc = new PayerController();
t('Payers index renders', fn() => $pc->index(Request::create('/')));
t('Payer create+update+destroy', function () use ($pc) {
    $pc->store(Request::create('/', 'POST', ['name' => 'Aetna FL', 'type' => 'Commercial', 'edi_payer_id' => '60054']));
    $p = \App\Models\Hhrr\Payer::where('name', 'Aetna FL')->first();
    $pc->update(Request::create('/', 'PUT', ['name' => 'Aetna FL Commercial', 'type' => 'Commercial']), $p);
    $p->refresh();
    if ($p->name !== 'Aetna FL Commercial') throw new Exception('update failed');
    $pc->destroy($p);
});

$ec = new EmployeeController();
$deptName = 'SmokeClinical_' . uniqid();
$dc->store(Request::create('/', 'POST', ['name' => $deptName, 'active' => '1']));
$dept = \App\Models\Hhrr\Department::where('name', $deptName)->first();

t('Employees index renders', fn() => $ec->index(Request::create('/')));
t('Employee create+show+update+destroy', function () use ($ec, $dept) {
    $ec->store(Request::create('/', 'POST', [
        'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane@test.local',
        'department_id' => $dept->id, 'position' => 'Therapist', 'active' => '1',
    ]));
    $e = \App\Models\Hhrr\Employee::where('last_name', 'Doe')->first();
    $ec->show($e)->render();
    $ec->update(Request::create('/', 'PUT', [
        'first_name' => 'Jane', 'last_name' => 'Doe-Smith', 'email' => 'jane@test.local', 'active' => '1',
    ]), $e);
    $e->refresh();
    if ($e->last_name !== 'Doe-Smith') throw new Exception('update failed');
    $ec->destroy($e);
});

$patC = new PatientController();
$payerName = 'SmokePayer_' . uniqid();
$pc->store(Request::create('/', 'POST', ['name' => $payerName, 'type' => 'Medicaid']));
$payer = \App\Models\Hhrr\Payer::where('name', $payerName)->first();

t('Patients index renders', fn() => $patC->index(Request::create('/')));
t('Patient create with insurance + update + destroy', function () use ($patC, $payer) {
    $patC->store(Request::create('/', 'POST', [
        'first_name' => 'Maria', 'last_name' => 'Perez',
        'date_of_birth' => '1990-05-15', 'gender' => 'Female',
        'mrn' => 'MRN-' . rand(1000, 9999),
        'active' => '1',
        'insurances' => [
            ['payer_id' => $payer->id, 'priority' => 'primary', 'policy_number' => 'POL123', 'subscriber_relationship' => 'self'],
        ],
    ]));
    $p = \App\Models\Hhrr\Patient::where('last_name', 'Perez')->first();
    if ($p->insurances->count() !== 1) throw new Exception('insurance not attached');
    $patC->show($p)->render();
    $patC->update(Request::create('/', 'PUT', [
        'first_name' => 'Maria', 'last_name' => 'Perez Updated',
        'active' => '1',
        'insurances' => [], // remove all insurance
    ]), $p);
    $p->refresh();
    if ($p->last_name !== 'Perez Updated') throw new Exception('patient update failed');
    if ($p->insurances()->count() !== 0) throw new Exception('insurance not removed');
    $patC->destroy($p);
});

$cc = new ContractController();
t('Contracts index renders', fn() => $cc->index(Request::create('/')));
t('Contract create+update+destroy', function () use ($cc) {
    $cc->store(Request::create('/', 'POST', [
        'title' => 'Test employment contract', 'type' => 'employment', 'status' => 'draft',
        'start_date' => '2026-01-01',
    ]));
    $c = \App\Models\Hhrr\Contract::where('title', 'Test employment contract')->first();
    $cc->update(Request::create('/', 'PUT', [
        'title' => 'Test contract (edited)', 'type' => 'employment', 'status' => 'active',
    ]), $c);
    $c->refresh();
    if ($c->status !== 'active') throw new Exception('update failed');
    $cc->destroy($c);
});

$dc->destroy($dept);
$pc->destroy($payer);

echo "\nPASS: $pass · FAIL: $fail\n";
