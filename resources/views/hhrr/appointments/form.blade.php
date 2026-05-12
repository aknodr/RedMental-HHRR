@extends('layouts.app')

@section('title', $appointment->exists ? 'Edit appointment' : 'New appointment')

@section('content')
    <a href="{{ route('hhrr.appointments.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to calendar
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $appointment->exists ? 'Edit appointment' : 'New appointment' }}</h1>

    <form method="POST" action="{{ $appointment->exists ? route('hhrr.appointments.update', $appointment) : route('hhrr.appointments.store') }}"
          class="bg-white rounded-xl border border-slate-200 p-6 max-w-2xl space-y-4">
        @csrf
        @if($appointment->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Patient <span class="text-rose-500">*</span></label>
                <select name="patient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select patient —</option>
                    @foreach($patients as $pat)
                        <option value="{{ $pat->id }}" @selected(old('patient_id', $appointment->patient_id) == $pat->id)>
                            {{ $pat->full_name }}@if($pat->mrn) — {{ $pat->mrn }}@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Provider <span class="text-rose-500">*</span></label>
                <select name="provider_id" id="provider_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select provider —</option>
                    @foreach($providers as $prov)
                        <option value="{{ $prov->id }}" @selected(old('provider_id', $appointment->provider_id) == $prov->id)>
                            {{ $prov->full_name }}@if($prov->position) — {{ $prov->position }}@endif
                        </option>
                    @endforeach
                </select>
                <div id="provider_load" class="mt-1 text-[10px]"></div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Clinic</label>
                <select name="clinic_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">—</option>
                    @foreach($clinics as $cl)
                        <option value="{{ $cl->id }}" @selected(old('clinic_id', $appointment->clinic_id) == $cl->id)>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date &amp; time <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" required
                       value="{{ old('scheduled_at', optional($appointment->scheduled_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="5" max="480"
                       value="{{ old('duration_minutes', $appointment->duration_minutes ?? 45) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\Hhrr\Appointment::STATUSES as $k => $v)
                        <option value="{{ $k }}" @selected(old('status', $appointment->status ?? 'scheduled') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Reason</label>
                <input type="text" name="reason" maxlength="200" value="{{ old('reason', $appointment->reason) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Brief reason for visit">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $appointment->notes) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <a href="{{ route('hhrr.appointments.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Cancel</a>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">{{ $appointment->exists ? 'Save changes' : 'Schedule' }}</button>
        </div>
    </form>

    <script>
    // Live provider-load hint when picking provider + date
    const providerLoadEl = document.getElementById('provider_load');
    const updateLoad = () => {
        const provId = document.getElementById('provider_id').value;
        const dt     = document.getElementById('scheduled_at').value;
        if (!provId || !dt) { providerLoadEl.innerHTML = ''; return; }
        // Hint client-side: just show the cap. Real enforcement is on the server.
        providerLoadEl.innerHTML = `<span class="text-slate-500">Florida cap: max <b>20</b> patients/provider/day. The server will reject if exceeded.</span>`;
    };
    document.getElementById('provider_id').addEventListener('change', updateLoad);
    document.getElementById('scheduled_at').addEventListener('change', updateLoad);
    updateLoad();
    </script>
@endsection
