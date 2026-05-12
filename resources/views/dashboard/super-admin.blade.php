@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">System overview</h1>
        <p class="text-slate-500 text-sm mt-1">Manage clients, their administrators, and system-wide settings.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total clients</div>
            <div class="text-3xl font-bold text-slate-900 mt-2">{{ $totalClients }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active</div>
            <div class="text-3xl font-bold text-emerald-600 mt-2">{{ $activeClients }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Inactive</div>
            <div class="text-3xl font-bold text-slate-400 mt-2">{{ $totalClients - $activeClients }}</div>
        </div>
    </div>

    @if($recentClients->count())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Recently created clients</h3>
                <a href="{{ route('super-admin.clients.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            <table class="w-full">
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentClients as $client)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('super-admin.clients.show', $client) }}" class="font-semibold text-slate-800 hover:text-indigo-600">{{ $client->name }}</a>
                                @unless($client->active)<span class="ml-2 text-[10px] font-bold uppercase text-slate-400">Inactive</span>@endunless
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-500 text-right">{{ $client->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white rounded-xl p-8 border border-slate-200 text-center">
            <i data-lucide="building-2" class="w-10 h-10 mx-auto text-slate-400 mb-3"></i>
            <p class="text-sm font-bold text-slate-600">No clients yet</p>
            <a href="{{ route('super-admin.clients.create') }}" class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="plus" class="w-4 h-4"></i> Create first client
            </a>
        </div>
    @endif
@endsection
