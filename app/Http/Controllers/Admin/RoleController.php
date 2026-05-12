<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(): View
    {
        $clientId = auth()->user()->client_id;

        $roles = Role::forClient($clientId)
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', [
            'roles'    => $roles,
            'catalog'  => PermissionsSeeder::CATALOG,
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'catalog' => PermissionsSeeder::CATALOG,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('client_id', $user->client_id)->where('guard_name', 'web')),
            ],
            'description'   => ['nullable', 'string'],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in($this->validPermissionNames())],
        ], [
            'name.unique' => 'Another role in this organization already uses that name.',
        ]);

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => 'web',
            'client_id'  => $user->client_id,
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', "Role “{$role->name}” created.");
    }

    public function edit(Role $role): View
    {
        $this->authorizeOwnership($role);

        return view('admin.roles.edit', [
            'role'        => $role->load('permissions'),
            'catalog'     => PermissionsSeeder::CATALOG,
            'selectedIds' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeOwnership($role);

        $user = auth()->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100',
                Rule::unique('roles', 'name')->ignore($role->id)->where(fn ($q) => $q->where('client_id', $user->client_id)->where('guard_name', 'web')),
            ],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in($this->validPermissionNames())],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizeOwnership($role);

        if ($role->users()->exists()) {
            return back()->with('error', 'This role is still assigned to users. Reassign them first.');
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('status', "Role “{$name}” deleted.");
    }

    /**
     * Flat matrix view: all roles × all permissions in a single page with
     * checkboxes. Saving updates them in bulk.
     */
    public function matrix(): View
    {
        $clientId = auth()->user()->client_id;
        $roles = Role::forClient($clientId)->with('permissions')->orderBy('name')->get();

        return view('admin.roles.matrix', [
            'roles'   => $roles,
            'catalog' => PermissionsSeeder::CATALOG,
        ]);
    }

    public function saveMatrix(Request $request): RedirectResponse
    {
        $clientId = auth()->user()->client_id;
        $roles = Role::forClient($clientId)->pluck('id')->all();
        $validPerms = $this->validPermissionNames();

        $matrix = $request->input('matrix', []); // [role_id => [permission_name => '1']]

        foreach ($roles as $roleId) {
            $role = Role::find($roleId);
            if (! $role) continue;

            $requested = array_keys($matrix[$roleId] ?? []);
            $permissions = array_values(array_intersect($requested, $validPerms));
            $role->syncPermissions($permissions);
        }

        return redirect()
            ->route('admin.roles.matrix')
            ->with('status', 'Permissions matrix saved.');
    }

    private function validPermissionNames(): array
    {
        $names = [];
        foreach (PermissionsSeeder::CATALOG as $group) {
            foreach ($group as $name => $_desc) $names[] = $name;
        }
        return $names;
    }

    private function authorizeOwnership(Role $role): void
    {
        abort_unless($role->client_id === auth()->user()->client_id, 403);
    }
}
