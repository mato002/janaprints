<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuotationNoteController extends Controller
{
    public function store(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('view', $quotation);

        $data = $request->validate(['note' => ['required', 'string']]);

        QuotationNote::query()->create([
            ...$data,
            'company_id' => $quotation->company_id,
            'quotation_id' => $quotation->id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('status', __('Note added.'));
    }

    public function destroy(Quotation $quotation, QuotationNote $note): RedirectResponse
    {
        $this->authorize('update', $quotation);
        abort_unless($note->quotation_id === $quotation->id, 404);

        $note->delete();

        return back()->with('status', __('Note removed.'));
    }
}
