@extends('layouts.app')

@section('title', $clinic->name)

@section('content')
    <a href="{{ route('hhrr.clinics.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to clinics
    </a>

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $clinic->name }}</h1>
            <p class="text-slate-500 text-sm mt-1">
                @if($clinic->code) <span class="font-mono text-xs">{{ $clinic->code }}</span> · @endif
                {{ $clinic->city }}@if($clinic->state), {{ $clinic->state }}@endif
            </p>
        </div>
        @can('hhrr.clinics.edit')
            <a href="{{ route('hhrr.clinics.edit', $clinic) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg inline-flex items-center gap-2">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Address</div>
            <div class="text-sm text-slate-800">{{ $clinic->address ?: '—' }}</div>
            @if($clinic->zip)<div class="text-xs text-slate-500 mt-1">ZIP {{ $clinic->zip }}</div>@endif
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Contact</div>
            <div class="text-sm text-slate-800">{{ $clinic->phone ?: '—' }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $clinic->email ?: '—' }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Patients</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $clinic->patients->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">enrolled at this site</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Enrolled patients</h3>
        </div>
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">MRN</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Enrolled</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clinic->patients as $patient)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('hhrr.patients.show', $patient) }}" class="font-semibold text-slate-800 hover:text-indigo-600">{{ $patient->full_name }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm font-mono text-slate-600">{{ $patient->mrn ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ optional($patient->pivot->enrollment_date)->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs uppercase text-slate-600">{{ $patient->pivot->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-12 text-center text-slate-400 text-sm">No patients enrolled yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
