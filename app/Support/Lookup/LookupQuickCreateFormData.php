<?php

namespace App\Support\Lookup;

use App\Enums\CustomerType;
use App\Enums\QuotationItemType;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\JobTitle;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerSegment;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\ItemAttribute;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Platform\FormSettingsService;

class LookupQuickCreateFormData
{
    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function customer(?Customer $customer = null): array
    {
        $companyId = $customer?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $customer?->branch_id ?? tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('customer', $companyId, $branchId, $customer),
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'segments' => CustomerSegment::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'types' => CustomerType::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function lead(?Lead $lead = null): array
    {
        $companyId = $lead?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $lead?->branch_id ?? tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('lead', $companyId, $branchId, $lead),
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->get(),
            'sources' => LeadSource::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'stages' => LeadStage::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('sort_order')->get(),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(),
            'users' => User::query()->when(! auth()->user()->hasRole('Super Admin'), fn ($q) => $q->where('company_id', $companyId))->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function warehouse(?Warehouse $warehouse = null): array
    {
        $companyId = $warehouse?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $warehouse?->branch_id ?? tenant()->branchId();
        $formKey = 'warehouse.create';

        $fields = $this->formSettings->resolvedFields($formKey, $companyId, $branchId, $warehouse);

        foreach (['code' => __('Warehouse Code'), 'name' => __('Warehouse Name'), 'branch_id' => __('Branch')] as $field => $label) {
            $fields[$field] = [
                ...($fields[$field] ?? []),
                'label' => $fields[$field]['label'] ?? $label,
                'required' => true,
                'visible' => true,
                'hidden' => false,
                'read_only' => false,
            ];
        }

        return [
            'formFields' => $fields,
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'selectedBranchId' => $branchId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function item(?InventoryItem $item = null): array
    {
        $companyId = $item?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $item?->branch_id ?? tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('inventory_item', $companyId, $branchId, $item),
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'subcategories' => InventorySubcategory::query()->forTenant()->with('category')->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'units' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'attributes' => ItemAttribute::query()->forTenant()->with('options')->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function category(): array
    {
        return [
            'units' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function subcategory(): array
    {
        return [
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function unit(): array
    {
        return [
            'baseUnits' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function vendor(): array
    {
        return [
            'types' => VendorType::cases(),
            'statuses' => VendorStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function branch(): array
    {
        return [
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function department(): array
    {
        return [
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function employee(): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()->company_id;

        return [
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->get(),
            'departments' => Department::query()->where('company_id', $companyId)->get(),
            'jobTitles' => JobTitle::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('title')->get(),
            'genders' => Gender::cases(),
            'statuses' => EmploymentStatus::cases(),
            'assignableRoles' => auth()->user()?->can('roles.edit')
                ? app(\App\Services\EmailIdentity\EmployeeActivationRoleResolver::class)->assignableRolesFor()
                : collect(),
            'canAssignActivationRole' => auth()->user()?->can('roles.edit') ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function segment(): array
    {
        return [
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function quotation(?int $presetCustomerId = null, ?int $presetLeadId = null): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('quotation', $companyId, $branchId),
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(),
            'leads' => Lead::query()->forTenant()->orderBy('lead_name')->get(),
            'itemTypes' => QuotationItemType::cases(),
            'presetCustomerId' => $presetCustomerId,
            'presetLeadId' => $presetLeadId,
        ];
    }
}
