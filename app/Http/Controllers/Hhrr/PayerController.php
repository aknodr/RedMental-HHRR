<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Payer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayerController extends Controller
{
    public function index(Request $request): View
    {
        $q    = trim((string) $request->query('q', ''));
        $type = $request->query('type');

        $payers = Payer::query()
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('edi_payer_id', 'like', "%{$q}%");
            }))
            ->when($type, fn ($qb) => $qb->where('type', $type))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('hhrr.payers.index', [
            'payers' => $payers,
            'q'      => $q,
            'type'   => $type,
            'types'  => Payer::TYPES,
        ]);
    }

    public function create(): View
    {
        return view('hhrr.payers.form', [
            'payer' => new Payer(['type' => 'Commercial', 'active' => true]),
            'types' => Payer::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Payer::create($this->validated($request));
        return redirect()->route('hhrr.payers.index')->with('status', 'Payer created.');
    }

    public function edit(Payer $payer): View
    {
        return view('hhrr.payers.form', ['payer' => $payer, 'types' => Payer::TYPES]);
    }

    public function update(Request $request, Payer $payer): RedirectResponse
    {
        $payer->update($this->validated($request));
        return redirect()->route('hhrr.payers.index')->with('status', 'Payer updated.');
    }

    public function destroy(Payer $payer): RedirectResponse
    {
        if ($payer->patientInsurances()->exists()) {
            return back()->with('error', 'This payer is still linked to patient insurance records.');
        }
        $payer->delete();
        return redirect()->route('hhrr.payers.index')->with('status', 'Payer deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:200'],
            'edi_payer_id' => ['nullable', 'string', 'max:20'],
            'type'         => ['required', Rule::in(array_keys(Payer::TYPES))],
            'phone'        => ['nullable', 'string', 'max:50'],
            'email'        => ['nullable', 'email', 'max:200'],
            'address'      => ['nullable', 'string', 'max:200'],
            'city'         => ['nullable', 'string', 'max:100'],
            'state'        => ['nullable', 'string', 'size:2'],
            'zip'          => ['nullable', 'string', 'max:10'],
            'notes'        => ['nullable', 'string'],
            'active'       => ['sometimes', 'boolean'],
        ]);
        $data['active'] = $request->boolean('active', true);
        return $data;
    }
}
