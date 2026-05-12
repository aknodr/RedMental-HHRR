<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $clinics = Clinic::query()
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%")
                  ->orWhere('city', 'like', "%{$q}%");
            }))
            ->withCount('patients')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('hhrr.clinics.index', compact('clinics', 'q'));
    }

    public function create(): View
    {
        return view('hhrr.clinics.form', ['clinic' => new Clinic(['active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Clinic::create($this->validated($request));
        return redirect()->route('hhrr.clinics.index')->with('status', 'Clinic created.');
    }

    public function show(Clinic $clinic): View
    {
        return view('hhrr.clinics.show', [
            'clinic' => $clinic->load(['patients' => fn ($q) => $q->orderBy('last_name')]),
        ]);
    }

    public function edit(Clinic $clinic): View
    {
        return view('hhrr.clinics.form', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic): RedirectResponse
    {
        $clinic->update($this->validated($request, $clinic->id));
        return redirect()->route('hhrr.clinics.index')->with('status', 'Clinic updated.');
    }

    public function destroy(Clinic $clinic): RedirectResponse
    {
        if ($clinic->patients()->exists()) {
            return back()->with('error', 'This clinic still has patients enrolled.');
        }
        $clinic->delete();
        return redirect()->route('hhrr.clinics.index')->with('status', 'Clinic deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:150',
                Rule::unique('clinics', 'name')
                    ->ignore($ignoreId)
                    ->where(fn ($q) => $q->where('client_id', auth()->user()->client_id))],
            'code'    => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:200'],
            'city'    => ['nullable', 'string', 'max:100'],
            'state'   => ['nullable', 'string', 'size:2'],
            'zip'     => ['nullable', 'string', 'max:10'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:200'],
            'active'  => ['sometimes', 'boolean'],
        ]);
        $data['active'] = $request->boolean('active', true);
        return $data;
    }
}
