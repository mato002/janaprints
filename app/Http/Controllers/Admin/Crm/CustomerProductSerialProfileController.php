<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerProductSerialProfile;
use App\Models\Inventory\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerProductSerialProfileController extends Controller
{
    use HandlesModalFormResponses, ScopesToTenant;

    public function store(Request $request, Customer $customer): RedirectResponse|Response
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'serial_prefix' => ['required', 'string', 'max:30'],
            'serial_padding_length' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $item = InventoryItem::query()->forTenant()->findOrFail($validated['inventory_item_id']);

        CustomerProductSerialProfile::query()->updateOrCreate(
            [
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'inventory_item_id' => $item->id,
            ],
            [
                'serial_prefix' => $validated['serial_prefix'],
                'serial_padding_length' => $validated['serial_padding_length'],
            ],
        );

        return $this->modalOrRedirect(
            __('Serial numbering profile saved.'),
            redirect()->route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications']),
        );
    }

    public function destroy(Customer $customer, CustomerProductSerialProfile $profile): RedirectResponse
    {
        $this->authorize('update', $customer);
        abort_unless($profile->customer_id === $customer->id, 404);

        $profile->delete();

        return back()->with('status', __('Serial profile removed.'));
    }
}
