<?php

namespace App\Services\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Models\ActivityLog;
use App\Support\Production\MaterialReadinessService;
use App\Support\Production\MaterialRequirementsService;
use App\Support\Sales\DirectCustomerSalesOrderService;
use App\Support\Sales\SalesOrderFinancialStatusService;

class ProductionReleaseReadinessService
{
    public function __construct(
        protected SalesOrderFinancialStatusService $financialStatus,
        protected MaterialReadinessService $materialReadiness,
    ) {}

    /**
     * @return array{
     *     ready: bool,
     *     blockers: list<string>,
     *     warnings: list<string>,
     *     checks: list<array{key: string, label: string, passed: bool, severity: string, message: ?string}>
     * }
     */
    public function assess(SalesOrder $salesOrder, ?User $user = null): array
    {
        $salesOrder->loadMissing([
            'customer',
            'jobCard',
            'items',
            'inventoryItem',
            'artworkRequest',
            'productionSpecifications',
        ]);

        $checks = [];
        $blockers = [];
        $warnings = [];

        $this->runCheck($checks, $blockers, $warnings, 'customer', __('Customer linked'), $salesOrder->customer_id !== null);
        $this->runCheck(
            $checks,
            $blockers,
            $warnings,
            'order_status',
            __('Sales order confirmed'),
            in_array($salesOrder->status, [SalesOrderStatus::Confirmed, SalesOrderStatus::ReadyForProduction], true),
        );
        $existingJob = $salesOrder->jobCard;
        $jobAwaitingQueue = $existingJob?->status === ProductionJobCardStatus::Draft;

        if ($jobAwaitingQueue) {
            return $this->assessQueueHandoff($salesOrder, $existingJob);
        }

        $this->runCheck(
            $checks,
            $blockers,
            $warnings,
            'job_card',
            __('No existing job card'),
            $salesOrder->jobCard === null,
            __('A job card already exists for this order.'),
        );

        $salesOrder->loadMissing('items.productionSpecification');

        $spec = $salesOrder->productionSpecifications->first()
            ?? $salesOrder->items->first(fn ($item) => $item->productionSpecification !== null)?->productionSpecification;

        $hasSpecSource = $spec !== null
            || $salesOrder->items->isNotEmpty()
            || $salesOrder->inventory_item_id !== null
            || $salesOrder->quotation_id !== null;

        $this->runCheck(
            $checks,
            $blockers,
            $warnings,
            'production_spec',
            __('Production specification exists'),
            $hasSpecSource,
            __('Add order lines or a production specification before releasing to production.'),
        );

        if ($spec !== null) {
            $rejected = $spec->approval_status === ProductionSpecificationApprovalStatus::Rejected;
            $this->runCheck(
                $checks,
                $blockers,
                $warnings,
                'spec_approval',
                __('Production specification ready'),
                ! $rejected,
                __('This specification was rejected. Edit it before sending to production.'),
            );
        } elseif ($hasSpecSource) {
            $warnings[] = __('Production specification will be generated automatically when the job card is created.');
            $checks[] = $this->warningCheck(
                'spec_approval',
                __('Production specification approved'),
                __('Pending automatic specification snapshot.'),
            );
        }

        $artworkRequired = $this->artworkRequired($salesOrder);
        $artworkApproved = $this->artworkApproved($salesOrder);

        if ($artworkRequired) {
            $this->runCheck(
                $checks,
                $blockers,
                $warnings,
                'artwork',
                __('Artwork approved'),
                $artworkApproved,
                __('Artwork must be approved before production release.'),
            );
        } else {
            $checks[] = $this->passedCheck('artwork', __('Artwork approved'), __('Not required for this order.'));
        }

        $dueDate = $salesOrder->required_date ?? $spec?->due_date;

        if ($dueDate === null) {
            $warnings[] = __('Required due date is not set — production scheduling may be inaccurate.');
            $checks[] = $this->warningCheck(
                'due_date',
                __('Required due date set'),
                __('Set a required due date on the sales order.'),
            );
        } else {
            $checks[] = $this->passedCheck('due_date', __('Required due date set'));
        }

        $routingResolved = $this->routingResolvable($salesOrder);

        if (! $routingResolved) {
            $warnings[] = __('Production routing could not be resolved from the linked product.');
            $checks[] = $this->warningCheck('routing', __('Production routing resolvable'), __('Configure product routing or work centres.'));
        } else {
            $checks[] = $this->passedCheck('routing', __('Production routing resolvable'));
        }

        $depositWarning = $this->depositWarning($salesOrder);

        if ($depositWarning !== null) {
            $warnings[] = $depositWarning;
            $checks[] = $this->warningCheck('commercial', __('Commercial prerequisites'), $depositWarning);
        } else {
            $checks[] = $this->passedCheck('commercial', __('Commercial prerequisites'));
        }

        $this->appendMaterialReadinessCheck($checks, $blockers, $warnings, $salesOrder->jobCard, $salesOrder);

        return [
            'ready' => $blockers === [],
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'checks' => $checks,
        ];
    }

