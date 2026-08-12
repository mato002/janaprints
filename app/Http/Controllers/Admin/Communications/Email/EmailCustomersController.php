<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailVisibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailCustomersController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailVisibilityService $visibility,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        $companyId = $this->requireCompanyId();
        $q = trim((string) $request->get('q', ''));

        $customers = $this->visibility->topCustomersByEmail($companyId, 50);

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $customers = array_values(array_filter(
                $customers,
                fn (array $row) => str_contains(mb_strtolower($row['customer_name']), $needle)
                    || str_contains(mb_strtolower((string) ($row['email'] ?? '')), $needle),
            ));
        }

        return view('admin.communications.email.customers', [
            'customers' => $customers,
            'filters' => ['q' => $q],
            'mailbox' => $this->visibility->mailboxSummary($companyId),
        ]);
    }
}
