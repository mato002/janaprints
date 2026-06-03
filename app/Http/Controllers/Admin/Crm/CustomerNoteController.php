<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerNoteController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $data = $request->validate(['note' => ['required', 'string']]);

        CustomerNote::query()->create([
            ...$data,
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('status', __('Note added.'));
    }

    public function destroy(Customer $customer, CustomerNote $note): RedirectResponse
    {
        $this->authorize('update', $customer);
        abort_unless($note->customer_id === $customer->id, 404);

        $note->delete();

        return back()->with('status', __('Note removed.'));
    }
}
