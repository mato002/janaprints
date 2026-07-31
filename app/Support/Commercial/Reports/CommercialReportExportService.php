<?php

namespace App\Support\Commercial\Reports;

use App\Enums\CommercialReportExportStatus;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\CommercialReportExport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommercialReportExportService
{
    /**
     * @param  array<string, mixed>  $scopePayload
     */
    public function queue(
        Request $request,
        array $scopePayload,
        string $module,
        string $tab,
        string $format,
        string $redirectRoute,
    ): RedirectResponse {
        $user = $request->user();
        $companyId = (int) ($scopePayload['company_id'] ?? $user?->company_id);
        $now = now();

        $export = CommercialReportExport::query()->create([
            'company_id' => $companyId,
            'user_id' => (int) $user->id,
            'module' => $module,
            'tab' => $tab,
            'format' => $format,
            'scope_payload' => $scopePayload,
            'status' => CommercialReportExportStatus::Queued,
            'queued_at' => $now,
            'expires_at' => $now->copy()->addDays((int) config('platform.commercial_reports.export_ttl_days', 7)),
        ]);

        ProcessCommercialReportExportJob::dispatch($export->id);

        return redirect()
            ->route($this->resolveRedirectRoute($request, $redirectRoute), $this->redirectParams($request))
            ->with('export_id', $export->id)
            ->with('status', __('Your :format export has been queued. You can download it from Export History when ready.', [
                'format' => strtoupper($format),
            ]));
    }

    protected function resolveRedirectRoute(Request $request, string $defaultRoute): string
    {
        if ($request->filled('report') && \Illuminate\Support\Facades\Route::has('admin.reports.commercial')) {
            return 'admin.reports.commercial';
        }

        return $defaultRoute;
    }

    /**
     * @return array<string, mixed>
     */
    protected function redirectParams(Request $request): array
    {
        $params = array_merge($request->query(), $request->except(['_token', 'format']));

        if (
            $request->input('embedded') === '1'
            || $request->query('embedded') === '1'
            || $request->header('Turbo-Frame') === 'module-workspace-content'
        ) {
            $params['embedded'] = '1';
        }

        return array_filter(
            $params,
            fn ($value) => $value !== null && $value !== '',
        );
    }

    public function authorizeView(User $user, CommercialReportExport $export): void
    {
        abort_unless($user->can('commercial.reports.exports.view'), 403);

        $companyId = tenant()->companyId() ?? $user->company_id;
        abort_unless($export->company_id === (int) $companyId, 403);

        if (! $user->can('commercial.reports.exports.download')) {
            abort_unless($export->user_id === $user->id, 403);
        }
    }

    public function authorizeDownload(User $user, CommercialReportExport $export): void
    {
        abort_unless($user->can('commercial.reports.exports.download'), 403);

        $companyId = tenant()->companyId() ?? $user->company_id;
        abort_unless($export->company_id === (int) $companyId, 403);

        if (! $user->can('commercial.reports.exports.view')) {
            abort_unless($export->user_id === $user->id, 403);
        }

        abort_if($export->isExpired(), 410, __('This export has expired.'));
        abort_unless($export->isDownloadable(), 404);
    }
}
