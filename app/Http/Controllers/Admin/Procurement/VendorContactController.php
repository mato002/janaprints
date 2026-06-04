<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendor;
use App\Models\Procurement\VendorContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorContactController extends Controller
{
    public function store(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('update', $vendor);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['is_primary'])) {
            $vendor->contacts()->update(['is_primary' => false]);
        }

        $vendor->contacts()->create([
            ...$data,
            'company_id' => $vendor->company_id,
        ]);

        return back()->with('status', __('Contact added.'));
    }

    public function destroy(Vendor $vendor, VendorContact $contact): RedirectResponse
    {
        $this->authorize('update', $vendor);
        abort_unless($contact->vendor_id === $vendor->id, 404);

        $contact->delete();

        return back()->with('status', __('Contact removed.'));
    }
}
