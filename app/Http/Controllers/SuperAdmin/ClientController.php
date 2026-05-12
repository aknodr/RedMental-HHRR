<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $clients = Client::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qb) use ($search) {
                    $qb->where('name', 'like', "%{$search}%")
                       ->orWhere('legal_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('tax_id', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active',   fn ($q) => $q->where('active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('active', false))
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.clients.index', compact('clients', 'search', 'status'));
    }

    public function create(): View
    {
        return view('super-admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $client = DB::transaction(function () use ($data) {
            $client = Client::create($data['client']);

            $admin = User::create([
                'client_id' => $client->id,
                'name'      => $data['admin']['name'],
                'email'     => $data['admin']['email'],
                'password'  => Hash::make($data['admin']['password']),
                'active'    => true,
            ]);
            $admin->syncRoles(['Client Admin']);

            return $client;
        });

        return redirect()
            ->route('super-admin.clients.show', $client)
            ->with('status', "Client {$client->name} created with its administrator.");
    }

    public function show(Client $client): View
    {
        $client->load(['users.roles']);
        return view('super-admin.clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('super-admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id'     => ['nullable', 'string', 'max:50'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'max:255'],
            'address'    => ['nullable', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:100'],
            'state'      => ['nullable', 'string', 'size:2'],
            'zip'        => ['nullable', 'string', 'max:10'],
            'notes'      => ['nullable', 'string'],
            'active'     => ['sometimes', 'boolean'],
        ]);
        $data['active'] = $request->boolean('active');

        $client->update($data);

        return redirect()
            ->route('super-admin.clients.show', $client)
            ->with('status', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $name = $client->name;
        $client->delete();

        return redirect()
            ->route('super-admin.clients.index')
            ->with('status', "Client {$name} deleted.");
    }

    /**
     * Validate both the client record and the initial admin user in one pass.
     */
    private function validated(Request $request): array
    {
        $rules = [
            'name'           => ['required', 'string', 'max:255'],
            'legal_name'     => ['nullable', 'string', 'max:255'],
            'tax_id'         => ['nullable', 'string', 'max:50'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'state'          => ['nullable', 'string', 'size:2'],
            'zip'            => ['nullable', 'string', 'max:10'],
            'notes'          => ['nullable', 'string'],
            'admin_name'     => ['required', 'string', 'max:255'],
            'admin_email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $v = $request->validate($rules);

        return [
            'client' => [
                'name'       => $v['name'],
                'legal_name' => $v['legal_name'] ?? null,
                'tax_id'     => $v['tax_id'] ?? null,
                'phone'      => $v['phone'] ?? null,
                'email'      => $v['email'] ?? null,
                'address'    => $v['address'] ?? null,
                'city'       => $v['city'] ?? null,
                'state'      => $v['state'] ?? null,
                'zip'        => $v['zip'] ?? null,
                'notes'      => $v['notes'] ?? null,
                'active'     => true,
            ],
            'admin' => [
                'name'     => $v['admin_name'],
                'email'    => $v['admin_email'],
                'password' => $v['admin_password'],
            ],
        ];
    }
}
