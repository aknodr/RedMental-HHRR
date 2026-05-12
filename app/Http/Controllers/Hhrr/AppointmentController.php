<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Appointment;
use App\Models\Hhrr\Clinic;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->query('month')
            ? \Carbon\Carbon::parse($request->query('month') . '-01')
            : now()->startOfMonth();

        // Calendar grid for the month, padded to start on Sunday and end on Saturday
        $start = $month->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $end   = $month->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);

        $appointments = Appointment::query()
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['patient', 'provider', 'clinic'])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn ($a) => $a->scheduled_at->toDateString());

        // Build a flat list of dates for the grid
        $days = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $days[] = $cur->copy();
            $cur->addDay();
        }

        return view('hhrr.appointments.index', [
            'month'        => $month,
            'days'         => $days,
            'appointments' => $appointments,
            'providers'    => Employee::where('active', true)->where('is_provider', true)->orderBy('last_name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $defaultDate = $request->query('date');
        $defaultProvider = $request->query('provider_id');

        return view('hhrr.appointments.form', [
            'appointment' => new Appointment([
                'scheduled_at'     => $defaultDate ? \Carbon\Carbon::parse($defaultDate)->setTime(9, 0) : now()->addDay()->setTime(9, 0),
                'provider_id'      => $defaultProvider,
                'duration_minutes' => 45,
                'status'           => 'scheduled',
            ]),
            'patients'  => Patient::where('active', true)->orderBy('last_name')->get(),
            'providers' => Employee::where('active', true)->where('is_provider', true)->orderBy('last_name')->get(),
            'clinics'   => Clinic::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->enforceDailyCap($data['provider_id'], $data['scheduled_at']);

        Appointment::create($data);
        return redirect()->route('hhrr.appointments.index', ['month' => substr($data['scheduled_at'], 0, 7)])
            ->with('status', 'Appointment scheduled.');
    }

    public function edit(Appointment $appointment): View
    {
        return view('hhrr.appointments.form', [
            'appointment' => $appointment,
            'patients'    => Patient::where('active', true)->orderBy('last_name')->get(),
            'providers'   => Employee::where('active', true)->where('is_provider', true)->orderBy('last_name')->get(),
            'clinics'     => Clinic::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $this->validated($request);
        if ($data['status'] !== 'cancelled') {
            $this->enforceDailyCap($data['provider_id'], $data['scheduled_at'], $appointment->id);
        }

        $appointment->update($data);
        return redirect()->route('hhrr.appointments.index', ['month' => substr($data['scheduled_at'], 0, 7)])
            ->with('status', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();
        return back()->with('status', 'Appointment removed.');
    }

    private function validated(Request $request): array
    {
        if ($request->input('clinic_id') === '') $request->merge(['clinic_id' => null]);

        return $request->validate([
            'patient_id'       => ['required', 'exists:patients,id'],
            'provider_id'      => ['required', 'exists:employees,id'],
            'clinic_id'        => ['nullable', 'exists:clinics,id'],
            'scheduled_at'     => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'status'           => ['required', Rule::in(array_keys(Appointment::STATUSES))],
            'reason'           => ['nullable', 'string', 'max:200'],
            'notes'            => ['nullable', 'string'],
        ]);
    }

    private function enforceDailyCap(int $providerId, string $scheduledAt, ?int $excludeId = null): void
    {
        $date  = \Carbon\Carbon::parse($scheduledAt);
        $count = Appointment::countForProviderOnDate($providerId, $date, $excludeId);
        if ($count >= Appointment::MAX_PER_PROVIDER_PER_DAY) {
            $provider = Employee::find($providerId);
            throw ValidationException::withMessages([
                'provider_id' => sprintf(
                    'Provider %s already has %d appointments on %s — Florida law caps at %d per day.',
                    $provider?->full_name ?? '#'.$providerId,
                    $count,
                    $date->format('M j, Y'),
                    Appointment::MAX_PER_PROVIDER_PER_DAY,
                ),
            ]);
        }
    }
}
