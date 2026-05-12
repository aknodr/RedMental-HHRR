<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Appointment;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Payroll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $payrolls = Payroll::query()
            ->with('employee')
            ->orderByDesc('period_end')
            ->paginate(20);

        return view('hhrr.payroll.index', [
            'payrolls'    => $payrolls,
            'frequencies' => Payroll::FREQUENCIES,
        ]);
    }

    public function generate(): View
    {
        return view('hhrr.payroll.generate', [
            'employees'   => Employee::where('active', true)->orderBy('last_name')->get(),
            'frequencies' => Payroll::FREQUENCIES,
            'defaults'    => $this->defaultPeriod('bi_weekly'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'frequency'    => ['required', Rule::in(array_keys(Payroll::FREQUENCIES))],
            'period_start' => ['required', 'date'],
            'period_end'   => ['required', 'date', 'after_or_equal:period_start'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['exists:employees,id'],
            'per_patient_bonus' => ['nullable', 'numeric', 'min:0'],
        ]);

        $bonus = (float) ($data['per_patient_bonus'] ?? 0);
        $created = 0;

        DB::transaction(function () use ($data, $bonus, &$created) {
            foreach ($data['employee_ids'] as $empId) {
                $emp = Employee::find($empId);
                if (! $emp) continue;

                // Estimate hours: try the appointments table; fall back to 0 (user can edit)
                $patients = Appointment::where('provider_id', $emp->id)
                    ->whereBetween('scheduled_at', [$data['period_start'], $data['period_end']])
                    ->whereIn('status', ['scheduled', 'completed'])
                    ->count();
                $minutes = Appointment::where('provider_id', $emp->id)
                    ->whereBetween('scheduled_at', [$data['period_start'], $data['period_end']])
                    ->whereIn('status', ['scheduled', 'completed'])
                    ->sum('duration_minutes');
                $hours = round($minutes / 60, 2);

                // Look up by employee + period (date-only comparison to avoid
                // datetime format drift between insert and lookup in SQLite).
                $existing = Payroll::where('employee_id', $emp->id)
                    ->whereDate('period_start', $data['period_start'])
                    ->whereDate('period_end',   $data['period_end'])
                    ->first();

                $attrs = [
                    'employee_id'       => $emp->id,
                    'period_start'      => $data['period_start'],
                    'period_end'        => $data['period_end'],
                    'frequency'         => $data['frequency'],
                    'hours_worked'      => $hours,
                    'hourly_rate'       => (float) ($emp->hourly_rate ?? 0),
                    'per_patient_bonus' => $bonus,
                    'patients_seen'     => $patients,
                    'status'            => 'draft',
                ];

                if ($existing) {
                    $existing->fill($attrs);
                    $payroll = $existing;
                } else {
                    $payroll = new Payroll($attrs);
                }
                $payroll->recalculate();
                $payroll->save();
                $created++;
            }
        });

        return redirect()->route('hhrr.payroll.index')->with('status', "Generated {$created} payroll record(s).");
    }

    public function edit(Payroll $payroll): View
    {
        return view('hhrr.payroll.edit', [
            'payroll'  => $payroll->load('employee'),
            'statuses' => Payroll::STATUSES,
        ]);
    }

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'hours_worked'      => ['required', 'numeric', 'min:0'],
            'hourly_rate'       => ['required', 'numeric', 'min:0'],
            'per_patient_bonus' => ['nullable', 'numeric', 'min:0'],
            'patients_seen'     => ['nullable', 'integer', 'min:0'],
            'deductions'        => ['nullable', 'numeric', 'min:0'],
            'status'            => ['required', Rule::in(array_keys(Payroll::STATUSES))],
            'notes'             => ['nullable', 'string'],
        ]);

        $payroll->fill($data);
        $payroll->recalculate();
        $payroll->save();

        return redirect()->route('hhrr.payroll.index')->with('status', 'Payroll updated.');
    }

    public function destroy(Payroll $payroll): RedirectResponse
    {
        $payroll->delete();
        return back()->with('status', 'Payroll record removed.');
    }

    /** Default period boundaries based on frequency. */
    public static function defaultPeriod(string $frequency): array
    {
        if ($frequency === 'monthly') {
            return [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end'   => now()->endOfMonth()->toDateString(),
            ];
        }
        // bi-weekly: last 14 days ending today
        return [
            'period_start' => now()->subDays(13)->toDateString(),
            'period_end'   => now()->toDateString(),
        ];
    }
}
