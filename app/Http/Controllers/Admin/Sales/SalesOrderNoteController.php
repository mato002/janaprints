<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SalesOrderNoteController extends Controller
{
    public function store(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('view', $salesOrder);

        $data = $request->validate(['note' => ['required', 'string']]);

        SalesOrderNote::query()->create([
            ...$data,
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('status', __('Note added.'));
    }

    public function destroy(SalesOrder $salesOrder, SalesOrderNote $note): RedirectResponse
    {
        $this->authorize('update', $salesOrder);
        abort_unless($note->sales_order_id === $salesOrder->id, 404);

        $note->delete();

        return back()->with('status', __('Note removed.'));
    }
}
