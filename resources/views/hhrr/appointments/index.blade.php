@extends('layouts.app')

@section('title', 'Appointments calendar')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Appointments</h1>
            <p class="text-slate-500 text-sm mt-1">Each provider is capped at <b>{{ \App\Models\Hhrr\Appointment::MAX_PER_PROVIDER_PER_DAY }} patients per day</b>.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hhrr.appointments.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
               class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 inline-flex items-center gap-1">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
            </a>
            <h2 class="text-lg font-bold text-slate-900 px-3">{{ $month->format('F Y') }}</h2>
            <a href="{{ route('hhrr.appointments.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
               class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 inline-flex items-center gap-1">
                Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
            @can('hhrr.appointments.create')
                <a href="{{ route('hhrr.appointments.create') }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> New appointment
                </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200 text-[10px] font-bold uppercase tracking-widest text-slate-500">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                <div class="px-2 py-2 text-center">{{ $d }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach($days as $day)
                @php
                    $key = $day->toDateString();
                    $todays = $appointments[$key] ?? collect();
                    $isOtherMonth = $day->month !== $month->month;
                    $isToday = $day->isToday();
                    // Count per provider for the day
                    $perProvider = $todays->groupBy('provider_id')->map->count();
                @endphp
                <div class="min-h-[110px] border-r border-b border-slate-100 p-2 {{ $isOtherMonth ? 'bg-slate-50/50' : '' }} {{ $isToday ? 'bg-indigo-50/30' : '' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs {{ $isOtherMonth ? 'text-slate-400' : 'text-slate-700 font-semibold' }} {{ $isToday ? 'text-indigo-700 font-bold' : '' }}">
                            {{ $day->day }}
                        </span>
                        @can('hhrr.appointments.create')
                            <a href="{{ route('hhrr.appointments.create', ['date' => $key]) }}"
                               class="text-slate-400 hover:text-indigo-600" title="Add appointment">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            </a>
                        @endcan
                    </div>
                    <div class="space-y-1">
                        @foreach($todays->take(4) as $appt)
                            @php
                                $isFull = ($perProvider[$appt->provider_id] ?? 0) >= \App\Models\Hhrr\Appointment::MAX_PER_PROVIDER_PER_DAY;
                                $statusColor = match($appt->status) {
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-slate-100 text-slate-500 line-through',
                                    'no_show'   => 'bg-rose-100 text-rose-700',
                                    default     => 'bg-indigo-100 text-indigo-800',
                                };
                            @endphp
                            <a href="{{ auth()->user()->can('hhrr.appointments.edit') ? route('hhrr.appointments.edit', $appt) : '#' }}"
                               class="block text-[10px] px-1.5 py-1 rounded {{ $statusColor }} truncate"
                               title="{{ $appt->scheduled_at->format('g:i a') }} · {{ $appt->patient?->full_name }} → {{ $appt->provider?->full_name }}">
                                <span class="font-bold">{{ $appt->scheduled_at->format('g:ia') }}</span>
                                {{ $appt->patient?->last_name }}
                            </a>
                        @endforeach
                        @if($todays->count() > 4)
                            <div class="text-[10px] text-slate-500 italic px-1.5">+{{ $todays->count() - 4 }} more</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Daily provider load summary --}}
    @php
        $todaysCounts = $appointments->flatten()->where('status', '!=', 'cancelled')
            ->filter(fn ($a) => $a->scheduled_at->isToday())
            ->groupBy('provider_id');
    @endphp
    @if($todaysCounts->isNotEmpty())
        <div class="mt-6 bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-900 mb-3 text-sm">Today's provider load</h3>
            <div class="space-y-2">
                @foreach($providers as $prov)
                    @php($c = ($todaysCounts[$prov->id] ?? collect())->count())
                    @if($c > 0)
                        <div class="flex items-center gap-3">
                            <div class="text-sm font-semibold text-slate-700 w-48 truncate">{{ $prov->full_name }}</div>
                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                @php($pct = min(100, $c * 5))
                                <div class="{{ $c >= 20 ? 'bg-rose-500' : ($c >= 15 ? 'bg-amber-500' : 'bg-emerald-500') }} h-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="text-xs font-bold text-slate-600 w-16 text-right">{{ $c }} / 20</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
@endsection
