@extends('layouts.app')

@section('title', $payer->exists ? 'Edit payer' : 'New payer')

@section('content')
    <a href="{{ route('hhrr.payers.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to payers
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $payer->exists ? 'Edit payer' : 'New payer' }}</h1>
    @include('hhrr._shared._flash')

    <form method="POST" action="{{ $payer->exists ? route('hhrr.payers.update', $payer) : route('hhrr.payers.store') }}">
        @csrf
        @if($payer->exists) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    Name * <span class="text-slate-400 text-[10px]">— start typing to autocomplete from the FL payer registry</span>
                </label>
                <input type="text" name="name" id="payer_name" value="{{ old('name', $payer->name) }}" required
                       autocomplete="off" list="fl_payers"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <datalist id="fl_payers"></datalist>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type *</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}" @selected(old('type', $payer->type) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">EDI Payer ID</label>
                <input type="text" name="edi_payer_id" id="payer_edi" maxlength="20" value="{{ old('edi_payer_id', $payer->edi_payer_id) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label>
                <input type="text" name="phone" id="payer_phone" value="{{ old('phone', $payer->phone) }}"
                       class="phone-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $payer->email) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
                <input type="text" name="address" value="{{ old('address', $payer->address) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city', $payer->city) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">State</label>
                    <input type="text" name="state" maxlength="2" value="{{ old('state', $payer->state) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ZIP</label>
                    <input type="text" name="zip" value="{{ old('zip', $payer->zip) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $payer->notes) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $payer->active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Payer is active
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hhrr.payers.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="save" class="w-4 h-4"></i> {{ $payer->exists ? 'Save changes' : 'Create' }}
            </button>
        </div>
    </form>

    @include('hhrr._shared._form_helpers')

    <script>
    (async () => {
        try {
            const res = await fetch('/data/florida_payers.json');
            const list = await res.json();
            const dl = document.getElementById('fl_payers');
            const byName = {};
            list.forEach(p => {
                byName[p.name.toLowerCase()] = p;
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.label = `${p.type}${p.edi_payer_id ? ' · '+p.edi_payer_id : ''}`;
                dl.appendChild(opt);
            });

            const nameEl = document.getElementById('payer_name');
            const ediEl  = document.getElementById('payer_edi');
            const phEl   = document.getElementById('payer_phone');
            const typeEl = document.querySelector('select[name="type"]');

            const fill = () => {
                const match = byName[(nameEl.value || '').trim().toLowerCase()];
                if (!match) return;
                if (!ediEl.value && match.edi_payer_id) ediEl.value = match.edi_payer_id;
                if (!phEl.value  && match.phone)        phEl.value  = match.phone;
                if (typeEl) {
                    const opt = Array.from(typeEl.options).find(o => o.value.toLowerCase() === match.type.toLowerCase() || o.text.toLowerCase() === match.type.toLowerCase());
                    if (opt) typeEl.value = opt.value;
                }
                RM.toast('success', `Auto-filled from FL payer registry`);
            };
            nameEl.addEventListener('change', fill);
            nameEl.addEventListener('input',  () => { if (byName[(nameEl.value || '').trim().toLowerCase()]) fill(); });
        } catch (e) { /* silent */ }
    })();
    </script>
@endsection
