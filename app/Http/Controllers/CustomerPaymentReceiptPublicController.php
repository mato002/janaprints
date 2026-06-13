<?php

namespace App\Http\Controllers;

use App\Models\Sales\CustomerPayment;
use App\Support\Documents\ReceiptDocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPaymentReceiptPublicController extends Controller
{
    public function __construct(
        protected ReceiptDocumentService $documents,
    ) {}

    public function show(Request $request, CustomerPayment $payment): View
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $document = $this->documents->build($payment, includeInternalMeta: false);

        return view('documents.receipt.public', compact('document'));
    }
}
