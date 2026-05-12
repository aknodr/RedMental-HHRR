@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Roles</h1>
            <p class="text-slate-500 text-sm mt-1">Define custom roles for this organization and bundle permissions into them.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.roles.matrix') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="grid-2x2-check" class="w-4 h-4"></i> Permissions matrix
            </a>
            <a href="{{ route('admin.roles.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="plus" class="w-4 h-4"></i> New role
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Permissions</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Users</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($roles as $role)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ $role->name }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded">
                                {{ $role->permissions_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center text-sm font-semibold text-slate-600">{{ $role->users_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                      data-confirm-delete="role “{{ $role->name }}”">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-16 text-center text-slate-400 text-sm">
                            <i data-lucide="shield" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                            No custom roles yet.
                            <a href="{{ route('admin.roles.create') }}" class="text-indigo-600 hover:underline">Create the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
