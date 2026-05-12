@extends('layouts.app')

@section('title', $clinic->exists ? 'Edit clinic' : 'New clinic')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('hhrr.clinics.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to clinics
        </a>
        <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $clinic->exists ? 'Edit clinic' : 'New clinic' }}</h1>

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $clinic->exists ? route('hhrr.clinics.update', $clinic) : route('hhrr.clinics.store') }}"
              class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
            @csrf
            @if($clinic->exists) @method('PUT') @endif

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $clinic->name) }}" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Code</label>
                    <input type="text" name="code" value="{{ old('code', $clinic->code) }}" maxlength="20"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="relative">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
                <input type="text" id="clinic_address" name="address" value="{{ old('address', $clinic->address) }}"
                       class="addr-input w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       data-suggestions-id="clinic_addr_suggestions"
                       data-city-id="clinic_city" data-state-id="clinic_state" data-zip-id="clinic_zip"
                       placeholder="Start typing address…">
                <div id="clinic_addr_suggestions" class="addr-suggestions" style="display:none;"></div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
                    <input type="text" id="clinic_city" name="city" value="{{ old('city', $clinic->city) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">State</label>
                    <input type="text" id="clinic_state" name="state" value="{{ old('state', $clinic->state) }}" maxlength="2"
                           class="state-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ZIP</label>
                    <input type="text" id="clinic_zip" name="zip" value="{{ old('zip', $clinic->zip) }}"
                           class="zip-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $clinic->phone) }}"
                           class="phone-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $clinic->email) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700 pt-2">
                <input type="checkbox" name="active" value="1" {{ old('active', $clinic->active) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Active
            </label>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                <a href="{{ route('hhrr.clinics.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Cancel</a>
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">{{ $clinic->exists ? 'Save changes' : 'Create clinic' }}</button>
            </div>
        </form>
    </div>

    @include('hhrr._shared._form_helpers')
@endsection
