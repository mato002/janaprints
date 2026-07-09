<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Services\Client\ClientPortalCommunicationService;
use Illuminate\View\View;

class ClientCommunicationHistoryController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected ClientPortalCommunicationService $communications,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        return view('client.communications.history', [
            'customer' => $customer,
            'logs' => $this->communications->paginateForCustomer($customer),
        ]);
    }
}
