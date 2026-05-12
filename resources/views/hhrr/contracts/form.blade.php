@extends('layouts.app')

@section('title', $contract->exists ? 'Edit contract' : 'New contract')

@section('content')
    <a href="{{ route('hhrr.contracts.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to contracts
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $contract->exists ? 'Edit contract' : 'New contract' }}</h1>
    @include('hhrr._shared._flash')

    <form method="POST" action="{{ $contract->exists ? route('hhrr.contracts.update', $contract) : route('hhrr.contracts.store') }}">
        @csrf
        @if($contract->exists) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $contract->title) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type *</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($types as $k => $v)<option value="{{ $k }}" @selected(old('type', $contract->type) === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status *</label>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(old('status', $contract->status) === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Employee</label>
                <select name="employee_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">—</option>
                    @foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id', $contract->employee_id) == $e->id)>{{ $e->full_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Patient</label>
                <select name="patient_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">—</option>
                    @foreach($patients as $p)<option value="{{ $p->id }}" @selected(old('patient_id', $contract->patient_id) == $p->id)>{{ $p->full_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Start date</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($contract->start_date)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">End date</label>
                <input type="date" name="end_date" value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Amount</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', $contract->amount) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Terms</label>
                <textarea name="terms" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('terms', $contract->terms) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $contract->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hhrr.contracts.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="save" class="w-4 h-4"></i> {{ $contract->exists ? 'Save changes' : 'Create' }}
            </button>
        </div>
    </form>
@endsection
