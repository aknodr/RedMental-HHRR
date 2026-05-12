@extends('layouts.app')

@section('title', 'Contracts')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Contracts</h1>
            <p class="text-slate-500 text-sm mt-1">Employment and service agreements.</p>
        </div>
        @can('hhrr.contracts.create')
            <a href="{{ route('hhrr.contracts.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="plus" class="w-4 h-4"></i> New contract
            </a>
        @endcan
    </div>

    {{-- Tabs --}}
    @php
        $tabs = [
            'all'      => ['Total',          'slate',   $counts['all']],
            'active'   => ['Active',         'emerald', $counts['active']],
            'expiring' => ['Expiring ≤30d',  'amber',   $counts['expiring']],
            'expired'  => ['Expired',        'rose',    $counts['expired']],
            'draft'    => ['Drafts',         'slate',   $counts['draft']],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-5">
        @foreach($tabs as $key => [$label, $color, $count])
            @php $isActive = ($status === $key) || ($key === 'all' && !$status); @endphp
            <a href="{{ route('hhrr.contracts.index', $key === 'all' ? [] : ['status' => $key]) }}"
               class="bg-white rounded-xl border-2 p-4 transition hover:shadow {{ $isActive ? 'border-'.$color.'-400 ring-2 ring-'.$color.'-100' : 'border-slate-200' }}">
                <div class="text-[10px] font-bold uppercase tracking-widest text-{{ $color }}-600">{{ $label }}</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $count }}</div>
            </a>
        @endforeach
    </div>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex gap-3 items-end flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Title…" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Type</label>
            <select name="type" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach($types as $k => $v)<option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Title</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Type</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Party</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Dates</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Amount</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contracts as $contract)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ $contract->title }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $types[$contract->type] ?? $contract->type }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            @if($contract->employee) <span class="text-amber-600">EMP</span> {{ $contract->employee->full_name }}
                            @elseif($contract->patient) <span class="text-emerald-600">PAT</span> {{ $contract->patient->full_name }}
                            @else — @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500">
                            {{ optional($contract->start_date)->format('m/d/Y') ?: '—' }}
                            @if($contract->end_date)
                                → {{ $contract->end_date->format('m/d/Y') }}
                                @php $d = $contract->days_to_expiry; @endphp
                                @if($d !== null)
                                    @if($d < 0)
                                        <div class="text-rose-600 font-semibold mt-0.5">Expired {{ abs($d) }}d ago</div>
                                    @elseif($d <= 30)
                                        <div class="text-amber-600 font-semibold mt-0.5">In {{ $d }}d</div>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-3 font-mono text-sm text-slate-700">{{ $contract->amount ? '$' . number_format($contract->amount, 2) : '—' }}</td>
                        <td class="px-5 py-3">
                            @php
                                $eff = $contract->effective_status;
                                $color = match($eff) {
                                    'active'    => 'emerald',
                                    'expiring'  => 'amber',
                                    'expired'   => 'rose',
                                    'terminated'=> 'rose',
                                    default     => 'slate',
                                };
                                $effLabel = $eff === 'expiring' ? 'Expiring' : ($statuses[$eff] ?? ucfirst($eff));
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-{{ $color }}-50 text-{{ $color }}-700 text-[10px] font-bold uppercase">
                                @if($eff === 'expiring') <i data-lucide="alert-triangle" class="w-3 h-3"></i> @endif
                                {{ $effLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @include('hhrr._shared._action_buttons', [
                                'editRoute'   => auth()->user()->can('hhrr.contracts.edit')   ? route('hhrr.contracts.edit', $contract)    : null,
                                'deleteRoute' => auth()->user()->can('hhrr.contracts.delete') ? route('hhrr.contracts.destroy', $contract) : null,
                                'deleteLabel' => 'contract — ' . ($contract->title ?: '#'.$contract->id),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-16 text-center text-slate-400 text-sm">No contracts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($contracts->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $contracts->links() }}</div>
        @endif
    </div>
@endsection
