@extends('layouts.app')

@section('title', 'Permissions matrix')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to roles
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Permissions matrix</h1>
            <p class="text-slate-500 text-sm mt-1">Toggle which permissions each role has. Changes apply to everyone with that role.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    @if($roles->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
            <i data-lucide="shield" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
            <p class="text-sm text-slate-500">No roles yet — <a href="{{ route('admin.roles.create') }}" class="text-indigo-600 hover:underline">create a role first</a> to start assigning permissions.</p>
        </div>
    @else
        <form method="POST" action="{{ route('admin.roles.matrix.save') }}">
            @csrf

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="sticky left-0 bg-slate-50 px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase border-r border-slate-200 min-w-[260px]">Permission</th>
                                @foreach($roles as $role)
                                    <th class="px-3 py-3 text-center text-xs font-bold text-slate-700 border-r border-slate-100 last:border-r-0" style="min-width: 120px;">
                                        <div class="truncate" title="{{ $role->name }}">{{ $role->name }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($catalog as $group => $perms)
                                <tr class="bg-indigo-50/50 border-y border-indigo-100">
                                    <td colspan="{{ $roles->count() + 1 }}" class="px-4 py-2 text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                                        {{ $group }}
                                    </td>
                                </tr>
                                @foreach($perms as $permName => $description)
                                    <tr class="hover:bg-slate-50 border-b border-slate-100">
                                        <td class="sticky left-0 bg-white group-hover:bg-slate-50 px-4 py-2 border-r border-slate-200">
                                            <div class="font-medium text-slate-800">{{ $description }}</div>
                                            <code class="text-[10px] text-slate-400 font-mono">{{ $permName }}</code>
                                        </td>
                                        @foreach($roles as $role)
                                            @php($checked = $role->permissions->contains('name', $permName))
                                            <td class="px-3 py-2 text-center border-r border-slate-100 last:border-r-0">
                                                <label class="inline-flex cursor-pointer">
                                                    <input type="checkbox"
                                                           name="matrix[{{ $role->id }}][{{ $permName }}]"
                                                           value="1"
                                                           @checked($checked)
                                                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                                                </label>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between">
                <p class="text-xs text-slate-500">{{ $roles->count() }} {{ Str::plural('role', $roles->count()) }}</p>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                    <i data-lucide="save" class="w-4 h-4"></i> Save matrix
                </button>
            </div>
        </form>
    @endif
@endsection
