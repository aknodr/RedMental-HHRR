@extends('layouts.app')
@section('title', 'HHRR — Invoice ' . $invoice->invoice_number)

@section('content')
@php
    use App\Models\Hhrr\Invoice;
    $statusBadge = match($invoice->status){
        'paid'    => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'check-circle', 'Paid'],
        'sent'    => ['bg-blue-50 text-blue-700 border-blue-200',          'send', 'Sent'],
        'overdue' => ['bg-rose-50 text-rose-700 border-rose-200',          'alert-triangle', 'Overdue'],
        'void'    => ['bg-slate-50 text-slate-400 border-slate-200',       'ban', 'Void'],
        default   => ['bg-slate-50 text-slate-600 border-slate-200',       'pencil', 'Draft'],
    };
@endphp

<style>
    .iv-section { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.02); margin-bottom:1rem; }
    .iv-hd { padding:.75rem 1.25rem; display:flex; align-items:center; gap:.6rem; border-bottom:1px solid #e2e8f0; background:linear-gradient(180deg,#fff,#fafbff); }
    .iv-num { width:26px; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; color:#fff; flex-shrink:0; background:linear-gradient(135deg,#4338ca,#3b82f6); }
    .iv-title { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:#1e293b; }
    .iv-body { padding:1rem 1.25rem; }
</style>

<div class="max-w-7xl mx-auto">
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-4 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('hhrr.invoices.index') }}" class="w-9 h-9 rounded-lg bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-colors border border-slate-200 flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="p-2.5 bg-gradient-to-br from-indigo-500 to-blue-700 text-white rounded-xl shadow-md shadow-indigo-500/25">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-widest text-indigo-500">HHRR · Invoice</div>
                    <h1 class="text-xl font-black text-slate-800 font-mono">{{ $invoice->invoice_number }}</h1>
                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                        <span class="text-[11px] text-slate-500 font-semibold">{{ $invoice->patient?->full_name ?? '—' }}</span>
                        @if($invoice->payer)<span class="text-slate-200">|</span><span class="text-[10px] text-slate-400 font-medium">{{ $invoice->payer->name }}</span>@endif
                        <span class="text-slate-200">|</span>
                        <span class="text-[10px] text-slate-400 font-medium">Issued {{ $invoice->issue_date->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statusBadge[0] }}">
                    <i data-lucide="{{ $statusBadge[1] }}" class="w-3.5 h-3.5"></i> {{ $statusBadge[2] }}
                </span>
                @if($invoice->status === 'draft')
                    @can('hhrr.invoices.edit')
                        <form method="POST" action="{{ route('hhrr.invoices.send', $invoice) }}" class="inline">@csrf
                            <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg inline-flex items-center gap-1.5"><i data-lucide="send" class="w-3.5 h-3.5"></i> Mark sent</button>
                        </form>
                    @endcan
                @endif
                @if($invoice->status !== 'paid')
                    @can('hhrr.invoices.edit')
                        <a href="{{ route('hhrr.invoices.edit', $invoice) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold uppercase tracking-wider rounded-lg inline-flex items-center gap-1.5"><i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit</a>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1 space-y-4">
            <div class="iv-section">
                <div class="iv-hd"><div class="iv-num">i</div><div><div class="iv-title">Bill to</div></div></div>
                <div class="iv-body text-[12px] space-y-1">
                    <div class="font-bold text-slate-800 text-[14px]">{{ $invoice->patient?->full_name }}</div>
                    @if($invoice->patient?->address)<div class="text-slate-600">{{ $invoice->patient->address }}</div>@endif
                    @if($invoice->patient?->city)<div class="text-slate-600">{{ $invoice->patient->city }}, {{ $invoice->patient->state }} {{ $invoice->patient->zip }}</div>@endif
                    @if($invoice->patient?->phone)<div class="text-slate-500 mt-2">{{ $invoice->patient->phone }}</div>@endif
                </div>
            </div>

            <div class="iv-section">
                <div class="iv-hd"><div class="iv-num"><i data-lucide="calendar" class="w-3.5 h-3.5"></i></div><div><div class="iv-title">Dates</div></div></div>
                <div class="iv-body space-y-2 text-[12px]">
                    <div class="flex justify-between"><span class="text-slate-400 font-bold">Issued</span><span class="font-semibold text-slate-700">{{ $invoice->issue_date->format('M j, Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-bold">Due</span><span class="font-semibold text-slate-700">{{ $invoice->due_date->format('M j, Y') }}</span></div>
                    @if($invoice->paid_date)
                        <div class="flex justify-between"><span class="text-slate-400 font-bold">Paid</span><span class="font-semibold text-emerald-600">{{ $invoice->paid_date->format('M j, Y') }}</span></div>
                    @endif
                </div>
            </div>

            @if($invoice->status !== 'paid' && $invoice->status !== 'void')
                @can('hhrr.invoices.edit')
                    <div class="iv-section">
                        <div class="iv-hd"><div class="iv-num"><i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i></div><div><div class="iv-title">Record payment</div></div></div>
                        <div class="iv-body">
                            <form method="POST" action="{{ route('hhrr.invoices.mark_paid', $invoice) }}" class="space-y-2">@csrf
                                <input type="number" step="0.01" min="0" name="amount_paid" required value="{{ $invoice->total }}" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm font-mono">
                                <input type="date" name="paid_date" required value="{{ now()->toDateString() }}" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
                                <button class="w-full px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg inline-flex items-center justify-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5"></i> Record payment</button>
                            </form>
                        </div>
                    </div>
                @endcan
            @endif
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="iv-section">
                <div class="iv-hd"><div class="iv-num">1</div><div><div class="iv-title">Line items</div></div></div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-center">CPT</th>
                            <th class="px-4 py-2 text-center">Date</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Unit $</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoice->lines as $line)
                            <tr>
                                <td class="px-4 py-2.5 text-[13px]">{{ $line->description }}</td>
                                <td class="px-4 py-2.5 text-center">@if($line->cpt_code)<span class="font-mono text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 px-1.5 py-0.5 rounded">{{ $line->cpt_code }}</span>@endif</td>
                                <td class="px-4 py-2.5 text-center text-[11px] text-slate-500">{{ optional($line->service_date)->format('M j, Y') }}</td>
                                <td class="px-4 py-2.5 text-right font-mono text-[12px]">{{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }}</td>
                                <td class="px-4 py-2.5 text-right font-mono text-[12px]">${{ number_format($line->unit_price, 2) }}</td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-[13px]">${{ number_format($line->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400 text-sm">No line items.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr class="border-t-2 border-slate-200">
                            <td colspan="5" class="px-4 py-2 text-right text-[11px] text-slate-500 font-bold uppercase">Subtotal</td>
                            <td class="px-4 py-2 text-right font-mono font-bold">${{ number_format((float) $invoice->subtotal, 2) }}</td>
                        </tr>
                        @if((float) $invoice->tax > 0)
                            <tr><td colspan="5" class="px-4 py-1 text-right text-[11px] text-slate-500 font-bold uppercase">Tax</td>
                                <td class="px-4 py-1 text-right font-mono">${{ number_format((float) $invoice->tax, 2) }}</td></tr>
                        @endif
                        <tr class="border-t border-slate-200">
                            <td colspan="5" class="px-4 py-2 text-right text-[11px] text-slate-700 font-black uppercase">Total</td>
                            <td class="px-4 py-2 text-right font-mono font-black text-lg text-indigo-700">${{ number_format((float) $invoice->total, 2) }}</td>
                        </tr>
                        @if((float) $invoice->amount_paid > 0)
                            <tr><td colspan="5" class="px-4 py-1 text-right text-[11px] text-emerald-600 font-bold uppercase">Paid</td>
                                <td class="px-4 py-1 text-right font-mono text-emerald-600">−${{ number_format((float) $invoice->amount_paid, 2) }}</td></tr>
                            <tr class="border-t border-slate-200">
                                <td colspan="5" class="px-4 py-2 text-right text-[11px] text-amber-700 font-black uppercase">Balance due</td>
                                <td class="px-4 py-2 text-right font-mono font-black text-lg {{ $invoice->balance > 0 ? 'text-amber-700' : 'text-emerald-600' }}">${{ number_format($invoice->balance, 2) }}</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            @if($invoice->notes || $invoice->terms)
                <div class="iv-section">
                    <div class="iv-hd"><div class="iv-num"><i data-lucide="sticky-note" class="w-3.5 h-3.5"></i></div><div><div class="iv-title">Notes &amp; terms</div></div></div>
                    <div class="iv-body grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($invoice->notes)<div><div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Notes</div><div class="text-[13px] text-slate-700 whitespace-pre-line">{{ $invoice->notes }}</div></div>@endif
                        @if($invoice->terms)<div><div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Terms</div><div class="text-[13px] text-slate-700 whitespace-pre-line">{{ $invoice->terms }}</div></div>@endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
