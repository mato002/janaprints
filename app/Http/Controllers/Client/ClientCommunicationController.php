<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Services\Client\ClientPortalCommunicationService;
use Illuminate\View\View;

class ClientCommunicationController extends Controller
{
    use ResolvesClientCustomer;

    public function __invoke(ClientPortalCommunicationService $communications): View
    {
        $customer = $this->clientCustomer();

        $logs = $communications->paginateForCustomer($customer);

        return view('client.communications.index', [
            'customer' => $customer,
            'logs' => $logs,
            'communications' => $communications,
        ]);
    }
}
