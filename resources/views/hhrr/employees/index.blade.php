@extends('layouts.app')

@section('title', 'Employees')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Employees</h1>
            <p class="text-slate-500 text-sm mt-1">People employed by this organization.</p>
        </div>
        @can('hhrr.employees.create')
            <a href="{{ route('hhrr.employees.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="user-plus" class="w-4 h-4"></i> New employee
            </a>
        @endcan
    </div>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex gap-3 items-end flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Name, email or employee #…"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Department</label>
            <select name="department" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected($department == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="active"   @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Emp. #</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Department</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Position</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($employees as $employee)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            <a href="{{ route('hhrr.employees.show', $employee) }}" class="hover:text-indigo-600">{{ $employee->full_name }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm font-mono text-slate-600">{{ $employee->employee_number ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $employee->department?->name ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $employee->position ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $employee->email ?: '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $employee->active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $employee->active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $employee->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @include('hhrr._shared._action_buttons', [
                                'showRoute'   => route('hhrr.employees.show', $employee),
                                'editRoute'   => auth()->user()->can('hhrr.employees.edit')   ? route('hhrr.employees.edit', $employee)    : null,
                                'deleteRoute' => auth()->user()->can('hhrr.employees.delete') ? route('hhrr.employees.destroy', $employee) : null,
                                'deleteLabel' => $employee->full_name,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-16 text-center text-slate-400 text-sm">No employees yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($employees->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $employees->links() }}</div>
        @endif
    </div>
@endsection
