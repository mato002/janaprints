<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Support\Communications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'commercial_alerts' => ['sometimes', 'boolean'],
            'production_alerts' => ['sometimes', 'boolean'],
            'accounting_alerts' => ['sometimes', 'boolean'],
            'hr_alerts' => ['sometimes', 'boolean'],
            'system_alerts' => ['sometimes', 'boolean'],
        ]);

        $companyId = $this->requireCompanyId();
        $prefs = $this->notifications->updatePreferences(
            $request->user(),
            $companyId,
            $request->only([
                'commercial_alerts',
                'production_alerts',
                'accounting_alerts',
                'hr_alerts',
                'system_alerts',
            ]),
        );

        return response()->json([
            'preferences' => [
                'commercial_alerts' => $prefs->commercial_alerts,
                'production_alerts' => $prefs->production_alerts,
                'accounting_alerts' => $prefs->accounting_alerts,
                'hr_alerts' => $prefs->hr_alerts,
                'system_alerts' => $prefs->system_alerts,
            ],
        ]);
    }
}
