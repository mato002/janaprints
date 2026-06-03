<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerContactController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'is_primary' => ['boolean'],
        ]);

        CustomerContact::query()->create([
            ...$data,
            'company_id' => $customer->company_id,
            'branch_id' => $customer->branch_id,
            'customer_id' => $customer->id,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return back()->with('status', __('Contact added.'));
    }

    public function destroy(Customer $customer, CustomerContact $contact): RedirectResponse
    {
        $this->authorize('update', $customer);
        abort_unless($contact->customer_id === $customer->id, 404);

        $contact->delete();

        return back()->with('status', __('Contact removed.'));
    }
}
