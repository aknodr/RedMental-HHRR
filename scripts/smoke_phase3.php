<?php
/**
 * Phase 3 smoke test — Role matrix + User CRUD within a client.
 * Usage: php scripts/smoke_phase3.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$pass = 0; $fail = 0;
function t(string $label, callable $fn) {
    global $pass, $fail;
    echo str_pad($label, 55, '.');
    try { $fn(); echo " OK\n"; $pass++; }
    catch (\Throwable $e) { echo " FAIL\n  ↳ " . $e->getMessage() . " at " . basename($e->getFile()) . ':' . $e->getLine() . "\n"; $fail++; }
}

$admin = User::where('email', 'admin@demo-bh.local')->first();
Auth::login($admin);

$rc = new RoleController();
$uc = new UserController();

$smokeRoleName = 'SmokeRole_' . uniqid();

t('Roles index renders', function () use ($rc) { $rc->index()->render(); });
t('New-role form renders', function () use ($rc) { $rc->create()->render(); });

t('Store creates a client-scoped role', function () use ($rc, $admin, $smokeRoleName) {
    $req = Request::create('/', 'POST', [
        'name' => $smokeRoleName,
        'permissions' => ['hhrr.patients.view', 'hhrr.patients.create', 'hhrr.payers.view'],
    ]);
    $rc->store($req);
    $role = Role::where('client_id', $admin->client_id)->where('name', $smokeRoleName)->first();
    if (!$role) throw new Exception('Role not created');
    if ($role->permissions->count() !== 3) throw new Exception('Permissions not attached');
});

$role = Role::where('client_id', $admin->client_id)->where('name', $smokeRoleName)->first();

t('Edit form renders', function () use ($rc, $role) { $rc->edit($role)->render(); });

t('Update changes permissions', function () use ($rc, $role, $smokeRoleName) {
    $req = Request::create('/', 'PUT', [
        'name' => $smokeRoleName,
        'permissions' => ['hhrr.patients.view', 'hhrr.patients.edit', 'hhrr.payers.view', 'hhrr.payers.create'],
    ]);
    $rc->update($req, $role);
    $role->refresh()->load('permissions');
    if ($role->permissions->count() !== 4) throw new Exception('Expected 4 permissions, got ' . $role->permissions->count());
});

t('Matrix renders for the client', function () use ($rc) { $rc->matrix()->render(); });

t('Matrix bulk save works', function () use ($rc, $role) {
    $req = Request::create('/', 'POST', [
        'matrix' => [
            $role->id => [
                'hhrr.patients.view' => '1',
                'clinical.psr.view' => '1',
            ],
        ],
    ]);
    $rc->saveMatrix($req);
    $role->refresh()->load('permissions');
    $names = $role->permissions->pluck('name')->sort()->values()->all();
    if ($names !== ['clinical.psr.view', 'hhrr.patients.view']) throw new Exception('Matrix did not sync: ' . implode(',', $names));
});

t('Users index renders', function () use ($uc) { $uc->index(Request::create('/')); $uc->index(Request::create('/', 'GET', ['q' => 'admin'])); });
t('New-user form renders', function () use ($uc) { $uc->create()->render(); });

$newEmail = 'tester+' . rand(1000, 9999) . '@demo-bh.local';

t('Create new user with role', function () use ($uc, $newEmail, $admin, $role) {
    $req = Request::create('/', 'POST', [
        'name' => 'Test User',
        'email' => $newEmail,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '(305) 000-0000',
        'active' => '1',
        'roles' => [$role->id],
    ]);
    $uc->store($req);
    $new = User::where('email', $newEmail)->first();
    if (!$new) throw new Exception('User not created');
    if ($new->client_id !== $admin->client_id) throw new Exception('User not scoped to admin client');
    if (!$new->hasRole($role->name)) throw new Exception('Role not attached');
});

$newUser = User::where('email', $newEmail)->first();

t('Edit form renders', function () use ($uc, $newUser) { $uc->edit($newUser)->render(); });

t('Update user roles + info', function () use ($uc, $newUser) {
    $req = Request::create('/', 'PUT', [
        'name' => 'Test User Updated',
        'email' => $newUser->email,
        'phone' => '(305) 999-9999',
        'active' => '1',
        'roles' => [],
    ]);
    $uc->update($req, $newUser);
    $newUser->refresh();
    if ($newUser->name !== 'Test User Updated') throw new Exception('Name not updated');
    if ($newUser->roles->count() !== 0) throw new Exception('Roles not cleared');
});

t('Destroy user', function () use ($uc, $newUser) {
    $uc->destroy($newUser);
    if (User::find($newUser->id)) throw new Exception('User still exists');
});

t('Destroy role (no users)', function () use ($rc, $role) {
    $rc->destroy($role);
    if (Role::find($role->id)) throw new Exception('Role still exists');
});

echo "\nPASS: $pass · FAIL: $fail\n";
