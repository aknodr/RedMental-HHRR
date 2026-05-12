@extends('layouts.app')

@section('title', $patient->full_name)

@section('content')
    <a href="{{ route('hhrr.patients.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to patients
    </a>
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $patient->full_name }}</h1>
            <p class="text-slate-500 text-sm mt-1">
                @if($patient->date_of_birth){{ $patient->date_of_birth->format('m/d/Y') }} · {{ $patient->age }} y/o @endif
                @if($patient->gender) · {{ $patient->gender }}@endif
                @if($patient->mrn) · MRN <span class="font-mono">{{ $patient->mrn }}</span>@endif
            </p>
        </div>
        @can('hhrr.patients.edit')
            <a href="{{ route('hhrr.patients.edit', $patient) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="md:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Contact &amp; address</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Phone</dt><dd>{{ $patient->phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Email</dt><dd>{{ $patient->email ?: '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500 uppercase">Address</dt><dd>{{ $patient->address ?: '—' }}{{ $patient->city ? ', ' . $patient->city : '' }}{{ $patient->state ? ', ' . $patient->state : '' }} {{ $patient->zip }}</dd></div>
                <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500 uppercase">Emergency contact</dt><dd>{{ $patient->emergency_contact_name ?: '—' }}{{ $patient->emergency_contact_phone ? ' · ' . $patient->emergency_contact_phone : '' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Preferred language</dt><dd>{{ $patient->preferred_language ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Intake date</dt><dd>{{ optional($patient->intake_date)->format('M j, Y') ?: '—' }}</dd></div>
                @if($patient->notes)
                    <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500 uppercase">Notes</dt><dd class="whitespace-pre-line">{{ $patient->notes }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Insurance</h3>
                @forelse($patient->insurances as $ins)
                    <div class="py-2 border-b border-slate-100 last:border-b-0">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm">{{ $ins->payer->name ?? '—' }}</span>
                            <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded">{{ ucfirst($ins->priority) }}</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            @if($ins->policy_number) Policy <span class="font-mono">{{ $ins->policy_number }}</span> @endif
                            @if($ins->group_number) · Grp <span class="font-mono">{{ $ins->group_number }}</span>@endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No insurance on file.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Care assignment</h3>
                <div class="text-sm">
                    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Provider</div>
                    <div class="text-slate-800 mb-3">{{ $patient->assignedProvider?->full_name ?: '— Unassigned —' }}</div>
                    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Clinics</div>
                    @forelse($patient->clinics as $clinic)
                        <div class="text-slate-800 text-sm">
                            <a href="{{ route('hhrr.clinics.show', $clinic) }}" class="hover:text-indigo-600">{{ $clinic->name }}</a>
                            @if($clinic->pivot->status !== 'active')
                                <span class="text-[10px] uppercase text-slate-400 ml-1">({{ $clinic->pivot->status }})</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Not enrolled in any clinic.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-2">Status</h3>
                @if($patient->active)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active</span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase">Inactive</span>
                @endif
            </div>
        </div>
    </div>
@endsection
