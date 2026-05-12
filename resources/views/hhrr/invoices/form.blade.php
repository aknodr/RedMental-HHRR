@extends('layouts.app')
@section('title', 'HHRR — Invoice')

@section('content')
@php $isEdit = $invoice->exists; @endphp

<style>
    .iv-section { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.02); margin-bottom:1rem; }
    .iv-hd { padding:.75rem 1.25rem; display:flex; align-items:center; gap:.6rem; border-bottom:1px solid #e2e8f0; background:linear-gradient(180deg,#fff,#fafbff); }
    .iv-num { width:26px; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; color:#fff; flex-shrink:0; background:linear-gradient(135deg,#4338ca,#3b82f6); }
    .iv-title { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:#1e293b; }
    .iv-body { padding:1.1rem 1.25rem; }
    .field-label { display:block; font-size:.65rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.3rem; }
    .field-input, .field-select, .field-textarea {
        width:100%; padding:.55rem .75rem; border:1px solid #e2e8f0; border-radius:.55rem;
        font-size:.85rem; color:#1e293b; background:#fff;
    }
    .field-input:focus, .field-select:focus, .field-textarea:focus { outline:none; border-color:#4338ca; box-shadow:0 0 0 3px rgba(67,56,202,.08); }
    .field-textarea { min-height:80px; resize:vertical; line-height:1.55; }
    .field-mono { font-family:'JetBrains Mono', ui-monospace, monospace; }

    .line-row { display:grid; grid-template-columns:2fr 80px 100px 70px 90px 80px 28px; gap:6px; align-items:center; padding:6px 0; }
    .line-row input, .line-row select { padding:.4rem .55rem; border:1px solid #e2e8f0; border-radius:.4rem; font-size:.78rem; }
    .line-row input.field-mono { font-family:'JetBrains Mono', ui-monospace, monospace; }
    @media (max-width: 768px) { .line-row { grid-template-columns:1fr; } }
</style>

<div class="max-w-6xl mx-auto"
     x-data="{
        lines: @js(($invoice->lines ?? collect())->map(fn ($l) => [
            'id' => $l->id, 'description' => $l->description, 'cpt_code' => $l->cpt_code,
            'service_date' => optional($l->service_date)->format('Y-m-d'),
            'quantity' => (float) $l->quantity, 'unit_price' => (float) $l->unit_price,
        ])->all()),
        addLine() { this.lines.push({ id: null, description: '', cpt_code: '', service_date: '{{ now()->toDateString() }}', quantity: 1, unit_price: 0 }); },
        get subtotal() { return this.lines.reduce((s, l) => s + (Number(l.quantity) * Number(l.unit_price)), 0); },
        money(v) { return '$' + Number(v || 0).toFixed(2); },
     }">

    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-4 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('hhrr.invoices.index') }}" class="w-9 h-9 rounded-lg bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-colors border border-slate-200 flex-shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div class="p-2.5 bg-gradient-to-br from-indigo-500 to-blue-700 text-white rounded-xl shadow-md shadow-indigo-500/25 flex-shrink-0">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-widest text-indigo-500">HHRR · Invoice</div>
                <h1 class="text-xl font-black text-slate-800">{{ $isEdit ? 'Edit invoice' : 'New invoice' }}</h1>
            </div>
        </div>
    </div>

    @include('hhrr._shared._flash')

    <form method="POST" action="{{ $isEdit ? route('hhrr.invoices.update', $invoice) : route('hhrr.invoices.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="iv-section">
            <div class="iv-hd"><div class="iv-num">1</div><div><div class="iv-title">Header</div></div></div>
            <div class="iv-body grid grid-cols-2 md:grid-cols-3 gap-3">
                <div>
                    <label class="field-label">Invoice # *</label>
                    <input type="text" name="invoice_number" required maxlength="30" value="{{ old('invoice_number', $invoice->invoice_number) }}" class="field-input field-mono">
                </div>
                <div>
                    <label class="field-label">Issue date *</label>
                    <input type="date" name="issue_date" required value="{{ old('issue_date', optional($invoice->issue_date)->format('Y-m-d')) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Due date *</label>
                    <input type="date" name="due_date" required value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Patient *</label>
                    <select name="patient_id" required class="field-select">
                        <option value="">—</option>
                        @foreach($patients as $p)<option value="{{ $p->id }}" @selected(old('patient_id', $invoice->patient_id) == $p->id)>{{ $p->full_name }}@if($p->mrn) ({{ $p->mrn }})@endif</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Payer</label>
                    <select name="payer_id" class="field-select">
                        <option value="">— Self-pay —</option>
                        @foreach($payers as $py)<option value="{{ $py->id }}" @selected(old('payer_id', $invoice->payer_id) == $py->id)>{{ $py->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Status *</label>
                    <select name="status" required class="field-select">
                        @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(old('status', $invoice->status) === $k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="iv-section">
            <div class="iv-hd">
                <div class="iv-num">2</div>
                <div class="flex-1"><div class="iv-title">Line items</div></div>
                <button type="button" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[10px] font-bold uppercase tracking-wider rounded-md inline-flex items-center gap-1" @click="addLine">
                    <i data-lucide="plus" class="w-3 h-3"></i> Add line
                </button>
            </div>
            <div class="iv-body">
                <div class="hidden md:grid grid-cols-[2fr_80px_100px_70px_90px_80px_28px] gap-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1 mb-1">
                    <div>Description</div><div>CPT</div><div>Service date</div><div class="text-right">Qty</div><div class="text-right">Unit $</div><div class="text-right">Total</div><div></div>
                </div>
                <template x-for="(line, idx) in lines" :key="idx">
                    <div class="line-row border-b border-slate-100">
                        <input type="hidden" :name="`lines[${idx}][id]`" :value="line.id ?? ''">
                        <input type="text" :name="`lines[${idx}][description]`" x-model="line.description" required placeholder="Description / service rendered">
                        <input type="text" :name="`lines[${idx}][cpt_code]`" x-model="line.cpt_code" maxlength="20" class="field-mono" placeholder="CPT">
                        <input type="date" :name="`lines[${idx}][service_date]`" x-model="line.service_date">
                        <input type="number" step="0.01" min="0" :name="`lines[${idx}][quantity]`" x-model.number="line.quantity" required class="field-mono text-right">
                        <input type="number" step="0.01" min="0" :name="`lines[${idx}][unit_price]`" x-model.number="line.unit_price" required class="field-mono text-right">
                        <div class="text-right font-mono text-[12px] font-bold text-slate-700" x-text="money(line.quantity * line.unit_price)"></div>
                        <button type="button" @click="lines.splice(idx, 1)" class="text-rose-600 hover:bg-rose-50 rounded p-1"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                    </div>
                </template>
                <template x-if="lines.length === 0">
                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center text-slate-400 text-sm">
                        <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        No line items yet — click "Add line" to start.
                    </div>
                </template>
                <div class="border-t border-slate-200 mt-3 pt-3 flex justify-end">
                    <div class="w-64 space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500 font-bold uppercase text-[11px]">Subtotal</span><span class="font-mono font-bold" x-text="money(subtotal)"></span></div>
                        <div class="flex justify-between items-center">
                            <label class="text-slate-500 font-bold uppercase text-[11px]">Tax rate</label>
                            <input type="number" step="0.001" min="0" max="1" name="tax_rate" value="{{ old('tax_rate', 0) }}" class="w-20 px-2 py-0.5 border border-slate-300 rounded text-right text-[12px] font-mono">
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-1.5"><span class="text-slate-700 font-black uppercase text-[11px]">Total</span><span class="font-mono font-black text-lg text-indigo-700" x-text="money(subtotal * (1 + Number(document.querySelector('input[name=tax_rate]')?.value ?? 0)))"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="iv-section">
            <div class="iv-hd"><div class="iv-num">3</div><div><div class="iv-title">Notes &amp; terms</div></div></div>
            <div class="iv-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="field-label">Notes</label><textarea name="notes" class="field-textarea">{{ old('notes', $invoice->notes) }}</textarea></div>
                <div><label class="field-label">Terms</label><textarea name="terms" class="field-textarea" placeholder="Payment terms, late-fee policy, …">{{ old('terms', $invoice->terms) }}</textarea></div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pb-6">
            <a href="{{ route('hhrr.invoices.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Cancel</a>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold uppercase tracking-wider rounded-lg inline-flex items-center gap-1.5 shadow-md shadow-indigo-500/25"><i data-lucide="save" class="w-4 h-4"></i> {{ $isEdit ? 'Save changes' : 'Create invoice' }}</button>
        </div>
    </form>
</div>
@endsection
