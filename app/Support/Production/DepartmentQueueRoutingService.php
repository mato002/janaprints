<?php

namespace App\Support\Production;

use App\Enums\ProductionType;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Production\WorkCenter;

class DepartmentQueueRoutingService
{
    public function __construct(
        protected DepartmentQueueRegistry $departments,
    ) {}

    /**
     * @return array{
     *     department_slug: ?string,
     *     department_label: ?string,
     *     work_center: ?WorkCenter,
     *     production_type: ?ProductionType,
     *     source: string
     * }
     */
    public function resolveForJobCard(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing([
            'productionSpecification.printProductTemplate.preferredWorkCenter',
        ]);

        $spec = $jobCard->productionSpecification;
        $template = $spec?->printProductTemplate;
        $productionType = $spec?->production_type ?? $jobCard->production_type;

        if ($template?->preferredWorkCenter && $template->preferredWorkCenter->is_active) {
            return [
                'department_slug' => $this->slugForWorkCenterCode($template->preferredWorkCenter->code),
                'department_label' => $this->departments->department($this->slugForWorkCenterCode($template->preferredWorkCenter->code) ?? '')['label'] ?? $template->preferredWorkCenter->name,
                'work_center' => $template->preferredWorkCenter,
                'production_type' => $productionType,
                'source' => 'print_product_template',
            ];
        }

        if ($jobCard->status === \App\Enums\ProductionJobCardStatus::Outsourced) {
            return [
                'department_slug' => 'outsource',
                'department_label' => __('Outsource'),
                'work_center' => null,
                'production_type' => $productionType,
                'source' => 'job_status',
            ];
        }

        $workCenter = $this->recommendedWorkCenter($jobCard, $spec, $productionType);

        return [
            'department_slug' => $workCenter ? $this->slugForWorkCenterCode($workCenter->code) : $this->slugForProductionType($productionType),
            'department_label' => $workCenter?->name ?? $this->labelForProductionType($productionType),
            'work_center' => $workCenter,
            'production_type' => $productionType,
            'source' => $spec ? 'production_specification' : 'production_type',
        ];
    }

    public function recommendedWorkCenter(
        ProductionJobCard $jobCard,
        ?ProductionSpecification $spec = null,
        ?ProductionType $productionType = null,
    ): ?WorkCenter {
        $spec ??= $jobCard->productionSpecification;
        $productionType ??= $spec?->production_type ?? $jobCard->production_type;

        if ($template = $spec?->printProductTemplate) {
            $template->loadMissing('preferredWorkCenter');
            if ($template->preferredWorkCenter?->is_active) {
                return $template->preferredWorkCenter;
            }
        }

        $codes = $this->workCenterCodesForType($productionType);

        return WorkCenter::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->whereIn('code', $codes)
            ->orderBy('name')
            ->first();
    }

    /**
     * @return list<string>
     */
    public function workCenterCodesForType(ProductionType $type): array
    {
        $map = config('production.production_type_work_center_codes', []);
        $primary = $map[$type->value] ?? $map[ProductionType::Mixed->value] ?? 'DESIGN';

        return is_array($primary) ? $primary : [$primary];
    }

    protected function slugForWorkCenterCode(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        $code = strtoupper($code);

        foreach ($this->departments->availableDepartments() as $slug => $department) {
            if (in_array($code, $department['work_center_codes'], true)) {
                return $slug;
            }
        }

        return null;
    }

    protected function slugForProductionType(?ProductionType $type): ?string
    {
        if (! $type) {
            return null;
        }

        foreach ($this->departments->availableDepartments() as $slug => $department) {
            if (in_array($type->value, $department['production_types'], true)) {
                return $slug;
            }
        }

        return null;
    }

    protected function labelForProductionType(?ProductionType $type): ?string
    {
        if (! $type) {
            return null;
        }

        return str_replace('_', ' ', ucfirst($type->value));
    }
}
