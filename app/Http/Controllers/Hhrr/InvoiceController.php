<?php

namespace App\Http\Controllers\Hhrr;

use App\Http\Controllers\Controller;
use App\Models\Hhrr\Invoice;
use App\Models\Hhrr\InvoiceLine;
use App\Models\Hhrr\Patient;
use App\Models\Hhrr\Payer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $invoices = Invoice::query()
            ->with(['patient', 'payer'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('issue_date')
            ->paginate(20)
            ->withQueryString();

        // Auto-flag overdue (sent + past due_date) so the badge column reflects reality without a cron.
        Invoice::overdueNow()->update(['status' => 'overdue']);

        $stats = [
            'total_outstanding' => (float) Invoice::whereIn('status', ['sent', 'overdue'])->sum(DB::raw('total - amount_paid')),
            'paid_ytd'          => (float) Invoice::where('status', 'paid')->whereYear('paid_date', now()->year)->sum('total'),
            'overdue_count'     => Invoice::where('status', 'overdue')->count(),
            'draft_count'       => Invoice::where('status', 'draft')->count(),
        ];

        return view('hhrr.invoices.index', [
            'invoices' => $invoices,
            'statuses' => Invoice::STATUSES,
            'status'   => $status,
            'stats'    => $stats,
        ]);
    }

    public function create(): View
    {
        return view('hhrr.invoices.form', [
            'invoice'  => new Invoice([
                'invoice_number' => $this->nextInvoiceNumber(),
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'status'         => 'draft',
            ]),
            'patients' => Patient::where('active', true)->orderBy('last_name')->get(),
            'payers'   => Payer::orderBy('name')->get(),
            'statuses' => Invoice::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $linesInput = $request->input('lines', []);

        $invoice = DB::transaction(function () use ($data, $linesInput) {
            $invoice = Invoice::create($data + ['created_by' => auth()->id()]);
            $this->syncLines($invoice, $linesInput);
            $invoice->load('lines');
            $invoice->recalculate((float) request('tax_rate', 0))->save();
            return $invoice;
        });

        return redirect()->route('hhrr.invoices.show', $invoice)
            ->with('status', 'Invoice ' . $invoice->invoice_number . ' created.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'payer', 'lines', 'creator']);
        return view('hhrr.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        abort_if($invoice->status === 'paid', 403, 'Paid invoices cannot be edited.');
        $invoice->load('lines');
        return view('hhrr.invoices.form', [
            'invoice'  => $invoice,
            'patients' => Patient::where('active', true)->orderBy('last_name')->get(),
            'payers'   => Payer::orderBy('name')->get(),
            'statuses' => Invoice::STATUSES,
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === 'paid', 403, 'Paid invoices cannot be edited.');
        $data = $this->validated($request);
        $linesInput = $request->input('lines', []);

        DB::transaction(function () use ($invoice, $data, $linesInput) {
            $invoice->update($data);
            $this->syncLines($invoice, $linesInput);
            $invoice->load('lines');
            $invoice->recalculate((float) request('tax_rate', 0))->save();
        });

        return redirect()->route('hhrr.invoices.show', $invoice)->with('status', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === 'paid', 403, 'Paid invoices cannot be deleted.');
        $invoice->delete();
        return redirect()->route('hhrr.invoices.index')->with('status', 'Invoice deleted.');
    }

    public function markPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'paid_date'   => ['required', 'date'],
        ]);
        $invoice->update([
            'amount_paid' => $data['amount_paid'],
            'paid_date'   => $data['paid_date'],
            'status'      => $data['amount_paid'] >= (float) $invoice->total ? 'paid' : $invoice->status,
        ]);
        return back()->with('status', 'Payment recorded.');
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }
        return back()->with('status', 'Invoice marked as sent.');
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $count = Invoice::whereYear('issue_date', $year)->count() + 1;
        return sprintf('INV-%s-%04d', $year, $count);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'patient_id'     => ['required', 'exists:patients,id'],
            'payer_id'       => ['nullable', 'exists:payers,id'],
            'invoice_number' => ['required', 'string', 'max:30'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:issue_date'],
            'paid_date'      => ['nullable', 'date'],
            'status'         => ['required', Rule::in(array_keys(Invoice::STATUSES))],
            'amount_paid'    => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string'],
            'terms'          => ['nullable', 'string'],

            'lines'                  => ['array'],
            'lines.*.id'             => ['nullable', 'integer'],
            'lines.*.description'    => ['required', 'string'],
            'lines.*.cpt_code'       => ['nullable', 'string', 'max:20'],
            'lines.*.service_date'   => ['nullable', 'date'],
            'lines.*.quantity'       => ['required', 'numeric', 'min:0'],
            'lines.*.unit_price'     => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function syncLines(Invoice $invoice, array $linesInput): void
    {
        $kept = [];
        foreach ($linesInput as $row) {
            $payload = [
                'description'   => $row['description'],
                'cpt_code'      => $row['cpt_code'] ?? null,
                'service_date'  => $row['service_date'] ?? null,
                'quantity'      => (float) $row['quantity'],
                'unit_price'    => (float) $row['unit_price'],
                'line_total'    => 0, // recalculated by InvoiceLine::saving()
            ];
            $line = ! empty($row['id'])
                ? tap(InvoiceLine::where('id', $row['id'])->where('invoice_id', $invoice->id)->firstOrFail())->update($payload)
                : $invoice->lines()->create($payload);
            $kept[] = $line->id;
        }
        $invoice->lines()->whereNotIn('id', $kept ?: [0])->delete();
    }
}
