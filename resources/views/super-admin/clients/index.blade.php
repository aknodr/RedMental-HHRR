@extends('layouts.app')

@section('title', 'Clients')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Clients</h1>
            <p class="text-slate-500 text-sm mt-1">Organizations using the system.</p>
        </div>
        <a href="{{ route('super-admin.clients.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
            <i data-lucide="plus" class="w-4 h-4"></i> New client
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="{{ $search }}" placeholder="Name, legal name, tax ID, email…"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="active"   @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
        @if($search || $status)
            <a href="{{ route('super-admin.clients.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Legal name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Location</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Users</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clients as $client)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            <a href="{{ route('super-admin.clients.show', $client) }}" class="hover:text-indigo-600">{{ $client->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $client->legal_name ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ $client->city }}{{ $client->city && $client->state ? ', ' : '' }}{{ $client->state }}
                            {{ !$client->city && !$client->state ? '—' : '' }}
                        </td>
                        <td class="px-5 py-3 text-center text-sm font-semibold text-slate-700">{{ $client->users_count }}</td>
                        <td class="px-5 py-3">
                            @if($client->active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('super-admin.clients.show', $client) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('super-admin.clients.edit', $client) }}" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded transition" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="{{ route('super-admin.clients.destroy', $client) }}"
                                      data-confirm-delete="{{ $client->name }} — this removes all their users and records">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded transition" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-slate-400 text-sm">
                            <i data-lucide="building-2" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                            No clients yet. <a href="{{ route('super-admin.clients.create') }}" class="text-indigo-600 hover:underline">Create the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($clients->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $clients->links() }}</div>
        @endif
    </div>
@endsection
