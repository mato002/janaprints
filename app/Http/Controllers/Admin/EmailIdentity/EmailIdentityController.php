<?php

namespace App\Http\Controllers\Admin\EmailIdentity;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Services\EmailIdentity\CpanelMailboxGateway;
use App\Services\EmailIdentity\DepartmentMailboxCatalogService;
use App\Services\EmailIdentity\EmailIdentityReadinessService;
use Illuminate\Http\RedirectResponse;
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
        CpanelMailboxGateway $cpanel,
    ): View {
        ['companyId' => $companyId] = $this->tenantIds($request);

        return view('admin.email-identity.index', [
            'mailboxes' => $catalog->entries(),
            'readinessChecks' => $readiness->checks($companyId),
            'readinessSummary' => $readiness->summary($companyId),
            'cpanelStatus' => $this->cpanelStatus($cpanel),
            'queueGuidance' => $this->queueGuidance(),
        ]);
    }

    public function testCpanel(Request $request, CpanelMailboxGateway $cpanel): RedirectResponse
    {
        ['companyId' => $companyId] = $this->tenantIds($request);

        if (! $cpanel->isConfigured()) {
            return back()->with('error', __('cPanel is not configured. Set CPANEL_HOST, CPANEL_USERNAME, and CPANEL_API_TOKEN in your environment.'));
        }

        $result = $cpanel->testConnection();

        if ($result->success) {
            return back()->with('status', __('cPanel connection successful.'));
        }

        return back()->with('error', __('cPanel connection failed: :error', [
            'error' => $result->error ?? __('Unknown error'),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    protected function cpanelStatus(CpanelMailboxGateway $cpanel): array
    {
        return [
            'host_configured' => filled(config('mailboxes.cpanel.host')),
            'username_configured' => filled(config('mailboxes.cpanel.username')),
            'api_token_configured' => filled(config('mailboxes.cpanel.api_token')),
            'mock_mode' => $cpanel->isMockMode(),
            'mailbox_quota_mb' => (int) config('mailboxes.cpanel.default_quota_mb', 250),
        ];
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
