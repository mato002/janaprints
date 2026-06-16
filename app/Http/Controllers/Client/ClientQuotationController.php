<?php

namespace App\Http\Controllers\Client;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use App\Support\Documents\QuotationDocumentService;
use App\Support\Governance\WorkflowRulesService;
use App\Support\QuotationRevisionService;
use App\Enums\WorkflowRuleTrigger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientQuotationController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected QuotationDocumentService $documents,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $quotations = Quotation::query()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [QuotationStatus::Draft, QuotationStatus::PendingApproval])
            ->latest('quotation_date')
            ->paginate(12);

        return view('client.quotations.index', compact('customer', 'quotations'));
    }

    public function show(Quotation $quotation): View
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($quotation, $customer);

        if (in_array($quotation->status, [QuotationStatus::Sent, QuotationStatus::Viewed], true)) {
            if ($quotation->status === QuotationStatus::Sent) {
                $quotation->transitionTo(QuotationStatus::Viewed);
                QuotationRevisionService::snapshot($quotation->fresh());
            }
        }

        $quotation->load(['items']);

        return view('client.quotations.show', [
            'customer' => $customer,
            'quotation' => $quotation->fresh(),
            'canRespond' => in_array($quotation->status, [QuotationStatus::Sent, QuotationStatus::Viewed], true),
        ]);
    }

    public function pdf(Quotation $quotation): StreamedResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($quotation, $customer);

        return $this->documents->downloadPdf($quotation);
    }

    public function accept(Quotation $quotation): RedirectResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($quotation, $customer);

        abort_unless(
            in_array($quotation->status, [QuotationStatus::Sent, QuotationStatus::Viewed], true),
            403,
        );

        $quotation->transitionTo(QuotationStatus::Accepted);
        QuotationRevisionService::snapshot($quotation);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Approved, $quotation, $this->clientUser());

        return redirect()
            ->route('client.quotations.show', $quotation)
            ->with('status', __('Quotation accepted. Our team will follow up shortly.'));
    }

    public function reject(Request $request, Quotation $quotation): RedirectResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($quotation, $customer);

        abort_unless(
            in_array($quotation->status, [QuotationStatus::Sent, QuotationStatus::Viewed], true),
            403,
        );

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $quotation->transitionTo(QuotationStatus::Rejected);
        QuotationRevisionService::snapshot($quotation);

        if ($request->filled('reason')) {
            $quotation->update([
                'notes' => trim(($quotation->notes ? $quotation->notes."\n\n" : '').__('Client feedback: :reason', [
                    'reason' => $request->string('reason'),
                ])),
            ]);
        }

        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Rejected, $quotation, $this->clientUser());

        return redirect()
            ->route('client.quotations.index')
            ->with('status', __('Quotation declined.'));
    }
}
