<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Services\Client\ClientPortalStatementDocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientStatementController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected ClientPortalStatementDocumentService $statements,
    ) {}

    public function index(Request $request): View
    {
        $customer = $this->clientCustomer();

        $fromDate = $request->string('from_date')->toString() ?: now()->startOfMonth()->toDateString();
        $toDate = $request->string('to_date')->toString() ?: now()->toDateString();

        $report = null;
        if ($request->boolean('preview')) {
            $report = $this->statements->build($customer, [
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]);
        }

        return view('client.statements.index', [
            'customer' => $customer,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'report' => $report,
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        $customer = $this->clientCustomer();

        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'format' => ['nullable', 'in:csv,pdf,excel'],
        ]);

        return $this->statements->download($customer, [
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
        ], $validated['format'] ?? 'pdf');
    }
}
