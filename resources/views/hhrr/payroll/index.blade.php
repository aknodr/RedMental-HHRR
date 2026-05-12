@extends('layouts.app')

@section('title', 'Payroll')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Payroll</h1>
            <p class="text-slate-500 text-sm mt-1">Generate payroll runs based on appointments × hourly rate × patient count.</p>
        </div>
        @can('hhrr.payroll.manage')
            <a href="{{ route('hhrr.payroll.generate') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg inline-flex items-center gap-2">
                <i data-lucide="calculator" class="w-4 h-4"></i> Generate payroll
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">Period</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">Frequency</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Hours</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Rate</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Patients</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Gross</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Net</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($payrolls as $p)
                    @php($statusColor = match($p->status) {
                        'paid'     => 'bg-emerald-100 text-emerald-700',
                        'approved' => 'bg-indigo-100 text-indigo-700',
                        default    => 'bg-slate-100 text-slate-700',
                    })
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-xs text-slate-700 whitespace-nowrap">{{ $p->period_start->format('M j') }} – {{ $p->period_end->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-xs uppercase text-slate-500">{{ $frequencies[$p->frequency] ?? $p->frequency }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $p->employee?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-mono">{{ number_format((float)$p->hours_worked, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono">${{ number_format((float)$p->hourly_rate, 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ $p->patients_seen }}</td>
                        <td class="px-4 py-3 text-right font-bold font-mono text-slate-800">${{ number_format((float)$p->gross, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold font-mono text-emerald-700">${{ number_format((float)$p->net, 2) }}</td>
                        <td class="px-4 py-3"><span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $statusColor }} uppercase">{{ $p->status }}</span></td>
                        <td class="px-4 py-3 text-right">
                            @include('hhrr._shared._action_buttons', [
                                'editRoute'   => auth()->user()->can('hhrr.payroll.manage') ? route('hhrr.payroll.edit', $p)    : null,
                                'deleteRoute' => auth()->user()->can('hhrr.payroll.manage') ? route('hhrr.payroll.destroy', $p) : null,
                                'deleteLabel' => 'payroll for ' . ($p->employee?->full_name ?? 'employee'),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-5 py-16 text-center text-slate-400 text-sm">No payroll runs yet — click <b>Generate payroll</b> to create one.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($payrolls->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $payrolls->links() }}</div>
        @endif
    </div>
@endsection
