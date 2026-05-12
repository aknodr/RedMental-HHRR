<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $departments = Department::query()
            ->when($q !== '', fn ($qb) => $qb->where('name', 'like', "%{$q}%"))
            ->withCount('employees')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('hhrr.departments.index', compact('departments', 'q'));
    }

    public function create(): View
    {
        return view('hhrr.departments.form', ['department' => new Department()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validate($request);
        Department::create($data);
        return redirect()->route('hhrr.departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department): View
    {
        return view('hhrr.departments.form', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validate($request, $department->id));
        return redirect()->route('hhrr.departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'This department still has employees assigned.');
        }
        $department->delete();
        return redirect()->route('hhrr.departments.index')->with('status', 'Department deleted.');
    }

    private function validate(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120',
                Rule::unique('departments', 'name')
                    ->ignore($ignoreId)
                    ->where(fn ($q) => $q->where('client_id', auth()->user()->client_id))],
            'code'        => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'active'      => ['sometimes', 'boolean'],
        ]);
        $data['active'] = $request->boolean('active', true);
        return $data;
    }
}
