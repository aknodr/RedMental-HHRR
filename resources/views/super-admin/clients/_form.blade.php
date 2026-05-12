{{--
    Shared form for client information — used by both create.blade.php and edit.blade.php.
    When $client is null → create mode (also renders the initial admin user fields).
--}}
@php($isEdit = isset($client) && $client->exists)

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
    <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i> Client information
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Display name *</label>
            <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Legal name</label>
            <input type="text" name="legal_name" value="{{ old('legal_name', $client->legal_name ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Tax ID / EIN</label>
            <input type="text" name="tax_id" value="{{ old('tax_id', $client->tax_id ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $client->phone ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $client->email ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
            <input type="text" name="address" value="{{ old('address', $client->address ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
            <input type="text" name="city" value="{{ old('city', $client->city ?? '') }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">State</label>
                <input type="text" name="state" maxlength="2" value="{{ old('state', $client->state ?? '') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">ZIP</label>
                <input type="text" name="zip" value="{{ old('zip', $client->zip ?? '') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $client->notes ?? '') }}</textarea>
        </div>

        @if($isEdit)
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $client->active))
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Client is active
                </label>
            </div>
        @endif
    </div>
</div>

@unless($isEdit)
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
        <h3 class="font-semibold text-slate-900 mb-1 flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4 text-emerald-600"></i> Initial administrator
        </h3>
        <p class="text-xs text-slate-500 mb-4">This user can sign in to the client's account and manage their own roles, permissions, and users.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Full name *</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Email *</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Password * <span class="font-normal text-slate-400">(min 8)</span></label>
                <input type="password" name="admin_password" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Confirm password *</label>
                <input type="password" name="admin_password_confirmation" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>
@endunless
