@extends('layouts.app')

@section('title', $department->exists ? 'Edit department' : 'New department')

@section('content')
    <a href="{{ route('hhrr.departments.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to departments
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $department->exists ? 'Edit department' : 'New department' }}</h1>
    @include('hhrr._shared._flash')

    <form method="POST" action="{{ $department->exists ? route('hhrr.departments.update', $department) : route('hhrr.departments.store') }}">
        @csrf
        @if($department->exists) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Code</label>
                    <input type="text" name="code" maxlength="30" value="{{ old('code', $department->code) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $department->description) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="active" value="1" @checked(old('active', $department->active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Department is active
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hhrr.departments.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="save" class="w-4 h-4"></i> {{ $department->exists ? 'Save changes' : 'Create' }}
            </button>
        </div>
    </form>
@endsection
