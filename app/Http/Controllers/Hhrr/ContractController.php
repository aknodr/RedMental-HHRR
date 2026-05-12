<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Contract;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $q      = trim((string) $request->query('q', ''));
        $type   = $request->query('type');
        $status = $request->query('status'); // includes "expiring", "expired" and storage values

        $base = Contract::query()
            ->when($q !== '', fn ($qb) => $qb->where('title', 'like', "%{$q}%"))
            ->when($type, fn ($qb) => $qb->where('type', $type))
            ->with(['employee', 'patient']);

        $contracts = (clone $base)
            ->when($status === 'expiring', fn ($qb) => $qb->expiringSoon())
            ->when($status === 'expired',  fn ($qb) => $qb->expired())
            ->when($status === 'active',   fn ($qb) => $qb->active())
            ->when($status && !in_array($status, ['expiring', 'expired', 'active'], true),
                   fn ($qb) => $qb->where('status', $status))
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        // Counts for the tab strip
        $counts = [
            'all'      => (clone $base)->count(),
            'active'   => (clone $base)->active()->count(),
            'expiring' => (clone $base)->expiringSoon()->count(),
            'expired'  => (clone $base)->expired()->count(),
            'draft'    => (clone $base)->where('status', 'draft')->count(),
        ];

        return view('hhrr.contracts.index', [
            'contracts' => $contracts,
            'types'     => Contract::TYPES,
            'statuses'  => Contract::STATUSES,
            'q'         => $q,
            'type'      => $type,
            'status'    => $status,
            'counts'    => $counts,
        ]);
    }

    public function create(): View
    {
        return view('hhrr.contracts.form', [
            'contract'  => new Contract(['type' => 'employment', 'status' => 'draft']),
            'employees' => Employee::where('active', true)->orderBy('last_name')->get(),
            'patients'  => Patient::where('active', true)->orderBy('last_name')->get(),
            'types'     => Contract::TYPES,
            'statuses'  => Contract::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (!empty($data['end_date']) && \Carbon\Carbon::parse($data['end_date'])->isPast() && in_array($data['status'], ['active', 'draft'], true)) {
            $data['status'] = 'expired';
        }
        Contract::create($data);
        return redirect()->route('hhrr.contracts.index')->with('status', 'Contract created.');
    }

    public function edit(Contract $contract): View
    {
        return view('hhrr.contracts.form', [
            'contract'  => $contract,
            'employees' => Employee::where('active', true)->orderBy('last_name')->get(),
            'patients'  => Patient::where('active', true)->orderBy('last_name')->get(),
            'types'     => Contract::TYPES,
            'statuses'  => Contract::STATUSES,
        ]);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $data = $this->validated($request);
        if (!empty($data['end_date']) && \Carbon\Carbon::parse($data['end_date'])->isPast() && in_array($data['status'], ['active', 'draft'], true)) {
            $data['status'] = 'expired';
        }
        $contract->update($data);
        return redirect()->route('hhrr.contracts.index')->with('status', 'Contract updated.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();
        return redirect()->route('hhrr.contracts.index')->with('status', 'Contract deleted.');
    }

    private function validated(Request $request): array
    {
        foreach (['employee_id', 'patient_id', 'start_date', 'end_date'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        return $request->validate([
            'employee_id' => ['nullable', 'exists:employees,id'],
            'patient_id'  => ['nullable', 'exists:patients,id'],
            'type'        => ['required', Rule::in(array_keys(Contract::TYPES))],
            'title'       => ['required', 'string', 'max:200'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'amount'      => ['nullable', 'numeric', 'min:0'],
            'status'      => ['required', Rule::in(array_keys(Contract::STATUSES))],
            'terms'       => ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
        ]);
    }
}
