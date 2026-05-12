@extends('layouts.app')

@section('title', 'Generate payroll')

@section('content')
    <a href="{{ route('hhrr.payroll.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Generate payroll</h1>

    <form method="POST" action="{{ route('hhrr.payroll.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5 max-w-3xl"
          x-data="{ frequency: '{{ old('frequency', 'bi_weekly') }}', start: '{{ old('period_start', $defaults['period_start']) }}', end: '{{ old('period_end', $defaults['period_end']) }}' }">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Frequency *</label>
                <select name="frequency" x-model="frequency" required
                        @change="
                            if (frequency === 'monthly') { start = new Date(new Date().setDate(1)).toISOString().slice(0,10); end = new Date(new Date().getFullYear(), new Date().getMonth()+1, 0).toISOString().slice(0,10); }
                            else { const today = new Date(); end = today.toISOString().slice(0,10); start = new Date(today.setDate(today.getDate()-13)).toISOString().slice(0,10); }
                        "
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($frequencies as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Period start *</label>
                <input type="date" name="period_start" x-model="start" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Period end *</label>
                <input type="date" name="period_end" x-model="end" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bonus per patient seen (optional)</label>
                <input type="number" name="per_patient_bonus" step="0.01" min="0" value="{{ old('per_patient_bonus', 0) }}"
                       class="w-full md:w-40 px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-[10px] text-slate-400 mt-1">Patient count = number of (scheduled or completed) appointments where the employee is the provider during the period.</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-2">Include employees *</label>
            <div class="border border-slate-300 rounded-lg p-3 max-h-72 overflow-y-auto space-y-2 bg-white">
                <label class="flex items-center gap-2 text-xs text-slate-600 mb-2 pb-2 border-b border-slate-100">
                    <input type="checkbox" onchange="document.querySelectorAll('input[name=&quot;employee_ids[]&quot;]').forEach(c => c.checked = this.checked)"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="font-bold uppercase tracking-wider">Select all</span>
                </label>
                @foreach($employees as $emp)
                    <label class="flex items-center justify-between gap-2 text-sm text-slate-700 hover:bg-slate-50 px-2 py-1 rounded">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $emp->full_name }}</span>
                            @if($emp->position)<span class="text-xs text-slate-400">— {{ $emp->position }}</span>@endif
                        </div>
                        <span class="text-xs font-mono text-slate-500">${{ number_format((float)($emp->hourly_rate ?? 0), 2) }}/hr</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <a href="{{ route('hhrr.payroll.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Cancel</a>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">Generate payroll</button>
        </div>
    </form>
@endsection
