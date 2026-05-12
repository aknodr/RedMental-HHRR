@extends('layouts.app')
@section('title', 'HHRR — Route planner')

@section('content')
@php
    $statusBadge = [
        'scheduled' => ['bg-blue-50 text-blue-700 border-blue-200', 'calendar'],
        'completed' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'check-circle'],
        'no_show'   => ['bg-rose-50 text-rose-700 border-rose-200', 'x-circle'],
    ];
@endphp

<style>
    .rt-section { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.02); margin-bottom:1rem; }
    .rt-hd { padding:.75rem 1.25rem; display:flex; align-items:center; gap:.6rem; border-bottom:1px solid #e2e8f0; background:linear-gradient(180deg,#fff,#fafbff); }
    .rt-num { width:26px; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; color:#fff; flex-shrink:0; }
    .rt-num.am { background:linear-gradient(135deg,#0ea5e9,#3b82f6); }
    .rt-num.pm { background:linear-gradient(135deg,#f97316,#ea580c); }
    .rt-title { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:#1e293b; }
    .rt-sub { font-size:.6rem; color:#94a3b8; font-weight:600; margin-top:1px; }
    .rt-body { padding:.85rem 1.25rem; }

    .stop-card { display:flex; gap:.75rem; padding:.65rem .85rem; border:1px solid #e2e8f0; border-radius:.65rem; margin-bottom:.5rem; background:#fff; transition:all .15s; }
    .stop-card:hover { border-color:#cbd5e1; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .stop-num { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.75rem; flex-shrink:0; color:#fff; font-family:'JetBrains Mono', ui-monospace, monospace; }
    .stop-num.am { background:linear-gradient(135deg,#0ea5e9,#3b82f6); }
    .stop-num.pm { background:linear-gradient(135deg,#f97316,#ea580c); }
    .stop-time { font-family:'JetBrains Mono', ui-monospace, monospace; font-size:.78rem; font-weight:800; color:#1e293b; }
    .stop-name { font-weight:700; font-size:.85rem; color:#1e293b; }
    .stop-meta { font-size:.7rem; color:#64748b; margin-top:.1rem; }
    .stop-leg  { font-family:'JetBrains Mono', ui-monospace, monospace; font-size:.65rem; color:#94a3b8; }

    .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:.85rem; padding:.85rem 1rem; }
    .stat-label { font-size:.6rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
    .stat-value { font-size:1.55rem; font-weight:800; line-height:1.1; margin-top:.15rem; font-family:'JetBrains Mono', ui-monospace, monospace; }

    #route-map { height: 460px; border-radius:.75rem; }
    .leaflet-control-attribution { font-size:9px; }
</style>

<div class="max-w-7xl mx-auto">
    {{-- HEADER --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-gradient-to-br from-emerald-500 to-teal-700 text-white rounded-xl shadow-md shadow-emerald-500/25">
                <i data-lucide="route" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-widest text-emerald-500">HHRR · Route planner</div>
                <h1 class="text-xl font-black text-slate-800">Daily home-visit route</h1>
                <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Pick a date · pick a starting clinic · split AM/PM · estimate fuel cost</p>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Date *</label>
                <input type="date" name="date" value="{{ $date }}" required class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Start from</label>
                <select name="start_clinic_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm"
                        onchange="document.getElementById('start_lat').value=''; document.getElementById('start_lng').value='';">
                    @foreach($clinics as $c)<option value="{{ $c->id }}" @selected($startingClinic?->id === $c->id)>{{ $c->name }}@if(! $c->latitude) (no coords){{-- still pick: falls back to default --}}@endif</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Custom start lat</label>
                <input type="text" id="start_lat" name="start_lat" value="" placeholder="{{ $startLat }}" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm font-mono">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Custom start lng</label>
                <input type="text" id="start_lng" name="start_lng" value="" placeholder="{{ $startLng }}" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm font-mono">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">MPG</label>
                    <input type="number" min="1" step="1" name="mpg" value="{{ $mpg }}" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-sm font-mono">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">$ / gal</label>
                    <input type="number" min="0" step="0.01" name="fuel_price" value="{{ $fuelPrice }}" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-sm font-mono">
                </div>
            </div>
        </div>
        <div class="flex justify-end mt-3">
            <button class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg inline-flex items-center gap-1.5">
                <i data-lucide="navigation" class="w-3.5 h-3.5"></i> Plan route
            </button>
        </div>
    </form>

    {{-- TOTALS --}}
    @php
        $totalDuration = ($amPlan['duration_minutes'] ?? 0) + ($pmPlan['duration_minutes'] ?? 0);
        $sourceBadgeClass = ($amPlan['source'] === 'osrm' || $pmPlan['source'] === 'osrm')
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
            : 'bg-amber-50 text-amber-700 border-amber-200';
        $sourceLabel = match (true) {
            $amPlan['source'] === 'osrm' || $pmPlan['source'] === 'osrm' => 'Live road network',
            $amPlan['source'] === 'cache' || $pmPlan['source'] === 'cache' => 'Cached road data',
            $amPlan['source'] === 'haversine-fallback' || $pmPlan['source'] === 'haversine-fallback' => 'Straight-line fallback',
            default => 'No data',
        };
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
        <div class="stat-card">
            <div class="stat-label">Stops</div>
            <div class="stat-value text-slate-800">{{ $totalStops }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">{{ $amPlan['stops']->count() }} AM · {{ $pmPlan['stops']->count() }} PM</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total miles</div>
            <div class="stat-value text-blue-600">{{ number_format($totalMiles, 1) }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">via real road network</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Drive time</div>
            <div class="stat-value text-violet-600">{{ floor($totalDuration / 60) }}<span class="text-base">h</span>{{ str_pad((string) ($totalDuration % 60), 2, '0', STR_PAD_LEFT) }}<span class="text-base">m</span></div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">incl. return-to-start</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Estimated fuel</div>
            <div class="stat-value text-emerald-600">${{ number_format($totalFuel, 2) }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">{{ $mpg }} mpg @ ${{ number_format($fuelPrice, 2) }}/gal</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">No-coord patients</div>
            <div class="stat-value {{ $missingCoords->count() > 0 ? 'text-amber-600' : 'text-slate-300' }}">{{ $missingCoords->count() }}</div>
            <div class="text-[10px] text-slate-500 font-bold mt-0.5">excluded from route</div>
        </div>
    </div>

    @if($amPlan['warning'] || $pmPlan['warning'])
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-[12px] text-amber-800 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
            <span><strong>Routing service unreachable</strong> — falling back to straight-line distance × 1.30. Check OSRM at <code>{{ env('OSRM_BASE_URL', 'https://router.project-osrm.org') }}</code>.</span>
        </div>
    @endif

    {{-- MAP --}}
    <div class="rt-section">
        <div class="rt-hd">
            <div class="rt-num am"><i data-lucide="map" class="w-3.5 h-3.5"></i></div>
            <div class="flex-1">
                <div class="rt-title">Map preview</div>
                <div class="rt-sub">AM in blue, PM in orange, start point in red — solid lines follow real roads</div>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider border {{ $sourceBadgeClass }}">
                <i data-lucide="{{ ($amPlan['source'] === 'osrm' || $pmPlan['source'] === 'osrm') ? 'navigation' : 'minus' }}" class="w-3 h-3"></i> {{ $sourceLabel }}
            </span>
        </div>
        <div class="rt-body">
            <div id="route-map"></div>
        </div>
    </div>

    {{-- AM + PM ROUTES --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- AM --}}
        <div class="rt-section">
            <div class="rt-hd">
                <div class="rt-num am"><i data-lucide="sun" class="w-3.5 h-3.5"></i></div>
                <div class="flex-1">
                    <div class="rt-title">Morning (before 12:00)</div>
                    <div class="rt-sub">{{ $amPlan['stops']->count() }} stops · {{ number_format($amPlan['miles'], 1) }} mi · {{ floor(($amPlan['duration_minutes'] ?? 0) / 60) }}h{{ str_pad((string) (($amPlan['duration_minutes'] ?? 0) % 60), 2, '0', STR_PAD_LEFT) }}m drive · ${{ number_format($amPlan['fuel_cost'], 2) }} fuel</div>
                </div>
            </div>
            <div class="rt-body">
                @forelse($amPlan['stops'] as $i => $stop)
                    <div class="stop-card">
                        <div class="stop-num am">{{ $i + 1 }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="stop-time">{{ $stop->scheduled_at->format('g:i A') }}</div>
                            <a href="{{ route('hhrr.patients.show', $stop->patient) }}" class="stop-name hover:text-emerald-600">{{ $stop->patient?->full_name ?? '—' }}</a>
                            <div class="stop-meta truncate">{{ $stop->patient?->address }} · {{ $stop->patient?->city }}, {{ $stop->patient?->state }}</div>
                            <div class="stop-leg">leg: {{ number_format($amPlan['leg_miles'][$i] ?? 0, 1) }} mi · {{ $stop->provider?->full_name ?? '—' }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 italic text-sm text-center py-6">No morning appointments for this date.</p>
                @endforelse
                @if($amPlan['stops']->count() > 0)
                    <div class="text-[11px] text-slate-500 mt-2 italic">+ return-to-start leg: {{ number_format(end($amPlan['leg_miles']), 1) }} mi</div>
                @endif
            </div>
        </div>

        {{-- PM --}}
        <div class="rt-section">
            <div class="rt-hd">
                <div class="rt-num pm"><i data-lucide="sunset" class="w-3.5 h-3.5"></i></div>
                <div class="flex-1">
                    <div class="rt-title">Afternoon (12:00 onward)</div>
                    <div class="rt-sub">{{ $pmPlan['stops']->count() }} stops · {{ number_format($pmPlan['miles'], 1) }} mi · {{ floor(($pmPlan['duration_minutes'] ?? 0) / 60) }}h{{ str_pad((string) (($pmPlan['duration_minutes'] ?? 0) % 60), 2, '0', STR_PAD_LEFT) }}m drive · ${{ number_format($pmPlan['fuel_cost'], 2) }} fuel</div>
                </div>
            </div>
            <div class="rt-body">
                @forelse($pmPlan['stops'] as $i => $stop)
                    <div class="stop-card">
                        <div class="stop-num pm">{{ $i + 1 }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="stop-time">{{ $stop->scheduled_at->format('g:i A') }}</div>
                            <a href="{{ route('hhrr.patients.show', $stop->patient) }}" class="stop-name hover:text-orange-600">{{ $stop->patient?->full_name ?? '—' }}</a>
                            <div class="stop-meta truncate">{{ $stop->patient?->address }} · {{ $stop->patient?->city }}, {{ $stop->patient?->state }}</div>
                            <div class="stop-leg">leg: {{ number_format($pmPlan['leg_miles'][$i] ?? 0, 1) }} mi · {{ $stop->provider?->full_name ?? '—' }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 italic text-sm text-center py-6">No afternoon appointments for this date.</p>
                @endforelse
                @if($pmPlan['stops']->count() > 0)
                    <div class="text-[11px] text-slate-500 mt-2 italic">+ return-to-start leg: {{ number_format(end($pmPlan['leg_miles']), 1) }} mi</div>
                @endif
            </div>
        </div>
    </div>

    @if($missingCoords->count() > 0)
        <div class="rt-section mt-4 border-amber-200" style="border-color:#fcd34d;">
            <div class="rt-hd" style="background:linear-gradient(180deg,#fffbeb,#fef3c7);">
                <div class="rt-num" style="background:#f59e0b;"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i></div>
                <div class="flex-1">
                    <div class="rt-title">Missing patient coordinates ({{ $missingCoords->count() }})</div>
                    <div class="rt-sub">Cannot include in optimized route — please geocode their address</div>
                </div>
            </div>
            <div class="rt-body">
                <ul class="text-[12px] space-y-1">
                    @foreach($missingCoords as $appt)
                        <li class="flex items-center gap-2">
                            <i data-lucide="map-pin-off" class="w-3.5 h-3.5 text-amber-500"></i>
                            <a href="{{ route('hhrr.patients.show', $appt->patient) }}" class="font-bold hover:text-amber-700">{{ $appt->patient?->full_name }}</a>
                            <span class="text-slate-400">— {{ $appt->scheduled_at->format('g:i A') }}</span>
                            <span class="text-slate-400">— {{ $appt->patient?->address }}, {{ $appt->patient?->city }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

{{-- Leaflet map (free, no API key, OpenStreetMap tiles) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') return;

    const map = L.map('route-map').setView([{{ $startLat }}, {{ $startLng }}], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors',
    }).addTo(map);

    const startIcon = L.divIcon({ html: '<div style="background:#dc2626;color:#fff;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);">⌂</div>', className:'', iconSize:[30,30], iconAnchor:[15,15] });
    L.marker([{{ $startLat }}, {{ $startLng }}], { icon: startIcon, title: 'Start: {{ $startingClinic?->name ?? 'custom' }}' })
        .addTo(map)
        .bindPopup('<strong>Start</strong><br>{{ $startingClinic?->name ?? 'Custom location' }}');

    const buildIcon = (n, color) => L.divIcon({
        html: `<div style="background:${color};color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:11px;border:2px solid #fff;box-shadow:0 2px 4px rgba(0,0,0,.25);">${n}</div>`,
        className: '', iconSize: [28, 28], iconAnchor: [14, 14],
    });

    const allPoints = [[{{ $startLat }}, {{ $startLng }}]];

    // AM stops
    @foreach($amPlan['stops'] as $i => $stop)
        L.marker([{{ $stop->patient->latitude }}, {{ $stop->patient->longitude }}], { icon: buildIcon({{ $i + 1 }}, '#3b82f6') })
            .addTo(map)
            .bindPopup(@json('AM #' . ($i + 1) . ' — ' . ($stop->patient?->full_name ?? '') . '<br>' . $stop->scheduled_at->format('g:i A')));
        allPoints.push([{{ $stop->patient->latitude }}, {{ $stop->patient->longitude }}]);
    @endforeach

    // AM road geometry from OSRM (or straight-line fallback)
    const amGeometry = @json($amPlan['geometry'] ?? []);
    const amSource   = @json($amPlan['source'] ?? '');
    if (amGeometry.length > 1) {
        const realRoad = amSource === 'osrm' || amSource === 'cache';
        L.polyline(amGeometry, realRoad
            ? { color: '#3b82f6', weight: 4, opacity: 0.8 }
            : { color: '#3b82f6', weight: 3, opacity: 0.5, dashArray: '4 6' }
        ).addTo(map);
    }

    // PM stops
    @foreach($pmPlan['stops'] as $i => $stop)
        L.marker([{{ $stop->patient->latitude }}, {{ $stop->patient->longitude }}], { icon: buildIcon({{ $i + 1 }}, '#ea580c') })
            .addTo(map)
            .bindPopup(@json('PM #' . ($i + 1) . ' — ' . ($stop->patient?->full_name ?? '') . '<br>' . $stop->scheduled_at->format('g:i A')));
        allPoints.push([{{ $stop->patient->latitude }}, {{ $stop->patient->longitude }}]);
    @endforeach

    // PM road geometry from OSRM
    const pmGeometry = @json($pmPlan['geometry'] ?? []);
    const pmSource   = @json($pmPlan['source'] ?? '');
    if (pmGeometry.length > 1) {
        const realRoad = pmSource === 'osrm' || pmSource === 'cache';
        L.polyline(pmGeometry, realRoad
            ? { color: '#ea580c', weight: 4, opacity: 0.8 }
            : { color: '#ea580c', weight: 3, opacity: 0.5, dashArray: '4 6' }
        ).addTo(map);
    }

    if (allPoints.length > 1) {
        map.fitBounds(L.latLngBounds(allPoints), { padding: [40, 40] });
    }
});
</script>
@endsection
