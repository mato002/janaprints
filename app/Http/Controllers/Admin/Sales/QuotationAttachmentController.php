<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\QuotationAttachmentType;
use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class QuotationAttachmentController extends Controller
{
    public function store(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('view', $quotation);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'attachment_type' => ['required', Rule::enum(QuotationAttachmentType::class)],
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('quotation-attachments/'.$quotation->id, 'local');

        QuotationAttachment::query()->create([
            'company_id' => $quotation->company_id,
            'quotation_id' => $quotation->id,
            'uploaded_by' => auth()->id(),
            'attachment_type' => $data['attachment_type'],
            'original_name' => $uploaded->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
        ]);

        return back()->with('status', __('Attachment uploaded.'));
    }

    public function destroy(Quotation $quotation, QuotationAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $quotation);
        abort_unless($attachment->quotation_id === $quotation->id, 404);

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return back()->with('status', __('Attachment removed.'));
    }
}
