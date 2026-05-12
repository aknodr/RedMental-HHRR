@extends('layouts.app')

@section('title', $patient->exists ? 'Edit patient' : 'New patient')

@section('content')
    <a href="{{ route('hhrr.patients.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to patients
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $patient->exists ? 'Edit patient' : 'New patient' }}</h1>
    @include('hhrr._shared._flash')

    @php($selectedClinics = $patient->exists ? $patient->clinics->pluck('id')->all() : [])
    <form method="POST" action="{{ $patient->exists ? route('hhrr.patients.update', $patient) : route('hhrr.patients.store') }}"
          x-data="{ insurances: @js($patient->exists ? $patient->insurances->toArray() : []) }">
        @csrf
        @if($patient->exists) @method('PUT') @endif

        {{-- Identity --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i> Identity
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">First name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Middle</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $patient->middle_name) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Last name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">MRN</label>
                    <input type="text" name="mrn" value="{{ old('mrn', $patient->mrn) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date of birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($patient->date_of_birth)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Gender</label>
                    <select name="gender" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['' => '—', 'Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('gender', $patient->gender) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">SSN</label>
                    <input type="text" name="ssn" value="{{ old('ssn', $patient->ssn) }}" maxlength="15" class="ssn-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Preferred language</label>
                    <input type="text" name="preferred_language" value="{{ old('preferred_language', $patient->preferred_language) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Intake date</label>
                    <input type="date" name="intake_date" value="{{ old('intake_date', optional($patient->intake_date)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-emerald-600"></i> Contact &amp; address
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label><input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="phone-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $patient->email) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div class="md:col-span-2 relative">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
                    <input type="text" name="address" id="patient_address" value="{{ old('address', $patient->address) }}"
                           class="addr-input w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           data-suggestions-id="patient_addr_suggestions"
                           data-city-id="patient_city" data-state-id="patient_state" data-zip-id="patient_zip"
                           placeholder="Start typing address…">
                    <div id="patient_addr_suggestions" class="addr-suggestions" style="display:none;"></div>
                </div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">City</label><input type="text" id="patient_city" name="city" value="{{ old('city', $patient->city) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">State</label><input type="text" id="patient_state" name="state" maxlength="2" value="{{ old('state', $patient->state) }}" class="state-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">ZIP</label><input type="text" id="patient_zip" name="zip" value="{{ old('zip', $patient->zip) }}" class="zip-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                </div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Emergency contact</label><input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Emergency phone</label><input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" class="phone-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
            </div>
        </div>

        {{-- Care assignment --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-4 h-4 text-rose-600"></i> Care assignment
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned provider (doctor)</label>
                    <select name="assigned_provider_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Unassigned —</option>
                        @foreach($providers as $prov)
                            <option value="{{ $prov->id }}" @selected(old('assigned_provider_id', $patient->assigned_provider_id) == $prov->id)>
                                {{ $prov->full_name }}@if($prov->position) — {{ $prov->position }}@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Only employees flagged as providers appear here.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Enrolled in clinics</label>
                    <div class="border border-slate-300 rounded-lg p-3 max-h-40 overflow-y-auto space-y-1.5 bg-white">
                        @forelse($clinics as $clinic)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="clinics[]" value="{{ $clinic->id }}"
                                       @checked(in_array($clinic->id, old('clinics', $selectedClinics)))
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $clinic->name }}@if($clinic->city) <span class="text-xs text-slate-400">— {{ $clinic->city }}</span>@endif
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 italic">No clinics defined yet — create one first.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Insurance rows (Alpine dynamic) --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-amber-600"></i> Insurance
                </h3>
                <button type="button" @click="insurances.push({payer_id:'', priority:'primary', policy_number:'', group_number:'', subscriber_name:'', subscriber_relationship:'self', effective_date:'', termination_date:''})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add insurance
                </button>
            </div>

            <template x-for="(ins, idx) in insurances" :key="idx">
                <div class="border border-slate-200 rounded-lg p-4 mb-3 relative bg-slate-50/30">
                    <button type="button" @click="insurances.splice(idx, 1)" class="absolute top-2 right-2 p-1 text-slate-400 hover:text-rose-600" title="Remove">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Payer *</label>
                            <select :name="`insurances[${idx}][payer_id]`" x-model="ins.payer_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Select payer —</option>
                                @foreach($payers as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Priority *</label>
                            <select :name="`insurances[${idx}][priority]`" x-model="ins.priority" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="tertiary">Tertiary</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Policy #</label>
                            <input type="text" :name="`insurances[${idx}][policy_number]`" x-model="ins.policy_number" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Group #</label>
                            <input type="text" :name="`insurances[${idx}][group_number]`" x-model="ins.group_number" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Subscriber name</label>
                            <input type="text" :name="`insurances[${idx}][subscriber_name]`" x-model="ins.subscriber_name" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Relationship</label>
                            <select :name="`insurances[${idx}][subscriber_relationship]`" x-model="ins.subscriber_relationship" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="self">Self</option>
                                <option value="spouse">Spouse</option>
                                <option value="child">Child</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Effective date</label>
                            <input type="date" :name="`insurances[${idx}][effective_date]`" x-model="ins.effective_date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Termination date</label>
                            <input type="date" :name="`insurances[${idx}][termination_date]`" x-model="ins.termination_date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="insurances.length === 0">
                <p class="text-sm text-slate-400 italic">No insurance records yet.</p>
            </template>
        </div>

        {{-- Notes + status --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <div class="mb-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $patient->notes) }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="active" value="1" @checked(old('active', $patient->active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Patient is active
            </label>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hhrr.patients.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="save" class="w-4 h-4"></i> {{ $patient->exists ? 'Save changes' : 'Create' }}
            </button>
        </div>
    </form>

    @include('hhrr._shared._form_helpers')
@endsection
