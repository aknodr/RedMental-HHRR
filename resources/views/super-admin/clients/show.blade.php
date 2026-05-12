@extends('layouts.app')

@section('title', $client->name)

@section('content')
    <a href="{{ route('super-admin.clients.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to clients
    </a>

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                {{ $client->name }}
                @if($client->active)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase">Inactive</span>
                @endif
            </h1>
            @if($client->legal_name && $client->legal_name !== $client->name)
                <p class="text-slate-500 text-sm mt-1">{{ $client->legal_name }}</p>
            @endif
        </div>
        <a href="{{ route('super-admin.clients.edit', $client) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
            <i data-lucide="pencil" class="w-4 h-4"></i> Edit
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Client info --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Information</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tax ID</dt>
                    <dd class="text-slate-800">{{ $client->tax_id ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Phone</dt>
                    <dd class="text-slate-800">{{ $client->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Email</dt>
                    <dd class="text-slate-800">{{ $client->email ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Created</dt>
                    <dd class="text-slate-800">{{ $client->created_at?->format('M j, Y') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Address</dt>
                    <dd class="text-slate-800">
                        {{ $client->address ?: '—' }}
                        @if($client->city || $client->state || $client->zip)
                            <br>{{ $client->city }}{{ $client->city && $client->state ? ', ' : '' }}{{ $client->state }} {{ $client->zip }}
                        @endif
                    </dd>
                </div>
                @if($client->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Notes</dt>
                        <dd class="text-slate-800 whitespace-pre-line">{{ $client->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- User count --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Users</h3>
            <div class="text-4xl font-bold text-indigo-600">{{ $client->users->count() }}</div>
            <p class="text-xs text-slate-500 mt-1">Registered on this client</p>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-semibold">Administrator</p>
                @php($admin = $client->users->first(fn($u) => $u->hasRole('Client Admin')))
                @if($admin)
                    <div class="text-sm font-semibold text-slate-800">{{ $admin->name }}</div>
                    <div class="text-xs text-slate-500">{{ $admin->email }}</div>
                @else
                    <div class="text-xs text-rose-600">No administrator assigned yet</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Users list --}}
    <div class="bg-white rounded-xl border border-slate-200 mt-5 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Users of this client</h3>
            <span class="text-xs text-slate-500">{{ $client->users->count() }} {{ Str::plural('user', $client->users->count()) }}</span>
        </div>
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-2 text-[10px] font-bold text-slate-500 uppercase">Name</th>
                    <th class="px-5 py-2 text-[10px] font-bold text-slate-500 uppercase">Email</th>
                    <th class="px-5 py-2 text-[10px] font-bold text-slate-500 uppercase">Roles</th>
                    <th class="px-5 py-2 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($client->users as $user)
                    <tr>
                        <td class="px-5 py-2.5 font-semibold text-slate-800 text-sm">{{ $user->name }}</td>
                        <td class="px-5 py-2.5 text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-5 py-2.5 text-sm">
                            @foreach($user->roles as $role)
                                <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-5 py-2.5 text-sm">
                            @if($user->active)
                                <span class="text-emerald-600 text-xs font-semibold">Active</span>
                            @else
                                <span class="text-slate-400 text-xs font-semibold">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">No users.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
