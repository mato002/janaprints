<?php

namespace App\Http\Controllers\Admin\EmailIdentity;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Services\EmailIdentity\DepartmentMailboxCatalogService;
use App\Services\EmailIdentity\EmailIdentityReadinessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailIdentityController extends Controller
{
    use ResolvesIntegrationTenant;
    use ScopesToTenant;

    public function index(
        Request $request,
        DepartmentMailboxCatalogService $catalog,
        EmailIdentityReadinessService $readiness,
    ): View {
        ['companyId' => $companyId] = $this->tenantIds($request);

        return view('admin.email-identity.index', [
            'mailboxes' => $catalog->entries(),
            'readinessChecks' => $readiness->checks($companyId),
            'readinessSummary' => $readiness->summary($companyId),
            'queueGuidance' => $this->queueGuidance(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function queueGuidance(): array
    {
        $connection = (string) config('queue.default', 'sync');
        $isProduction = app()->environment('production');

        return [
            'connection' => $connection,
            'required_queue' => 'emails',
            'worker_command' => 'php artisan queue:work --queue=emails',
            'sync_warning' => $isProduction && $connection === 'sync',
        ];
    }
}
