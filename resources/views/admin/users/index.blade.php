@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Users</h1>
            <p class="text-slate-500 text-sm mt-1">People with access to this organization. Assign roles to control what they can do.</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
            <i data-lucide="user-plus" class="w-4 h-4"></i> New user
        </a>
    </div>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="{{ $search }}" placeholder="Name or email…"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
        @if($search)<a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>@endif
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Roles</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Last sign-in</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-sm">
                            @forelse($user->roles as $role)
                                <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded">{{ $role->name }}</span>
                            @empty
                                <span class="text-slate-400 text-xs italic">—</span>
                            @endforelse
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-5 py-3">
                            @if($user->active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm-delete="{{ $user->name }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400 text-sm">No users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
