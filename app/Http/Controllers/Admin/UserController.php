<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $clientId = auth()->user()->client_id;
        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->where('client_id', $clientId)
            ->when($search !== '', fn ($q) => $q->where(function ($qb) use ($search) {
                $qb->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            }))
            ->with('roles')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->availableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'active'   => ['sometimes', 'boolean'],
            'roles'    => ['array'],
            'roles.*'  => ['integer'],
        ]);

        $roles = Role::whereIn('id', $validated['roles'] ?? [])
            ->where('client_id', $user->client_id)
            ->get();

        $new = User::create([
            'client_id' => $user->client_id,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'active'    => $request->boolean('active', true),
        ]);
        $new->syncRoles($roles);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "User {$new->name} created.");
    }

    public function edit(User $user): View
    {
        $this->authorizeOwnership($user);

        return view('admin.users.edit', [
            'user'         => $user->load('roles'),
            'roles'        => $this->availableRoles(),
            'assignedIds'  => $user->roles->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeOwnership($user);
        $actor = auth()->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'active'   => ['sometimes', 'boolean'],
            'roles'    => ['array'],
            'roles.*'  => ['integer'],
        ]);

        $data = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'phone'  => $validated['phone'] ?? null,
            'active' => $request->boolean('active'),
        ];
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }
        $user->update($data);

        // Prevent an admin from removing their own Client Admin role (lock-out safety)
        $roleIds = $validated['roles'] ?? [];
        if ($user->id === $actor->id) {
            $clientAdminId = Role::where('name', 'Client Admin')->value('id');
            if ($clientAdminId && ! in_array($clientAdminId, $roleIds)) {
                $roleIds[] = $clientAdminId;
            }
        }

        $roles = Role::whereIn('id', $roleIds)
            ->where(function ($q) use ($actor) {
                $q->where('client_id', $actor->client_id)
                  ->orWhere('name', 'Client Admin'); // built-in role can be assigned too
            })
            ->get();
        $user->syncRoles($roles);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeOwnership($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "User {$name} deleted.");
    }

    /**
     * Roles a Client Admin can assign: their client's custom roles + the
     * built-in Client Admin role. Never exposes Super Admin.
     */
    private function availableRoles()
    {
        $clientId = auth()->user()->client_id;
        return Role::query()
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhere('name', 'Client Admin');
            })
            ->where('name', '!=', 'Super Admin')
            ->orderBy('client_id')   // global first, then custom
            ->orderBy('name')
            ->get();
    }

    private function authorizeOwnership(User $user): void
    {
        abort_unless($user->client_id === auth()->user()->client_id, 403);
    }
}
