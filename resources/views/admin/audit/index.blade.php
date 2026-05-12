@extends('layouts.app')

@section('title', 'Audit log')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Audit log</h1>
        <p class="text-slate-500 text-sm mt-1">Every authenticated request is recorded for HIPAA compliance — including reads (VIEW). Sensitive fields (password, SSN) are redacted.</p>
    </div>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Action</label>
            <select name="action" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">User</label>
            <select name="user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Any</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string)$userId === (string)$u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">When</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">User</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">Action</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">Resource</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">URL</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($logs as $log)
                    @php($actionColor = match($log->action) {
                        'CREATE' => 'bg-emerald-100 text-emerald-700',
                        'UPDATE' => 'bg-amber-100 text-amber-700',
                        'DELETE' => 'bg-rose-100 text-rose-700',
                        default  => 'bg-slate-100 text-slate-700',
                    })
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $actionColor }}">{{ $log->action }}</span></td>
                        <td class="px-4 py-3 text-slate-700 font-mono text-xs">{{ $log->resource }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 truncate max-w-md">{{ str_replace(url('/'), '', $log->url) }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 font-mono">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400 text-sm">No log entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
