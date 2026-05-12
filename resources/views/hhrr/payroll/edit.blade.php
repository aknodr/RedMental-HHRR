@extends('layouts.app')

@section('title', 'Edit payroll')

@section('content')
    <a href="{{ route('hhrr.payroll.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
    </a>

    <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ $payroll->employee?->full_name ?? '—' }}</h1>
    <p class="text-slate-500 text-sm mb-6">
        {{ $payroll->period_start->format('M j, Y') }} – {{ $payroll->period_end->format('M j, Y') }} ·
        <span class="uppercase">{{ \App\Models\Hhrr\Payroll::FREQUENCIES[$payroll->frequency] ?? $payroll->frequency }}</span>
    </p>

    <form method="POST" action="{{ route('hhrr.payroll.update', $payroll) }}"
          class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 max-w-3xl"
          x-data="{
              hours: {{ (float) $payroll->hours_worked }},
              rate:  {{ (float) $payroll->hourly_rate }},
              bonus: {{ (float) $payroll->per_patient_bonus }},
              patients: {{ (int)   $payroll->patients_seen }},
              deductions: {{ (float) $payroll->deductions }},
          }">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hours worked *</label>
                <input type="number" name="hours_worked" step="0.01" min="0" x-model.number="hours" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hourly rate *</label>
                <input type="number" name="hourly_rate" step="0.01" min="0" x-model.number="rate" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Patients seen</label>
                <input type="number" name="patients_seen" min="0" x-model.number="patients"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bonus per patient</label>
                <input type="number" name="per_patient_bonus" step="0.01" min="0" x-model.number="bonus"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Deductions</label>
                <input type="number" name="deductions" step="0.01" min="0" x-model.number="deductions"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status *</label>
                <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($statuses as $k => $v)
                        <option value="{{ $k }}" @selected($payroll->status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $payroll->notes }}</textarea>
            </div>
        </div>

        <div class="bg-slate-50 rounded-lg p-4 grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Gross</div>
                <div class="text-2xl font-bold text-slate-900 font-mono" x-text="'$' + (hours*rate + patients*bonus).toFixed(2)"></div>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Deductions</div>
                <div class="text-2xl font-bold text-rose-600 font-mono" x-text="'-$' + (deductions || 0).toFixed(2)"></div>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Net</div>
                <div class="text-2xl font-bold text-emerald-700 font-mono" x-text="'$' + (hours*rate + patients*bonus - (deductions||0)).toFixed(2)"></div>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <a href="{{ route('hhrr.payroll.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Cancel</a>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">Save changes</button>
        </div>
    </form>
@endsection
