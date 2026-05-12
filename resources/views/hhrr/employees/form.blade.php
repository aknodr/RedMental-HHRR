@extends('layouts.app')

@section('title', $employee->exists ? 'Edit employee' : 'New employee')

@section('content')
    <a href="{{ route('hhrr.employees.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to employees
    </a>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $employee->exists ? 'Edit employee' : 'New employee' }}</h1>
    @include('hhrr._shared._flash')

    <form method="POST" action="{{ $employee->exists ? route('hhrr.employees.update', $employee) : route('hhrr.employees.store') }}">
        @csrf
        @if($employee->exists) @method('PUT') @endif

        {{-- Identity --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i> Identity
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">First name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Last name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Employee #</label>
                    <input type="text" name="employee_number" value="{{ old('employee_number', $employee->employee_number) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date of birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Gender</label>
                    <select name="gender" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['' => '—', 'Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('gender', $employee->gender) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Employment --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="briefcase" class="w-4 h-4 text-amber-600"></i> Employment
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">—</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(old('department_id', $employee->department_id) == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Position</label>
                    <input type="text" name="position" value="{{ old('position', $employee->position) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        NPI <span class="text-slate-400 text-[10px]">(10 digits, providers only)</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="npi_input" name="npi" value="{{ old('npi', $employee->npi) }}" maxlength="10" pattern="\d{10}"
                               class="npi-mask flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" id="npi_lookup_btn"
                                class="inline-flex items-center gap-1 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg whitespace-nowrap">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i> Lookup NPI
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Free CMS NPPES registry — auto-fills name, address, phone & specialty.</p>
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" id="is_provider" name="is_provider" value="1" @checked(old('is_provider', $employee->is_provider))
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_provider" class="text-sm text-slate-700">This employee is a clinical provider (eligible to be assigned patients)</label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Hire date</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', optional($employee->hire_date)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Termination date</label>
                    <input type="date" name="termination_date" value="{{ old('termination_date', optional($employee->termination_date)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Hourly rate</label>
                    <input type="number" step="0.01" name="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Annual salary</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', $employee->salary) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-emerald-600"></i> Contact &amp; address
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label><input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="phone-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div class="md:col-span-2 relative">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
                    <input type="text" id="emp_address" name="address" value="{{ old('address', $employee->address) }}"
                           class="addr-input w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           data-suggestions-id="emp_addr_suggestions"
                           data-city-id="emp_city" data-state-id="emp_state" data-zip-id="emp_zip"
                           placeholder="Start typing address…">
                    <div id="emp_addr_suggestions" class="addr-suggestions" style="display:none;"></div>
                </div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">City</label><input type="text" id="emp_city" name="city" value="{{ old('city', $employee->city) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">State</label><input type="text" id="emp_state" name="state" maxlength="2" value="{{ old('state', $employee->state) }}" class="state-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">ZIP</label><input type="text" id="emp_zip" name="zip" value="{{ old('zip', $employee->zip) }}" class="zip-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                </div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Emergency contact</label><input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-semibold text-slate-600 mb-1">Emergency phone</label><input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" class="phone-mask w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                <div class="md:col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $employee->notes) }}</textarea></div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="active" value="1" @checked(old('active', $employee->active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Employee is active
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hhrr.employees.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                <i data-lucide="save" class="w-4 h-4"></i> {{ $employee->exists ? 'Save changes' : 'Create' }}
            </button>
        </div>
    </form>

    @include('hhrr._shared._form_helpers')

    <script>
    document.getElementById('npi_lookup_btn')?.addEventListener('click', async () => {
        const input = document.getElementById('npi_input');
        const npi = (input.value || '').replace(/\D/g, '');
        if (npi.length !== 10) {
            return Swal.fire({ icon: 'warning', title: 'Invalid NPI', text: 'NPI must be 10 digits.' });
        }

        Swal.fire({ title: 'Looking up NPI…', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
        try {
            const url = `https://npiregistry.cms.hhs.gov/api/?version=2.1&number=${npi}&pretty=on`;
            const res = await fetch(url);
            const json = await res.json();
            const result = (json.results || [])[0];
            if (!result) {
                Swal.close();
                return Swal.fire({ icon: 'info', title: 'No match', text: 'NPI not found in the CMS NPPES registry.' });
            }

            const setVal = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
            const setName = (name, val) => { const el = document.getElementsByName(name)[0]; if (el && val) el.value = val; };

            // Basic fields
            const basic = result.basic || {};
            setName('first_name', titleCase(basic.first_name));
            setName('last_name',  titleCase(basic.last_name));
            if (basic.gender) setName('gender', basic.gender === 'M' ? 'Male' : (basic.gender === 'F' ? 'Female' : 'Other'));

            // Primary taxonomy → position
            const primaryTax = (result.taxonomies || []).find(t => t.primary) || (result.taxonomies || [])[0];
            if (primaryTax?.desc) setName('position', primaryTax.desc);

            // Practice address
            const practice = (result.addresses || []).find(a => a.address_purpose === 'LOCATION') || (result.addresses || [])[0];
            if (practice) {
                setVal('emp_address', titleCase(practice.address_1 || ''));
                setVal('emp_city',    titleCase(practice.city || ''));
                setVal('emp_state',   (practice.state || '').toUpperCase().slice(0, 2));
                setVal('emp_zip',     practice.postal_code || '');
                setName('phone',      formatPhone(practice.telephone_number || ''));
            }

            // Mark as provider
            const cb = document.getElementById('is_provider');
            if (cb) cb.checked = true;

            Swal.fire({ icon: 'success', title: 'NPI matched', html:
                `<div class="text-left text-sm">
                   <div><strong>${(basic.first_name||'')} ${(basic.last_name||'')}</strong>${basic.credential ? ', '+basic.credential : ''}</div>
                   <div class="text-slate-500 text-xs mt-1">${primaryTax?.desc || ''}</div>
                 </div>`, timer: 2200, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Lookup failed', text: 'Could not reach the NPPES registry.' });
        }

        function titleCase(s) { return (s||'').toLowerCase().replace(/\b(\w)/g, c => c.toUpperCase()); }
        function formatPhone(s) {
            const d = (s||'').replace(/\D/g, '').slice(-10);
            return d.length === 10 ? `(${d.slice(0,3)}) ${d.slice(3,6)}-${d.slice(6)}` : (s || '');
        }
    });
    </script>
@endsection
