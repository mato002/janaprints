<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerFileController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('customer-files/'.$customer->id, 'local');

        CustomerFile::query()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'uploaded_by' => auth()->id(),
            'original_name' => $uploaded->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
        ]);

        return back()->with('status', __('File uploaded.'));
    }

    public function destroy(Customer $customer, CustomerFile $file): RedirectResponse
    {
        $this->authorize('update', $customer);
        abort_unless($file->customer_id === $customer->id, 404);

        Storage::disk('local')->delete($file->path);
        $file->delete();

        return back()->with('status', __('File removed.'));
    }
}
