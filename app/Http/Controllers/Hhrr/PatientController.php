<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Clinic;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Patient;
use App\Models\Hhrr\Payer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $clinicId = $request->query('clinic_id');
        $providerId = $request->query('provider_id');

        $patients = Patient::query()
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%{$q}%")
                  ->orWhere('last_name', 'like', "%{$q}%")
                  ->orWhere('mrn', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            }))
            ->when($status === 'active',   fn ($qb) => $qb->where('active', true))
            ->when($status === 'inactive', fn ($qb) => $qb->where('active', false))
            ->when($clinicId, fn ($qb) => $qb->whereHas('clinics', fn ($c) => $c->where('clinics.id', $clinicId)))
            ->when($providerId, fn ($qb) => $qb->where('assigned_provider_id', $providerId))
            ->with(['insurances.payer', 'assignedProvider', 'clinics'])
            ->orderBy('last_name')->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('hhrr.patients.index', [
            'patients'   => $patients,
            'q'          => $q,
            'status'     => $status,
            'clinics'    => Clinic::where('active', true)->orderBy('name')->get(),
            'providers'  => Employee::where('active', true)->where('is_provider', true)->orderBy('last_name')->get(),
            'clinicId'   => $clinicId,
            'providerId' => $providerId,
        ]);
    }

    public function create(): View
    {
        return view('hhrr.patients.form', [
            'patient'   => new Patient(['active' => true]),
            'payers'    => Payer::where('active', true)->orderBy('name')->get(),
            'clinics'   => Clinic::where('active', true)->orderBy('name')->get(),
            'providers' => Employee::where('active', true)->where('is_provider', true)->orderBy('last_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $patient = DB::transaction(function () use ($data) {
            $patient = Patient::create($data['patient']);
            foreach ($data['insurances'] as $ins) {
                $patient->insurances()->create($ins);
            }
            $patient->clinics()->sync($data['clinics']);
            return $patient;
        });

        return redirect()->route('hhrr.patients.show', $patient)->with('status', 'Patient created.');
    }

    public function show(Patient $patient): View
    {
        return view('hhrr.patients.show', [
            'patient' => $patient->load(['insurances.payer', 'contracts', 'assignedProvider', 'clinics']),
        ]);
    }

    public function edit(Patient $patient): View
    {
        return view('hhrr.patients.form', [
            'patient'   => $patient->load(['insurances', 'clinics']),
            'payers'    => Payer::where('active', true)->orderBy('name')->get(),
            'clinics'   => Clinic::where('active', true)->orderBy('name')->get(),
            'providers' => Employee::where('active', true)->where('is_provider', true)->orderBy('last_name')->get(),
        ]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $this->validated($request, $patient->id);

        DB::transaction(function () use ($data, $patient) {
            $patient->update($data['patient']);
            $patient->insurances()->delete();
            foreach ($data['insurances'] as $ins) {
                $patient->insurances()->create($ins);
            }
            $patient->clinics()->sync($data['clinics']);
        });

        return redirect()->route('hhrr.patients.show', $patient)->with('status', 'Patient updated.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->delete();
        return redirect()->route('hhrr.patients.index')->with('status', 'Patient deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        // Empty-string selects must become null so FK constraints don't fail.
        foreach (['assigned_provider_id', 'date_of_birth', 'intake_date'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }
        // Drop blank insurance rows the user added then never filled.
        $insurances = collect($request->input('insurances', []))
            ->filter(fn ($row) => is_array($row) && !empty($row['payer_id']))
            ->values()
            ->all();
        $request->merge(['insurances' => $insurances]);

        $v = $request->validate([
            'mrn'                     => ['nullable', 'string', 'max:30',
                Rule::unique('patients', 'mrn')
                    ->ignore($ignoreId)
                    ->where(fn ($q) => $q->where('client_id', auth()->user()->client_id))],
            'first_name'              => ['required', 'string', 'max:100'],
            'last_name'               => ['required', 'string', 'max:100'],
            'middle_name'             => ['nullable', 'string', 'max:100'],
            'date_of_birth'           => ['nullable', 'date'],
            'gender'                  => ['nullable', 'string', 'max:10'],
            'ssn'                     => ['nullable', 'string', 'max:15'],
            'phone'                   => ['nullable', 'string', 'max:50'],
            'email'                   => ['nullable', 'email', 'max:200'],
            'address'                 => ['nullable', 'string', 'max:200'],
            'city'                    => ['nullable', 'string', 'max:100'],
            'state'                   => ['nullable', 'string', 'size:2'],
            'zip'                     => ['nullable', 'string', 'max:10'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'preferred_language'      => ['nullable', 'string', 'max:30'],
            'intake_date'             => ['nullable', 'date'],
            'notes'                   => ['nullable', 'string'],
            'active'                  => ['sometimes', 'boolean'],
            'assigned_provider_id'    => ['nullable', 'exists:employees,id'],

            'clinics'   => ['array'],
            'clinics.*' => ['integer', 'exists:clinics,id'],

            'insurances'                           => ['array'],
            'insurances.*.payer_id'                => ['required', 'exists:payers,id'],
            'insurances.*.priority'                => ['required', Rule::in(['primary', 'secondary', 'tertiary'])],
            'insurances.*.policy_number'           => ['nullable', 'string', 'max:50'],
            'insurances.*.group_number'            => ['nullable', 'string', 'max:50'],
            'insurances.*.subscriber_name'         => ['nullable', 'string', 'max:200'],
            'insurances.*.subscriber_relationship' => ['nullable', Rule::in(['self', 'spouse', 'child', 'other'])],
            'insurances.*.effective_date'          => ['nullable', 'date'],
            'insurances.*.termination_date'        => ['nullable', 'date'],
        ]);

        $patient = collect($v)->except(['insurances', 'clinics'])->toArray();
        $patient['active'] = $request->boolean('active', true);

        return [
            'patient'    => $patient,
            'insurances' => array_values($v['insurances'] ?? []),
            'clinics'    => $v['clinics'] ?? [],
        ];
    }
}
