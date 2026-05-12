@extends('layouts.app')

@section('title', 'New client')

@section('content')
    <a href="{{ route('super-admin.clients.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to clients
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">New client</h1>

    @if($errors->any())
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super-admin.clients.store') }}">
        @csrf
        @include('super-admin.clients._form')

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('super-admin.clients.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="check" class="w-4 h-4"></i> Create client
            </button>
        </div>
    </form>
@endsection
