@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $client?->name }}</p>
    </div>

    <h2 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-3">HHRR</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase">Patients</span>
                <i data-lucide="user-round" class="w-4 h-4 text-indigo-500"></i>
            </div>
            <div class="text-3xl font-bold text-slate-900">{{ $stats['patients_total'] }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $stats['patients_active'] }} active</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase">Employees</span>
                <i data-lucide="users-round" class="w-4 h-4 text-indigo-500"></i>
            </div>
            <div class="text-3xl font-bold text-slate-900">{{ $stats['employees_active'] }}</div>
            <div class="text-xs text-slate-500 mt-1">currently active</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase">Contracts</span>
                <i data-lucide="file-signature" class="w-4 h-4 text-indigo-500"></i>
            </div>
            <div class="text-3xl font-bold text-slate-900">{{ $stats['contracts_active'] }}</div>
            <div class="text-xs text-slate-500 mt-1">active</div>
        </div>
        <a href="{{ route('hhrr.patients.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl p-5 transition flex flex-col justify-between">
            <div class="text-xs font-semibold uppercase tracking-wider opacity-80">Quick action</div>
            <div class="text-sm font-semibold mt-2">Manage patients →</div>
        </a>
    </div>

    @if($expiringContracts->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-8">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                <h2 class="text-sm font-bold text-amber-900 uppercase tracking-widest">Contracts expiring within 30 days</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($expiringContracts as $c)
                    <a href="{{ route('hhrr.contracts.edit', $c) }}" class="bg-white rounded-lg p-3 border border-amber-200 hover:border-amber-400 transition flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800 text-sm">{{ $c->title }}</div>
                            <div class="text-xs text-slate-500">{{ $c->employee?->full_name ?? $c->patient?->full_name ?? '—' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-amber-700 text-xs font-bold">{{ $c->end_date->format('M j') }}</div>
                            <div class="text-[10px] text-slate-500">in {{ $c->days_to_expiry }}d</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($patientsByClinic->isNotEmpty())
        <h2 class="text-xs font-bold text-rose-500 uppercase tracking-widest mb-3">Patients per clinic</h2>
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-8">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left">
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Clinic</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">City</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Patients</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($patientsByClinic as $clinic)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-slate-800">
                                <a href="{{ route('hhrr.clinics.show', $clinic) }}" class="hover:text-indigo-600">{{ $clinic->name }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ $clinic->city ?: '—' }}@if($clinic->state), {{ $clinic->state }}@endif</td>
                            <td class="px-5 py-3 text-right font-bold text-indigo-600">{{ $clinic->patients_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($recentPatients->count())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Recently added patients</h3>
            </div>
            <table class="w-full">
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentPatients as $patient)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('hhrr.patients.show', $patient) }}" class="font-semibold text-slate-800 hover:text-indigo-600">{{ $patient->full_name }}</a>
                                @if($patient->mrn)<span class="text-xs font-mono text-slate-400 ml-2">{{ $patient->mrn }}</span>@endif
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-500 text-right">{{ $patient->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
