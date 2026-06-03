<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesOrderAttachmentController extends Controller
{
    public function store(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('view', $salesOrder);

        $request->validate(['file' => ['required', 'file', 'max:20480']]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('sales-order-attachments/'.$salesOrder->id, 'local');

        SalesOrderAttachment::query()->create([
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'uploaded_by' => auth()->id(),
            'original_name' => $uploaded->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
        ]);

        return back()->with('status', __('Attachment uploaded.'));
    }

    public function destroy(SalesOrder $salesOrder, SalesOrderAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $salesOrder);
        abort_unless($attachment->sales_order_id === $salesOrder->id, 404);

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return back()->with('status', __('Attachment removed.'));
    }
}
