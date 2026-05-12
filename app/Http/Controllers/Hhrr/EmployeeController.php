<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Department;
use App\Models\Hhrr\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $department = $request->query('department');
        $status = $request->query('status');

        $employees = Employee::query()
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%{$q}%")
                  ->orWhere('last_name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('employee_number', 'like', "%{$q}%");
            }))
            ->when($department, fn ($qb) => $qb->where('department_id', $department))
            ->when($status === 'active',   fn ($qb) => $qb->where('active', true))
            ->when($status === 'inactive', fn ($qb) => $qb->where('active', false))
            ->with('department')
            ->orderBy('last_name')->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('hhrr.employees.index', [
            'employees'   => $employees,
            'departments' => Department::orderBy('name')->get(),
            'q'           => $q,
            'department'  => $department,
            'status'      => $status,
        ]);
    }

    public function create(): View
    {
        return view('hhrr.employees.form', [
            'employee'    => new Employee(['active' => true]),
            'departments' => Department::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Employee::create($this->validated($request));
        return redirect()->route('hhrr.employees.index')->with('status', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        return view('hhrr.employees.show', ['employee' => $employee->load('department', 'contracts')]);
    }

    public function edit(Employee $employee): View
    {
        return view('hhrr.employees.form', [
            'employee'    => $employee,
            'departments' => Department::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validated($request, $employee->id));
        return redirect()->route('hhrr.employees.show', $employee)->with('status', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();
        return redirect()->route('hhrr.employees.index')->with('status', 'Employee deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        foreach (['department_id', 'date_of_birth', 'hire_date', 'termination_date'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'department_id'            => ['nullable', 'exists:departments,id'],
            'employee_number'          => ['nullable', 'string', 'max:30',
                Rule::unique('employees', 'employee_number')
                    ->ignore($ignoreId)
                    ->where(fn ($q) => $q->where('client_id', auth()->user()->client_id))],
            'npi'                      => ['nullable', 'digits:10'],
            'first_name'               => ['required', 'string', 'max:100'],
            'last_name'                => ['required', 'string', 'max:100'],
            'email'                    => ['nullable', 'email', 'max:200'],
            'phone'                    => ['nullable', 'string', 'max:50'],
            'date_of_birth'            => ['nullable', 'date'],
            'gender'                   => ['nullable', 'string', 'max:10'],
            'position'                 => ['nullable', 'string', 'max:150'],
            'is_provider'              => ['sometimes', 'boolean'],
            'hourly_rate'              => ['nullable', 'numeric', 'min:0'],
            'salary'                   => ['nullable', 'numeric', 'min:0'],
            'hire_date'                => ['nullable', 'date'],
            'termination_date'         => ['nullable', 'date', 'after_or_equal:hire_date'],
            'address'                  => ['nullable', 'string', 'max:200'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'state'                    => ['nullable', 'string', 'size:2'],
            'zip'                      => ['nullable', 'string', 'max:10'],
            'emergency_contact_name'   => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone'  => ['nullable', 'string', 'max:50'],
            'notes'                    => ['nullable', 'string'],
            'active'                   => ['sometimes', 'boolean'],
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['is_provider'] = $request->boolean('is_provider', false);
        return $data;
    }
}
