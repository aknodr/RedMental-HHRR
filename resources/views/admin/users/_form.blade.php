@php
    $isEdit = isset($user) && $user->exists;
    $assigned = $assignedIds ?? [];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
    <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i> Account
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Full name *</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
                {{ $isEdit ? 'New password (leave blank to keep current)' : 'Password *' }}
            </label>
            <input type="password" name="password" {{ $isEdit ? '' : 'required' }}
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Confirm password</label>
            <input type="password" name="password_confirmation"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="active" value="1" @checked(old('active', $user->active ?? true))
                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Account is active
            </label>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
    <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="shield" class="w-4 h-4 text-emerald-600"></i> Roles
    </h3>
    @if($roles->isEmpty())
        <p class="text-sm text-slate-500">
            No roles available yet. <a href="{{ route('admin.roles.create') }}" class="text-indigo-600 hover:underline">Create a role</a> first.
        </p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($roles as $role)
                <label class="flex items-start gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           @checked(in_array($role->id, old('roles', $assigned)))
                           class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <div class="font-semibold text-sm text-slate-800">{{ $role->name }}</div>
                        <div class="text-[10px] text-slate-500">
                            @if(is_null($role->client_id))
                                <span class="text-amber-600 font-semibold">Built-in</span>
                            @else
                                {{ $role->permissions->count() }} {{ Str::plural('permission', $role->permissions->count()) }}
                            @endif
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    @endif
</div>