    protected function materialsAreOptional(SalesOrder $salesOrder, ?ProductionJobCard $jobCard = null): bool
    {
        return $salesOrder->production_destination !== null
            || $jobCard?->production_destination !== null;
    }

    /**
     * @param  list<array{key: string, label: string, passed: bool, severity: string, message: ?string}>  $checks
     * @param  list<string>  $blockers
     * @param  list<string>  $warnings
     */
    protected function appendMaterialReadinessCheck(
        array &$checks,
        array &$blockers,
        array &$warnings,
        ?ProductionJobCard $jobCard,
        SalesOrder $salesOrder,
    ): void {
        if ($jobCard === null) {
            $sources = app(MaterialRequirementsService::class)->resolveSourcesFromSalesOrder($salesOrder);

            if ($sources->isEmpty()) {
                $checks[] = $this->warningCheck(
                    'materials',
                    __('Material availability'),
                    __('Material requirements are generated when the job card is created. Stock must be available before queuing.'),
                );

                return;
            }

            $materials = $this->materialReadiness->previewForSalesOrder($salesOrder);

            if (! $materials['has_requirements']) {
                $message = __('No bill of materials is configured for this product. Production must set up a BOM before this order can be released.');

                if ($this->materialsAreOptional($salesOrder, $jobCard)) {
                    $warnings[] = __('No BOM yet. Offset, Digital, and Outsourced jobs can go to production from the job card; materials can be added later.');
                    $checks[] = $this->warningCheck('materials', __('Material availability'), __('BOM is optional for this production lane. Production can add materials on the job card.'));

                    return;
                }

                $blockers[] = $message;
                $checks[] = $this->failedCheck('materials', __('Material availability'), $message);

                return;
            }

            if ($this->materialsAreOptional($salesOrder, $jobCard) && ! $materials['ready']) {
                $warnings[] = $this->materialReadiness->formatBlockerMessage($materials);
                $checks[] = $this->warningCheck('materials', __('Material availability'), $materials['detail'] ?? __('Materials can be completed on the job card.'));

                return;
            }

            $this->applyMaterialAssessment($checks, $blockers, $warnings, $materials);

            return;
        }

        $materials = $this->materialReadiness->assess($jobCard);

        if ($this->materialsAreOptional($salesOrder, $jobCard) && ! $materials['ready']) {
            $warnings[] = $materials['detail'] ?: __('Materials can be completed on the job card.');
            $checks[] = $this->warningCheck('materials', __('Material availability'), $materials['detail'] ?? __('BOM is optional for this production lane.'));

            return;
        }

        $this->applyMaterialAssessment($checks, $blockers, $warnings, $materials);
    }

    /**
     * @param  list<array{key: string, label: string, passed: bool, severity: string, message: ?string}>  $checks
     * @param  list<string>  $blockers
     * @param  list<string>  $warnings
     * @param  array{
     *     status: string,
     *     ready: bool,
     *     percent: int,
     *     label: string,
     *     detail: string,
     *     has_requirements: bool,
     *     line_count: int,
     *     ready_count: int,
     *     short_count: int,
     *     missing: list<array{item: string, sku: ?string, shortfall: float, unit: ?string, available: float, required: float, remaining: float}>,
     *     materials_url: ?string
     * }  $materials
     */
    protected function applyMaterialAssessment(
        array &$checks,
        array &$blockers,
        array &$warnings,
        array $materials,
    ): void {
        if ($materials['ready']) {
            $checks[] = $this->passedCheck(
                'materials',
                __('Material availability'),
                $materials['detail'],
            );

            return;
        }

        $message = $this->materialReadiness->formatBlockerMessage($materials);
        $blockers[] = $message;
        $checks[] = $this->failedCheck('materials', __('Material availability'), $message);

        if (! empty($materials['missing'])) {
            foreach (array_slice($materials['missing'], 0, 3) as $line) {
                $qty = rtrim(rtrim(number_format($line['shortfall'], 3, '.', ''), '0'), '.');
                $unit = $line['unit'] ? ' '.$line['unit'] : '';
                $warnings[] = __('Short: :item (:qty:unit)', [
                    'item' => $line['item'],
                    'qty' => $qty,
                    'unit' => $unit,
                ]);
            }
        }
    }

