@extends('layouts.app')

@section('title', 'Patients')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Patients</h1>
            <p class="text-slate-500 text-sm mt-1">Demographics and insurance records — shared with the Clinical module.</p>
        </div>
        @can('hhrr.patients.create')
            <a href="{{ route('hhrr.patients.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="user-plus" class="w-4 h-4"></i> New patient
            </a>
        @endcan
    </div>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex gap-3 items-end flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Name, MRN, email or phone…"
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
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Clinic</label>
            <select name="clinic_id" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Any</option>
                @foreach($clinics as $cl)
                    <option value="{{ $cl->id }}" @selected((string)$clinicId === (string)$cl->id)>{{ $cl->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Provider</label>
            <select name="provider_id" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Any</option>
                @foreach($providers as $p)
                    <option value="{{ $p->id }}" @selected((string)$providerId === (string)$p->id)>{{ $p->full_name }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">MRN</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">DOB / Age</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Provider</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Clinics</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Primary payer</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($patients as $patient)
                    @php($primary = $patient->insurances->firstWhere('priority', 'primary'))
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            <a href="{{ route('hhrr.patients.show', $patient) }}" class="hover:text-indigo-600">{{ $patient->full_name }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm font-mono text-slate-600">{{ $patient->mrn ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ optional($patient->date_of_birth)->format('m/d/Y') ?: '—' }}
                            @if($patient->age) <span class="text-slate-400">({{ $patient->age }})</span>@endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $patient->assignedProvider?->full_name ?: '—' }}</td>
                        <td class="px-5 py-3 text-xs text-slate-600">
                            @forelse($patient->clinics as $cl)
                                <span class="inline-block px-1.5 py-0.5 mr-1 bg-slate-100 rounded text-[10px]">{{ $cl->name }}</span>
                            @empty — @endforelse
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $primary?->payer?->name ?: '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $patient->active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $patient->active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $patient->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @include('hhrr._shared._action_buttons', [
                                'showRoute'   => route('hhrr.patients.show', $patient),
                                'editRoute'   => auth()->user()->can('hhrr.patients.edit')   ? route('hhrr.patients.edit', $patient)    : null,
                                'deleteRoute' => auth()->user()->can('hhrr.patients.delete') ? route('hhrr.patients.destroy', $patient) : null,
                                'deleteLabel' => $patient->full_name,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-16 text-center text-slate-400 text-sm">No patients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($patients->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $patients->links() }}</div>
        @endif
    </div>
@endsection
