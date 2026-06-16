<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClientAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClientAccountController extends Controller
{
    use ResolvesClientCustomer;

    public function edit(): View
    {
        return view('client.account.edit', [
            'customer' => $this->clientCustomer(),
            'user' => $this->clientUser(),
        ]);
    }

    public function update(UpdateClientAccountRequest $request): RedirectResponse
    {
        $user = $this->clientUser();
        $customer = $this->clientCustomer();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $customer, $validated): void {
            $user->name = $validated['name'];

            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->save();

            $customer->fill([
                'contact_person' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'alternative_phone' => $validated['alternative_phone'] ?? null,
                'city' => $validated['city'] ?? null,
                'physical_address' => $validated['physical_address'] ?? null,
                'postal_address' => $validated['postal_address'] ?? null,
                'website' => $validated['website'] ?? null,
            ])->save();
        });

        return back()->with('status', __('Account updated.'));
    }
}
