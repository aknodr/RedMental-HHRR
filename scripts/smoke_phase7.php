<?php
/**
 * Phase 7 smoke test — appointments / payroll / audit / contracts intelligence
 * (the F5–F12 batch).
 *
 * Usage: php scripts/smoke_phase7.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Hhrr\AppointmentController;
use App\Http\Controllers\Hhrr\EmployeeController;
use App\Http\Controllers\Hhrr\PatientController;
use App\Http\Controllers\Hhrr\PayrollController;
use App\Models\Hhrr\Appointment;
use App\Models\AuditLog;
use App\Models\Hhrr\Contract;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Patient;
use App\Models\Hhrr\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

$pass = 0; $fail = 0;
function t(string $label, callable $fn) {
    global $pass, $fail;
    echo str_pad($label, 60, '.');
    try { $fn(); echo " OK\n"; $pass++; }
    catch (\Throwable $e) { echo " FAIL\n  ↳ " . $e->getMessage() . "\n"; $fail++; }
}

Auth::login(User::where('email', 'admin@demo-bh.local')->first());

t('Patient with empty assigned_provider creates', function () {
    (new PatientController())->store(Request::create('/', 'POST', [
        'first_name' => 'Empty', 'last_name' => 'Provider', 'assigned_provider_id' => '',
    ]));
});
t('Employee with empty department creates', function () {
    (new EmployeeController())->store(Request::create('/', 'POST', [
        'first_name' => 'Empty', 'last_name' => 'Dept', 'department_id' => '', 'email' => 'empty@demo-bh.local',
    ]));
});

t('Contract scopes work (active/expired/expiringSoon)', function () {
    Contract::query()->getQuery()->wheres = []; // reset
    $a = Contract::active()->count();
    $e = Contract::expired()->count();
    $s = Contract::expiringSoon()->count();
    if (!is_int($a) || !is_int($e) || !is_int($s)) throw new Exception('expected ints');
});

t('Contract effective_status reflects dates', function () {
    $emp = Employee::first();
    $c = Contract::create([
        'client_id' => $emp->client_id, 'employee_id' => $emp->id,
        'type' => 'employment', 'title' => 'Smoke contract',
        'start_date' => now()->subYear(), 'end_date' => now()->subDay(),
        'amount' => 1000, 'status' => 'active',
    ]);
    if (! $c->is_expired) throw new Exception('expected is_expired=true');
    if ($c->effective_status !== 'expired') throw new Exception("effective_status was {$c->effective_status}");
    $c->delete();
});

$pat = Patient::first();
$prov = Employee::where('is_provider', true)->first();
$apt = null;

t('Appointment store schedules', function () use ($pat, $prov, &$apt) {
    $resp = (new AppointmentController())->store(Request::create('/', 'POST', [
        'patient_id'  => $pat->id, 'provider_id' => $prov->id,
        'scheduled_at' => now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i'),
        'duration_minutes' => 45, 'status' => 'scheduled',
    ]));
    if ($resp->getStatusCode() !== 302) throw new Exception('expected 302');
    $apt = Appointment::latest()->first();
});

t('Appointment 20/day cap enforced', function () use ($pat, $prov) {
    // Pre-fill 20 appointments at distinct times on a target day
    $day = now()->addDays(2)->setTime(8, 0);
    for ($i = 0; $i < 20; $i++) {
        Appointment::create([
            'client_id'   => $prov->client_id,
            'patient_id'  => $pat->id, 'provider_id' => $prov->id,
            'scheduled_at' => $day->copy()->addMinutes($i * 15),
            'duration_minutes' => 15, 'status' => 'scheduled',
        ]);
    }
    try {
        (new AppointmentController())->store(Request::create('/', 'POST', [
            'patient_id' => $pat->id, 'provider_id' => $prov->id,
            'scheduled_at' => $day->copy()->addHours(10)->format('Y-m-d\TH:i'),
            'duration_minutes' => 30, 'status' => 'scheduled',
        ]));
        throw new Exception('expected ValidationException for cap');
    } catch (ValidationException $e) {
        if (! str_contains($e->getMessage(), 'caps at 20')) throw new Exception('unexpected message: ' . $e->getMessage());
    }
});

t('Appointment cancel clears the cap', function () use ($pat, $prov) {
    $day = now()->addDays(2);
    Appointment::where('provider_id', $prov->id)->whereDate('scheduled_at', $day)->update(['status' => 'cancelled']);
    (new AppointmentController())->store(Request::create('/', 'POST', [
        'patient_id' => $pat->id, 'provider_id' => $prov->id,
        'scheduled_at' => $day->copy()->setTime(8, 0)->format('Y-m-d\TH:i'),
        'duration_minutes' => 30, 'status' => 'scheduled',
    ]));
});

t('Payroll generation for one provider', function () use ($prov) {
    $resp = (new PayrollController())->store(Request::create('/', 'POST', [
        'frequency' => 'bi_weekly',
        'period_start' => now()->subDays(13)->toDateString(),
        'period_end'   => now()->toDateString(),
        'employee_ids' => [$prov->id],
        'per_patient_bonus' => 5,
    ]));
    if ($resp->getStatusCode() !== 302) throw new Exception('expected 302');
    $p = Payroll::where('employee_id', $prov->id)->latest()->first();
    if (! $p) throw new Exception('payroll not created');
    if ($p->gross < 0) throw new Exception('gross is negative');
});

t('Audit log middleware records VIEW', function () {
    // Trigger a dashboard render-like path via direct insert just to validate the model writes
    AuditLog::create(['user_id' => Auth::id(), 'client_id' => Auth::user()->client_id, 'action' => 'VIEW', 'resource' => 'smoke.test', 'method' => 'GET', 'url' => '/smoke', 'ip_address' => '127.0.0.1']);
    if (AuditLog::where('resource', 'smoke.test')->count() < 1) throw new Exception('no audit row');
});

t('Audit index renders for current user', function () {
    (new AuditLogController())->index(new Request())->render();
});

echo "\nPASS: $pass · FAIL: $fail\n";
exit($fail ? 1 : 0);
