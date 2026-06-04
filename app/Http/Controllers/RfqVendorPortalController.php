<?php

namespace App\Http\Controllers;

use App\Models\Procurement\RfqVendor;
use App\Support\Procurement\RFQService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqVendorPortalController extends Controller
{
    public function show(string $token): View
    {
        $rfqVendor = RfqVendor::query()
            ->where('response_token', $token)
            ->with(['rfq.items', 'vendor', 'responses'])
            ->firstOrFail();

        $rfq = $rfqVendor->rfq;

        abort_unless($rfq->status->canReceiveResponses(), 404);

        return view('procurement.rfq-portal.show', [
            'rfq' => $rfq,
            'rfqVendor' => $rfqVendor,
        ]);
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $rfqVendor = RfqVendor::query()
            ->where('response_token', $token)
            ->with('rfq.items')
            ->firstOrFail();

        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.rfq_item_id' => ['required', 'integer', 'exists:rfq_items,id'],
            'lines.*.quoted_price' => ['required', 'numeric', 'min:0'],
            'lines.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'lines.*.warranty' => ['nullable', 'string', 'max:255'],
            'lines.*.delivery_terms' => ['nullable', 'string', 'max:255'],
            'lines.*.comments' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('rfq-responses/'.$rfqVendor->rfq_id, 'public');
            $validated['lines'][0]['attachment_path'] = $path;
        }

        RFQService::recordVendorResponse($rfqVendor, $validated['lines']);

        return back()->with('status', __('Your quotation has been submitted. Thank you.'));
    }
}
