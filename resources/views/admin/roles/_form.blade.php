@php
    $isEdit = isset($role) && $role->exists;
    $selected = $selectedIds ?? [];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
    <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="shield" class="w-4 h-4 text-indigo-600"></i> Role
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Role name *</label>
            <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-900 flex items-center gap-2">
            <i data-lucide="key-round" class="w-4 h-4 text-emerald-600"></i> Permissions
        </h3>
        <div class="flex gap-2 text-xs">
            <button type="button" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(c => c.checked = true)"
                    class="text-indigo-600 hover:underline font-semibold">Select all</button>
            <span class="text-slate-300">·</span>
            <button type="button" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(c => c.checked = false)"
                    class="text-slate-500 hover:underline font-semibold">Clear</button>
        </div>
    </div>

    <div class="space-y-5">
        @foreach($catalog as $group => $perms)
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ $group }}</h4>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($perms as $name => $description)
                        <label class="flex items-start gap-2 p-2 rounded hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $name }}"
                                   @checked(in_array($name, old('permissions', $selected)))
                                   class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <div class="text-sm font-medium text-slate-800">{{ $description }}</div>
                                <code class="text-[10px] text-slate-400 font-mono">{{ $name }}</code>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
