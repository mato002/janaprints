<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerProductSerialProfile;
use App\Models\Inventory\InventoryItem;
use App\Support\Crm\CustomerArtworkService;
use App\Support\Crm\CustomerArtworkTypeCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerArtworkController extends Controller
{
    use HandlesModalFormResponses, ScopesToTenant;

    public function store(Request $request, Customer $customer, CustomerArtworkService $service): RedirectResponse|Response
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'artwork_name' => ['required', 'string', 'max:255'],
            'artwork_type' => app(CustomerArtworkTypeCatalog::class)->validationRules((int) $customer->company_id, required: true),
            'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $service->uploadVersion(
            $customer,
            $request->file('file'),
            $validated['artwork_name'],
            $validated['artwork_type'],
            (int) auth()->id(),
        );

        return $this->modalOrRedirect(
            __('Artwork version uploaded.'),
            redirect()->route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications']),
        );
    }

    public function preview(Customer $customer, CustomerArtwork $customerArtwork, CustomerArtworkService $service): BinaryFileResponse
    {
        $this->authorize('view', $customer);
        abort_unless($customerArtwork->customer_id === $customer->id, 404);

        $stream = $service->streamPreview($customerArtwork);

        return response()->file($stream['path'], [
            'Content-Type' => $stream['mime'],
            'Content-Disposition' => 'inline; filename="'.addslashes($stream['name']).'"',
        ]);
    }
}
