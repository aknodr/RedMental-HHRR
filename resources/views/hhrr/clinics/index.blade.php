@extends('layouts.app')

@section('title', 'Clinics')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Clinics</h1>
            <p class="text-slate-500 text-sm mt-1">Physical sites of the organization. Patients are enrolled per clinic.</p>
        </div>
        @can('hhrr.clinics.create')
            <a href="{{ route('hhrr.clinics.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="plus" class="w-4 h-4"></i> New clinic
            </a>
        @endcan
    </div>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Name, code or city…"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
        @if($q)<a href="{{ route('hhrr.clinics.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>@endif
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Code</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">City / State</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-center">Patients</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clinics as $clinic)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            <a href="{{ route('hhrr.clinics.show', $clinic) }}" class="hover:text-indigo-600">{{ $clinic->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $clinic->code ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ $clinic->city ?: '—' }}@if($clinic->state), {{ $clinic->state }}@endif
                        </td>
                        <td class="px-5 py-3 text-center text-sm font-semibold text-slate-700">{{ $clinic->patients_count }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $clinic->active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $clinic->active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $clinic->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @include('hhrr._shared._action_buttons', [
                                'showRoute'   => route('hhrr.clinics.show', $clinic),
                                'editRoute'   => auth()->user()->can('hhrr.clinics.edit')   ? route('hhrr.clinics.edit', $clinic)    : null,
                                'deleteRoute' => auth()->user()->can('hhrr.clinics.delete') ? route('hhrr.clinics.destroy', $clinic) : null,
                                'deleteLabel' => $clinic->name,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400 text-sm">No clinics yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($clinics->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $clinics->links() }}</div>
        @endif
    </div>
@endsection
