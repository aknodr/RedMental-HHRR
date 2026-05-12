@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
    <a href="{{ route('hhrr.employees.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to employees
    </a>
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $employee->full_name }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $employee->position ?: '—' }} · {{ $employee->department?->name ?: 'No department' }}</p>
        </div>
        @can('hhrr.employees.edit')
            <a href="{{ route('hhrr.employees.edit', $employee) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="md:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Information</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Employee #</dt><dd class="font-mono">{{ $employee->employee_number ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Hire date</dt><dd>{{ optional($employee->hire_date)->format('M j, Y') ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Email</dt><dd>{{ $employee->email ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Phone</dt><dd>{{ $employee->phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Hourly rate</dt><dd>{{ $employee->hourly_rate ? '$' . number_format($employee->hourly_rate, 2) : '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-500 uppercase">Annual salary</dt><dd>{{ $employee->salary ? '$' . number_format($employee->salary, 2) : '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500 uppercase">Address</dt><dd>{{ $employee->address ?: '—' }}{{ $employee->city ? ', ' . $employee->city : '' }}{{ $employee->state ? ', ' . $employee->state : '' }} {{ $employee->zip }}</dd></div>
                <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500 uppercase">Emergency contact</dt><dd>{{ $employee->emergency_contact_name ?: '—' }}{{ $employee->emergency_contact_phone ? ' · ' . $employee->emergency_contact_phone : '' }}</dd></div>
                @if($employee->notes)
                    <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500 uppercase">Notes</dt><dd class="whitespace-pre-line">{{ $employee->notes }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-900 mb-2">Status</h3>
            @if($employee->active)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase">Inactive</span>
            @endif

            <h3 class="font-semibold text-slate-900 mt-5 mb-2">Contracts</h3>
            @if($employee->contracts->count())
                <ul class="text-sm space-y-1">
                    @foreach($employee->contracts as $c)
                        <li class="text-slate-700">{{ $c->title }} <span class="text-xs text-slate-400">({{ \App\Models\Hhrr\Contract::STATUSES[$c->status] ?? $c->status }})</span></li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-400">No contracts.</p>
            @endif
        </div>
    </div>
@endsection
