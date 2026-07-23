<?php

namespace App\Support\Lookup;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Crm\CustomerSegment;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Support\Crm\CustomerArtworkTypeCatalog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Warehouse;
use App\Models\Hr\PayrollGroupDefinition;
use App\Models\Procurement\Vendor;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Hr\PayrollGroupService;
use App\Support\Production\ProductionJobCardEligibilityService;
use App\Support\Sales\CustomerOrderContextService;
use App\Support\Platform\FormStatusOptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LookupOptionService
{
    /**
     * @return list<array{value: int|string, label: string}>
     */
    public function options(string $type, Request $request): array
    {
        return match ($type) {
            'companies' => $this->mapOptions($this->companiesQuery($request)->get(['id', 'name']), 'id', 'name'),
            'branches' => $this->mapOptions($this->branchesQuery($request)->get(['id', 'name']), 'id', 'name'),
            'customers' => $this->mapOptions($this->customersQuery($request)->get(['id', 'company_name']), 'id', 'company_name'),
            'vendors' => $this->mapOptions($this->vendorsQuery($request)->get(['id', 'vendor_name']), 'id', 'vendor_name'),
            'categories' => $this->mapOptions($this->categoriesQuery($request)->get(['id', 'name']), 'id', 'name'),
            'subcategories' => $this->subcategoryOptions($request),
            'brands' => $this->mapOptions($this->brandsQuery($request)->get(['id', 'name']), 'id', 'name'),
            'uoms' => $this->mapOptions($this->uomsQuery($request)->get(['id', 'name']), 'id', 'name'),
            'items' => $this->itemOptions($request),
            'warehouses' => $this->warehouseOptions($request),
            'segments' => $this->mapOptions($this->segmentsQuery($request)->get(['id', 'name']), 'id', 'name'),
            'departments' => $this->mapOptions($this->departmentsQuery($request)->get(['id', 'name']), 'id', 'name'),
            'employees' => $this->mapOptions($this->employeesQuery($request)->get(['id', 'first_name', 'last_name', 'employee_number']), 'id', fn ($row) => trim("{$row->first_name} {$row->last_name}")." ({$row->employee_number})"),
            'operators' => $this->mapOptions($this->operatorsQuery($request)->get(['id', 'name']), 'id', 'name'),
            'price_books' => $this->priceBookOptions($request),
            'leads' => $this->mapOptions($this->leadsQuery($request)->get(['id', 'lead_name']), 'id', 'lead_name'),
            'lead_sources' => $this->mapOptions($this->leadSourcesQuery($request)->get(['id', 'name']), 'id', 'name'),
            'artwork_types' => $this->artworkTypeOptions($request),
            'quotations' => $this->quotationOptions($request),
            'form_statuses' => $this->formStatusOptions($request),
            'payroll_groups' => $this->payrollGroupOptions($request),
            'customer_artworks' => $this->customerArtworkOptions($request),
            'customer_print_specifications' => $this->customerPrintSpecificationOptions($request),
            'sales_order_quotations' => $this->salesOrderQuotationOptions($request),
            'job_card_sales_orders' => $this->jobCardSalesOrderOptions($request),
            default => [],
        };
    }

    /**
     * @param  Collection<int, object>|list<object>  $rows
     * @return list<array{value: int|string, label: string}>
     */
    public function mapOptions(Collection|array $rows, string $valueKey, string|\Closure $labelKey): array
    {
        $rows = $rows instanceof Collection ? $rows : collect($rows);

        return $rows->map(function ($row) use ($valueKey, $labelKey) {
            $label = $labelKey instanceof \Closure ? $labelKey($row) : ($row->{$labelKey} ?? '');

            return [
                'value' => $row->{$valueKey},
                'label' => (string) $label,
            ];
        })->values()->all();
    }

    protected function companyId(Request $request): ?int
    {
        $companyId = $request->integer('company_id') ?: tenant()->companyId() ?: auth()->user()?->company_id;

        return $companyId ?: null;
    }

    protected function branchId(Request $request): ?int
    {
        $branchId = $request->integer('branch_id') ?: tenant()->branchId() ?: auth()->user()?->default_branch_id;

        return $branchId ?: null;
    }

    protected function companiesQuery(Request $request): Builder
    {
        $query = Company::query()->where('is_active', true)->orderBy('name');

        if (! auth()->user()?->hasRole('Super Admin')) {
            $query->where('id', auth()->user()?->company_id);
        }

        return $query;
    }

    protected function branchesQuery(Request $request): Builder
    {
        $query = Branch::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    protected function customersQuery(Request $request): Builder
    {
        $query = Customer::query()->orderBy('company_name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function vendorsQuery(Request $request): Builder
    {
        $query = Vendor::query()->orderBy('vendor_name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    protected function categoriesQuery(Request $request): Builder
    {
        $query = InventoryCategory::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function brandsQuery(Request $request): Builder
    {
        $query = Brand::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function uomsQuery(Request $request): Builder
    {
        $query = UnitOfMeasure::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function segmentsQuery(Request $request): Builder
    {
        $query = CustomerSegment::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    protected function departmentsQuery(Request $request): Builder
    {
        $query = Department::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    protected function employeesQuery(Request $request): Builder
    {
        $query = Employee::query()->where('is_active', true)->orderBy('first_name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    /**
     * Users that can be assigned as production floor operators.
     * Includes active logins and HR-onboarded staff awaiting activation.
     */
    protected function operatorsQuery(Request $request): Builder
    {
        $query = User::query()
            ->where(function (Builder $builder) {
                $builder->where('is_active', true)
                    ->orWhereNotNull('employee_id');
            })
            ->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where(function (Builder $builder) use ($branchId) {
                $builder->where('default_branch_id', $branchId)
                    ->orWhereNull('default_branch_id');
            });
        }

        return $query;
    }

    protected function leadsQuery(Request $request): Builder
    {
        $query = Lead::query()->orderBy('lead_name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function leadSourcesQuery(Request $request): Builder
    {
        $query = LeadSource::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function artworkTypeOptions(Request $request): array
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            return [];
        }

        return app(CustomerArtworkTypeCatalog::class)->optionsForCompany($companyId);
    }

    protected function quotationsQuery(Request $request): Builder
    {
        $query = Quotation::query()->orderByDesc('quotation_date')->orderByDesc('id');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        if ($customerId = $request->integer('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        return $query->limit(100);
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function quotationOptions(Request $request): array
    {
        return $this->quotationsQuery($request)
            ->get(['id', 'quotation_number'])
            ->map(fn ($row) => [
                'value' => $row->id,
                'label' => (string) $row->quotation_number,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function subcategoryOptions(Request $request): array
    {
        $query = InventorySubcategory::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('inventory_category_id', $categoryId);
        }

        return $query->get()->map(fn ($row) => [
            'value' => $row->id,
            'label' => trim(($row->category?->name ? $row->category->name.' / ' : '').$row->name),
        ])->values()->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function itemOptions(Request $request): array
    {
        $query = InventoryItem::query()->orderBy('item_name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query->get(['id', 'item_name', 'sku'])->map(fn ($row) => [
            'value' => $row->id,
            'label' => trim($row->item_name.($row->sku ? " ({$row->sku})" : '')),
        ])->values()->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function warehouseOptions(Request $request): array
    {
        $query = Warehouse::query()->where('is_active', true)->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query->get(['id', 'code', 'name'])->map(fn ($row) => [
            'value' => $row->id,
            'label' => trim("{$row->code} - {$row->name}"),
        ])->values()->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function priceBookOptions(Request $request): array
    {
        if (! class_exists(\App\Models\Commercial\CommercialPriceBook::class)) {
            return [];
        }

        $query = \App\Models\Commercial\CommercialPriceBook::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($companyId = $this->companyId($request)) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $this->branchId($request)) {
            $query->where(function ($inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        }

        return $this->mapOptions($query->get(['id', 'name']), 'id', 'name');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function formStatusOptions(Request $request): array
    {
        $formKey = $request->string('form_key')->toString();

        if ($formKey === '') {
            return [];
        }

        $service = app(FormStatusOptionService::class);

        if (! $service->formHasConfigurableStatus($formKey)) {
            return [];
        }

        return $service
            ->optionsFor($formKey, $this->companyId($request), $this->branchId($request))
            ->map(fn ($option) => [
                'value' => $option->value,
                'label' => $option->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function payrollGroupOptions(Request $request): array
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            return [];
        }

        $service = app(PayrollGroupService::class);

        return $service->activeForCompany($companyId)
            ->map(fn (PayrollGroupDefinition $group) => [
                'value' => $group->code,
                'label' => $group->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function customerArtworkOptions(Request $request): array
    {
        $customerId = $request->integer('customer_id');

        if (! $customerId) {
            return [];
        }

        $customer = Customer::query()->forTenant()->find($customerId);

        if (! $customer) {
            return [];
        }

        return app(CustomerOrderContextService::class)
            ->artworkLibrary($customer)
            ->map(fn (CustomerArtwork $artwork) => [
                'value' => $artwork->id,
                'label' => $artwork->artwork_name.' · '.$artwork->versionLabel(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function customerPrintSpecificationOptions(Request $request): array
    {
        $customerId = $request->integer('customer_id');

        if (! $customerId) {
            return [];
        }

        $customer = Customer::query()->forTenant()->find($customerId);

        if (! $customer) {
            return [];
        }

        return CustomerPrintSpecification::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerPrintSpecificationStatus::Active)
            ->whereNotNull('inventory_item_id')
            ->with('inventoryItem:id,item_name,sku')
            ->orderBy('name')
            ->get(['id', 'specification_code', 'name', 'inventory_item_id'])
            ->map(fn (CustomerPrintSpecification $spec) => [
                'value' => $spec->id,
                'label' => trim($spec->specification_code.' · '.$spec->name.($spec->inventoryItem?->item_name ? ' · '.$spec->inventoryItem->item_name : '')),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function salesOrderQuotationOptions(Request $request): array
    {
        $query = Quotation::query()
            ->forTenant()
            ->selectableForSalesOrderPicker()
            ->with('customer:id,company_name')
            ->orderByDesc('quotation_date')
            ->orderByDesc('id');

        if ($customerId = $request->integer('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        return $query
            ->get()
            ->map(fn (Quotation $quotation) => [
                'value' => $quotation->id,
                'label' => $quotation->salesOrderPickerLabel(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    protected function jobCardSalesOrderOptions(Request $request): array
    {
        return app(ProductionJobCardEligibilityService::class)->eligibleSalesOrderOptions();
    }
}
