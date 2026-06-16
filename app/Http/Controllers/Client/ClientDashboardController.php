<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Services\Client\ClientPortalService;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    use ResolvesClientCustomer;

    public function __invoke(ClientPortalService $portal): View
    {
        $customer = $this->clientCustomer();

        return view('client.dashboard', [
            'customer' => $customer,
            'dashboard' => $portal->dashboard($customer),
        ]);
    }
}
