@extends('layouts.app')
@section('title', 'HHRR — Invoices')

@section('content')
@php
    use App\Models\Hhrr\Invoice;
    $statusBadge = [
        'draft'   => ['bg-slate-50 text-slate-600 border-slate-200', 'pencil', 'Draft'],
        'sent'    => ['bg-blue-50 text-blue-700 border-blue-200',     'send', 'Sent'],
        'paid'    => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'check-circle', 'Paid'],
        'overdue' => ['bg-rose-50 text-rose-700 border-rose-200',     'alert-triangle', 'Overdue'],
        'void'    => ['bg-slate-50 text-slate-400 border-slate-200',  'ban', 'Void'],
    ];
@endphp

<div class="max-w-7xl mx-auto">
    {{-- HEADER --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-4 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-indigo-500 to-blue-700 text-white rounded-xl shadow-md shadow-indigo-500/25">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-widest text-indigo-500">HHRR · Billing</div>
                    <h1 class="text-xl font-black text-slate-800">Invoices</h1>
                    <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Issue, track and collect on patient and payer billing</p>
                </div>
            </div>
            @can('hhrr.invoices.create')
                <a href="{{ route('hhrr.invoices.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold uppercase tracking-wider rounded-lg inline-flex items-center gap-2 shadow-md shadow-indigo-500/25">
                    <i data-lucide="plus" class="w-4 h-4"></i> New invoice
                </a>
            @endcan
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Outstanding</div>
            <div class="text-2xl font-black text-slate-800 font-mono mt-1">${{ number_format($stats['total_outstanding'], 2) }}</div>
            <div class="text-[10px] text-amber-600 font-bold mt-0.5">Awaiting payment</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Paid YTD</div>
            <div class="text-2xl font-black text-emerald-600 font-mono mt-1">${{ number_format($stats['paid_ytd'], 2) }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">{{ now()->year }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Overdue</div>
            <div class="text-2xl font-black text-rose-600 font-mono mt-1">{{ $stats['overdue_count'] }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">need follow-up</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Drafts</div>
            <div class="text-2xl font-black text-slate-700 font-mono mt-1">{{ $stats['draft_count'] }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">not yet sent</div>
        </div>
    </div>

    <form method="GET" class="bg-white border border-slate-200 rounded-2xl p-3 mb-4 flex items-end gap-3 shadow-sm">
        <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status</label>
            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm min-w-[160px]">
                <option value="">All statuses</option>
                @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Invoice #</th>
                    <th class="px-4 py-3 text-left">Patient</th>
                    <th class="px-4 py-3 text-left">Payer</th>
                    <th class="px-4 py-3 text-left">Issued</th>
                    <th class="px-4 py-3 text-left">Due</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($invoices as $inv)
                    @php [$bClass, $bIcon, $bLabel] = $statusBadge[$inv->status] ?? $statusBadge['draft']; @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3"><a href="{{ route('hhrr.invoices.show', $inv) }}" class="font-mono font-bold text-[12px] text-indigo-600 hover:text-indigo-800">{{ $inv->invoice_number }}</a></td>
                        <td class="px-4 py-3 font-semibold text-slate-700 text-[13px]">{{ $inv->patient?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-[12px] text-slate-600">{{ $inv->payer?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-[12px] text-slate-600">{{ $inv->issue_date->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-[12px] text-slate-600">
                            {{ $inv->due_date->format('M j, Y') }}
                            @if($inv->status === 'overdue')
                                <div class="text-[10px] text-rose-600 font-bold">{{ $inv->due_date->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-[13px]">${{ number_format((float) $inv->total, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-[13px] {{ $inv->balance > 0 ? 'text-amber-700' : 'text-emerald-600' }}">${{ number_format($inv->balance, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $bClass }}">
                                <i data-lucide="{{ $bIcon }}" class="w-3 h-3"></i> {{ $bLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @include('hhrr._shared._action_buttons', [
                                'showRoute'   => route('hhrr.invoices.show', $inv),
                                'editRoute'   => $inv->status === 'paid' ? null : (auth()->user()->can('hhrr.invoices.edit') ? route('hhrr.invoices.edit', $inv) : null),
                                'deleteRoute' => $inv->status === 'paid' ? null : (auth()->user()->can('hhrr.invoices.delete') ? route('hhrr.invoices.destroy', $inv) : null),
                                'deleteLabel' => 'this invoice',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-16 text-center text-slate-400 text-sm">
                        <i data-lucide="file-text" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        No invoices yet.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div class="px-5 py-3 border-t bg-slate-50/50">{{ $invoices->links() }}</div>@endif
    </div>
</div>
@endsection