    public function assertReady(SalesOrder $salesOrder, ?User $user = null, bool $allowOverride = false): void
    {
        $assessment = $this->assess($salesOrder, $user);

        if ($assessment['ready']) {
            return;
        }

        if ($allowOverride && $user?->can('sales_orders.production')) {
            ActivityLog::query()->create([
                'company_id' => $salesOrder->company_id,
                'user_id' => $user->id,
                'action' => 'production_release_override',
                'model_type' => $salesOrder->getMorphClass(),
                'model_id' => $salesOrder->id,
                'properties' => [
                    'blockers' => $assessment['blockers'],
                    'warnings' => $assessment['warnings'],
                ],
                'created_at' => now(),
            ]);

            return;
        }

        $messages = collect($assessment['checks'])
            ->filter(fn (array $check) => ! $check['passed'] && ($check['severity'] ?? 'blocker') === 'blocker')
            ->mapWithKeys(fn (array $check) => [$check['key'] => $check['message'] ?? $check['label']])
            ->all();

        if ($messages === []) {
            $messages = ['production_release' => implode(' ', $assessment['blockers'])];
        }

        throw \Illuminate\Validation\ValidationException::withMessages($messages);
    }

    /**
     * @return array{
     *     ready: bool,
     *     blockers: list<string>,
     *     warnings: list<string>,
     *     checks: list<array{key: string, label: string, passed: bool, severity: string, message: ?string}>
     * }
     */
    protected function assessQueueHandoff(SalesOrder $salesOrder, ProductionJobCard $jobCard): array
    {
        $checks = [];
        $blockers = [];
        $warnings = [];

        $this->runCheck(
            $checks,
            $blockers,
            $warnings,
            'order_status',
            __('Sales order ready for production'),
            in_array($salesOrder->status, [SalesOrderStatus::Confirmed, SalesOrderStatus::ReadyForProduction], true),
        );

        $checks[] = $this->passedCheck(
            'job_card',
            __('Job card created'),
            $jobCard->job_card_number,
        );

        $jobCard->loadMissing('queues', 'routeSteps');
        $canQueue = app(ProductionAutoSchedulingService::class)->canQueueDraftJob($jobCard);

        $this->runCheck(
            $checks,
            $blockers,
            $warnings,
            'queue_route',
            __('Production queue route'),
            $canQueue,
            __('Configure a work centre route or enable auto-scheduling before queuing this job.'),
        );

        if (! $canQueue) {
            $warnings[] = __('Open the job card to assign a work centre manually if auto-scheduling is disabled.');
        }

        $this->appendMaterialReadinessCheck($checks, $blockers, $warnings, $jobCard, $salesOrder);

        return [
            'ready' => $blockers === [],
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'checks' => $checks,
        ];
    }

    protected function artworkRequired(SalesOrder $salesOrder): bool
    {
        if ($salesOrder->is_direct_order) {
            return app(DirectCustomerSalesOrderService::class)
                ->productRequiresArtwork($salesOrder->inventoryItem);
        }

        return $salesOrder->artwork_request_id !== null;
    }

    protected function artworkApproved(SalesOrder $salesOrder): bool
    {
        if ($salesOrder->uses_existing_artwork && $salesOrder->customer_artwork_id) {
            return true;
        }

        return $salesOrder->artworkRequest?->status === ArtworkRequestStatus::Approved;
    }

    protected function routingResolvable(SalesOrder $salesOrder): bool
    {
        if ($salesOrder->inventory_item_id) {
            return true;
        }

        return $salesOrder->items->isNotEmpty();
    }

    protected function depositWarning(SalesOrder $salesOrder): ?string
    {
        $deposit = $this->financialStatus->depositSummary($salesOrder);

        if (($deposit['required'] ?? 0) > 0 && ($deposit['outstanding'] ?? 0) > 0.01) {
            return __('Required deposit has not been fully received.');
        }

        return null;
    }

    /**
     * @param  list<string>  $blockers
     * @param  list<string>  $warnings
     */
    protected function runCheck(
        array &$checks,
        array &$blockers,
        array &$warnings,
        string $key,
        string $label,
        bool $passed,
        ?string $failureMessage = null,
    ): void {
        if ($passed) {
            $checks[] = $this->passedCheck($key, $label);

            return;
        }

        $message = $failureMessage ?? __(':label check failed.', ['label' => $label]);
        $blockers[] = $message;
        $checks[] = $this->failedCheck($key, $label, $message);
    }

    /**
     * @return array{key: string, label: string, passed: bool, severity: string, message: ?string}
     */
    protected function passedCheck(string $key, string $label, ?string $message = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'passed' => true,
            'severity' => 'ok',
            'message' => $message,
        ];
    }

    /**
     * @return array{key: string, label: string, passed: bool, severity: string, message: ?string}
     */
    protected function failedCheck(string $key, string $label, string $message): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'passed' => false,
            'severity' => 'blocker',
            'message' => $message,
        ];
    }

    /**
     * @return array{key: string, label: string, passed: bool, severity: string, message: ?string}
     */
    protected function warningCheck(string $key, string $label, string $message): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'passed' => true,
            'severity' => 'warning',
            'message' => $message,
        ];
    }
}
