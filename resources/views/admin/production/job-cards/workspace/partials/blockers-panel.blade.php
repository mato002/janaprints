@php
    use App\Enums\ProductionJobCardStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $workflowPresentation = $workflowPresentation ?? null;
    $controlAlerts = $controlAlerts ?? [];
    $completion = $completion ?? ['eligible' => false, 'blockers' => [], 'already_posted' => false];
    $hasPostedOutput = (bool) ($hasPostedOutput ?? ($completion['already_posted'] ?? false));
    $materialReadiness = is_array($materialReadiness ?? null) ? $materialReadiness : null;
    $executionState = $executionState ?? [];

    $showDownstreamRequirements = in_array($jobCard->status, [
        ProductionJobCardStatus::QualityCheck,
        ProductionJobCardStatus::Completed,
        ProductionJobCardStatus::ReadyForDispatch,
    ], true);

    $showMaterialReleaseGate = in_array($jobCard->status, [
        ProductionJobCardStatus::Draft,
        ProductionJobCardStatus::Queued,
        ProductionJobCardStatus::Rework,
        ProductionJobCardStatus::OnHold,
        ProductionJobCardStatus::InProduction,
    ], true);

    $items = [];
    $seen = [];
    $resolveUrl = null;

    if ($showMaterialReleaseGate && $materialReadiness && ! ($materialReadiness['ready'] ?? false)) {
        $hasRequirements = (bool) ($materialReadiness['has_requirements'] ?? false);
        $shortCount = (int) ($materialReadiness['short_count'] ?? 0);

        if (! $hasRequirements) {
            $message = (string) ($materialReadiness['setup_blocker'] ?? '') ?: __('Material requirements missing');
        } elseif ($jobCard->status === ProductionJobCardStatus::InProduction) {
            $message = $shortCount > 0
                ? __('Material shortages (:count) — receive stock or reserve what is available', ['count' => $shortCount])
                : __('Materials not ready — open Materials to finish setup');
        } else {
            $message = $shortCount > 0
                ? __('Material shortages (:count) block release — receive stock or reserve available', ['count' => $shortCount])
                : __('Material shortages block release');
        }

        $seen[$message] = true;
        $materialsUrl = (string) ($materialReadiness['materials_url']
            ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']));
        $materialsBase = strstr($materialsUrl, '#', true) ?: $materialsUrl;
        $resolveUrl ??= ($hasRequirements && $shortCount > 0)
            ? $materialsBase.'#materials-shortages'
            : $materialsBase;
        $items[] = [
            'severity' => 'error',
            'message' => $message,
        ];
    }

    if ($showMaterialReleaseGate && ($executionState['needs_operator'] ?? false)) {
        $message = __('Operator not assigned');
        if (! isset($seen[$message])) {
            $seen[$message] = true;
            $items[] = ['severity' => 'error', 'message' => $message];
            $resolveUrl ??= route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']).'#assign-operator';
        }
    }

    if ($showMaterialReleaseGate && ($executionState['needs_machine'] ?? false)) {
        $message = __('Machine not assigned');
        if (! isset($seen[$message])) {
            $seen[$message] = true;
            $items[] = ['severity' => 'error', 'message' => $message];
            $resolveUrl ??= route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']).'#assign-machine';
        }
    }

    if ($showDownstreamRequirements) {
        foreach ($workflowPresentation['readiness_items'] ?? [] as $item) {
            if ($item['passed'] ?? true) {
                continue;
            }

            $label = (string) ($item['label'] ?? '');
            if ($label === '' || isset($seen[$label])) {
                continue;
            }

            $seen[$label] = true;
            $action = $item['action'] ?? null;
            $method = $item['action_method'] ?? 'GET';
            $resolveUrl ??= ($method === 'GET') ? $action : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']);
            $items[] = [
                'severity' => 'error',
                'message' => $label,
            ];
        }
    }

    foreach ($controlAlerts as $alert) {
        $message = (string) ($alert['message'] ?? '');
        if ($message === '' || isset($seen[$message])) {
            continue;
        }

        $lower = strtolower($message);

        if (str_contains($lower, 'operation') && collect($items)->contains(fn (array $item) => str_contains(strtolower($item['message'] ?? ''), 'operation'))) {
            continue;
        }

        $seen[$message] = true;

        if ($resolveUrl === null) {
            $resolveUrl = match (true) {
                str_contains($lower, 'artwork') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']),
                str_contains($lower, 'qc') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']),
                str_contains($lower, 'shortag'), str_contains($lower, 'readiness'), str_contains($lower, 'requirements'), str_contains($lower, 'material') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']),
                str_contains($lower, 'finished goods'), str_contains($lower, 'post') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']),
                str_contains($lower, 'operation') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']),
                default => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
            };
        }

        $items[] = [
            'severity' => ($alert['type'] ?? '') === 'warning' ? 'warning' : 'error',
            'message' => $message,
        ];
    }

    if ($showDownstreamRequirements) {
        foreach ($completion['blockers'] ?? [] as $message) {
            $message = (string) $message;
            if ($message === '' || isset($seen[$message])) {
                continue;
            }

            if (! empty($completion['already_posted']) || ! empty($hasPostedOutput)) {
                continue;
            }

            $seen[$message] = true;
            $resolveUrl ??= route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']);
            $items[] = [
                'severity' => 'warning',
                'message' => $message,
            ];
        }
    }
    $compact = (bool) ($compact ?? false);
@endphp

@if ($compact)
    @if (! empty($items))
        <div class="job-360-blockers mes-blockers">
            <div class="job-360-blockers__head">
                <span class="job-360-blockers__title">🚨 {{ __('Blockers') }} ({{ count($items) }})</span>
                @if ($resolveUrl)
                    <a href="{{ $resolveUrl }}" class="job-360-blockers__resolve" @foreach (WorkspaceEmbed::leaveWorkspaceLinkAttributes() as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                        {{ __('Resolve') }} →
                    </a>
                @endif
            </div>
            <ul class="job-360-blockers__list">
                @foreach ($items as $item)
                    <li @class(['job-360-blockers__item', 'job-360-blockers__item--warning' => ($item['severity'] ?? '') === 'warning'])>
                        {{ $item['message'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="job-360-blockers mes-blockers mes-blockers--clear">
            <div class="job-360-blockers__head">
                <span class="job-360-blockers__title text-emerald-800">✓ {{ __('No blockers') }}</span>
            </div>
        </div>
    @endif
@elseif (! empty($items))
    <x-admin.job-module-card theme="alert" :title="__('Blockers')" icon="exclamation" compact class="h-full">
        <x-slot:actions>
            @if ($resolveUrl)
                <a href="{{ $resolveUrl }}" class="text-xs font-semibold text-red-700 hover:underline" @foreach (WorkspaceEmbed::leaveWorkspaceLinkAttributes() as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                    {{ __('Resolve') }} →
                </a>
            @endif
        </x-slot:actions>

        <p class="mb-2 text-xs font-medium text-red-800">{{ trans_choice(':count issue blocking release|:count issues blocking release', count($items), ['count' => count($items)]) }}</p>

        <ul class="space-y-1.5">
            @foreach ($items as $item)
                <li @class([
                    'flex items-start gap-2 rounded-md px-2 py-1.5 text-sm',
                    'bg-red-100/70 text-red-900' => ($item['severity'] ?? '') === 'error',
                    'bg-amber-100/70 text-amber-900' => ($item['severity'] ?? '') === 'warning',
                ])>
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current"></span>
                    <span>{{ $item['message'] }}</span>
                </li>
            @endforeach
        </ul>
    </x-admin.job-module-card>
@else
    <x-admin.job-module-card theme="materials" :title="__('Blockers')" icon="badge-check" compact class="h-full">
        <p class="text-sm font-medium text-emerald-800">{{ __('No blockers — clear to proceed') }}</p>
    </x-admin.job-module-card>
@endif
