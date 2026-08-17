<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\DocumentType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\HandlesQuickCreateLookup;
use App\Http\Controllers\Admin\Concerns\ResolvesEntityCode;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesQuotationItems;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Crm\CustomerSegment;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\Vendor;
use App\Models\Platform\SettingsGovernance;
use App\Models\Sales\Quotation;
use App\Rules\DateNotInThePast;
use App\Support\Catalogue\CatalogueService;
use App\Support\Catalogue\ItemAttributeService;
use App\Support\Crm\CustomerArtworkService;
use App\Support\Crm\CustomerPrintSpecificationService;
use App\Support\Hr\EmployeeNumberService;
use App\Support\Hr\PayrollGroupService;
use App\Support\Lookup\LookupQuickCreateFormData;
use App\Services\EmailIdentity\EmployeeOnboardingService;
use App\Support\Organization\JobTitleService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\FormStatusOptionService;
use App\Support\Platform\NumberingService;
use App\Support\QuotationRevisionService;
use App\Support\Sales\QuotationApprovalService;
use App\Support\Sales\QuotationArtworkLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuickCreateLookupController extends Controller
{
    use HandlesFormCustomFields, HandlesQuickCreateLookup, ManagesQuotationItems, ResolvesEntityCode;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected FormStatusOptionService $statusOptions,
        protected LookupQuickCreateFormData $lookupFormData,
        protected ItemAttributeService $itemAttributes,
    ) {}

    public function createCompany(): View
    {
        $this->authorize('create', Company::class);

        return $this->lookupForm('admin.lookups.quick-create.company', [
            'title' => __('Create company'),
            'action' => route('admin.companies.quick-store'),
        ]);
    }

    public function storeCompany(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Company::class);

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:50', Rule::unique('companies', 'code')],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'address' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.company', [
                'title' => __('Create company'),
                'action' => route('admin.companies.quick-store'),
            ]);
        }

        $code = $this->resolveEntityCode(
            $request,
            'name',
            Company::class,
            fn ($query) => $query,
            null,
            50,
        );

        $company = Company::query()->create([
            'name' => $validated['name'],
            'code' => $code,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($company->id, $company->name, __('Company created.'));
    }

    public function createBranch(): View
    {
        $this->authorize('create', Branch::class);

        return $this->lookupForm('admin.lookups.quick-create.branch', array_merge(
            $this->lookupFormData->branch(),
            [
                'title' => __('Create branch'),
                'action' => route('admin.branches.quick-store'),
            ],
        ));
    }

    public function storeBranch(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Branch::class);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        try {
            $validated = $request->validate([
                'company_id' => ['required', 'exists:companies,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [Rule::unique('branches', 'code')->where('company_id', $companyId)],
                ),
                'is_head_office' => ['boolean'],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.branch', array_merge(
                $this->lookupFormData->branch(),
                [
                    'title' => __('Create branch'),
                    'action' => route('admin.branches.quick-store'),
                ],
            ));
        }

        $branch = Branch::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'code' => $this->resolveCompanyScopedCode($request, 'name', Branch::class, $companyId),
            'is_head_office' => $request->boolean('is_head_office'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($branch->id, $branch->name, __('Branch created.'));
    }

    public function createCustomer(): View
    {
        $this->authorize('create', Customer::class);

        return $this->lookupForm('admin.lookups.quick-create.customer', array_merge(
            $this->lookupFormData->customer(),
            [
                'title' => __('Create customer'),
                'action' => route('admin.crm.customers.quick-store'),
            ],
        ));
    }

    public function storeCustomer(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Customer::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds($request);

        try {
            $validated = $this->validateCustomerQuickCreate($request, $companyId, $branchId);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.customer', array_merge(
                $this->lookupFormData->customer(),
                [
                    'title' => __('Create customer'),
                    'action' => route('admin.crm.customers.quick-store'),
                ],
            ));
        }

        $data = $this->formSettings->applyDefaults('customer', $validated, $companyId, $branchId);
        $data['status'] ??= CustomerStatus::Active->value;
        [$data, $customData] = $this->partitionCustomFields('customer', $data, $companyId, $branchId);

        $customer = Customer::query()->create([
            ...collect($data)->except(['segment_ids'])->toArray(),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_code' => $this->nextCustomerCode($companyId),
            'credit_limit' => $data['credit_limit'] ?? 0,
        ]);

        if (! empty($data['segment_ids'])) {
            $customer->segments()->sync($data['segment_ids']);
        }

        $this->syncCustomFields($customer, 'customer', $customData, $companyId);

        return $this->quickCreateResponse($customer->id, $customer->company_name, __('Customer created.'));
    }

    public function createLead(): View
    {
        $this->authorize('create', Lead::class);

        return $this->lookupForm('admin.lookups.quick-create.lead', array_merge(
            $this->lookupFormData->lead(),
            [
                'title' => __('Create lead'),
                'action' => route('admin.crm.leads.quick-store'),
            ],
        ));
    }

    public function storeLead(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Lead::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds($request);

        try {
            $validated = $this->validateLeadQuickCreate($request, $companyId, $branchId);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.lead', array_merge(
                $this->lookupFormData->lead(),
                [
                    'title' => __('Create lead'),
                    'action' => route('admin.crm.leads.quick-store'),
                ],
            ));
        }

        $data = $this->formSettings->applyDefaults('lead', $validated, $companyId, $branchId);
        [$data, $customData] = $this->partitionCustomFields('lead', $data, $companyId, $branchId);

        if (empty($data['stage_id'])) {
            $data['stage_id'] = LeadStage::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->value('id');
        }

        $lead = Lead::query()->create([
            ...collect($data)->except(['company_id', 'branch_id'])->toArray(),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'status' => $data['status'] ?? LeadStatus::Open,
        ]);

        $this->syncCustomFields($lead, 'lead', $customData, $companyId);

        return $this->quickCreateResponse($lead->id, $lead->lead_name, __('Lead created.'));
    }

    public function createLeadSource(): View
    {
        abort_unless(auth()->user()?->can('crm.leads.create'), 403);

        return $this->lookupForm('admin.lookups.quick-create.lead-source', [
            'title' => __('Create lead source'),
            'action' => route('admin.crm.lead-sources.quick-store'),
            'companies' => $this->activeCompanies(),
        ]);
    }

    public function storeLeadSource(Request $request): JsonResponse|Response
    {
        abort_unless(auth()->user()?->can('crm.leads.create'), 403);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) ($request->input('company_id') ?: tenant()->companyId() ?: auth()->user()->company_id)
            : (int) auth()->user()->company_id;

        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'is_active' => ['boolean'],
            ];

            if (auth()->user()->hasRole('Super Admin')) {
                $rules['company_id'] = ['required', 'exists:companies,id'];
            }

            $validated = $request->validate($rules);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.lead-source', [
                'title' => __('Create lead source'),
                'action' => route('admin.crm.lead-sources.quick-store'),
                'companies' => $this->activeCompanies(),
            ]);
        }

        $source = LeadSource::query()->create([
            'name' => $validated['name'],
            'company_id' => $companyId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($source->id, $source->name, __('Lead source created.'));
    }

    public function createArtworkType(): View
    {
        abort_unless(auth()->user()?->can('crm.customers.edit'), 403);

        return $this->lookupForm('admin.lookups.quick-create.artwork-type', [
            'title' => __('Create artwork type'),
            'action' => route('admin.crm.artwork-types.quick-store'),
            'companies' => $this->activeCompanies(),
        ]);
    }

    public function storeArtworkType(Request $request, \App\Support\Crm\CustomerArtworkTypeCatalog $catalog): JsonResponse|Response
    {
        abort_unless(auth()->user()?->can('crm.customers.edit'), 403);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) ($request->input('company_id') ?: tenant()->companyId() ?: auth()->user()->company_id)
            : (int) auth()->user()->company_id;

        try {
            $rules = [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('customer_artwork_types', 'name')->where(fn ($query) => $query->where('company_id', $companyId)),
                ],
                'is_active' => ['boolean'],
            ];

            if (auth()->user()->hasRole('Super Admin')) {
                $rules['company_id'] = ['required', 'exists:companies,id'];
            }

            $validated = $request->validate($rules);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.artwork-type', [
                'title' => __('Create artwork type'),
                'action' => route('admin.crm.artwork-types.quick-store'),
                'companies' => $this->activeCompanies(),
            ]);
        }

        $type = $catalog->create(
            $companyId,
            $validated['name'],
            $request->boolean('is_active', true),
        );

        return $this->quickCreateStringResponse($type->code, $type->name, __('Artwork type created.'));
    }

    public function createQuotation(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        return $this->lookupForm('admin.lookups.quick-create.quotation', array_merge(
            $this->lookupFormData->quotation(
                $request->integer('customer_id') ?: null,
                $request->integer('lead_id') ?: null,
            ),
            [
                'title' => __('Create quotation'),
                'action' => route('admin.quotations.quick-store'),
            ],
        ));
    }

    public function storeQuotation(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Quotation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds($request);

        try {
            $header = $this->validateQuotationHeader($request, $companyId, $branchId);
            $request->validate(['customer_artwork_id' => ['nullable', 'integer']]);
            ['items' => $items, 'totals' => $totals] = $this->validatedItems($request);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.quotation', array_merge(
                $this->lookupFormData->quotation(
                    $request->integer('customer_id') ?: null,
                    $request->integer('lead_id') ?: null,
                ),
                [
                    'title' => __('Create quotation'),
                    'action' => route('admin.quotations.quick-store'),
                ],
            ));
        }

        [$header, $customData] = $this->partitionCustomFields('quotation', $header, $companyId, $branchId);

        $quotation = Quotation::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_number' => app(NumberingService::class)->next(
                DocumentType::Quotation,
                $companyId,
                $branchId,
            ),
            'prepared_by' => auth()->id(),
            'status' => QuotationStatus::Draft,
            'revision_number' => 1,
            ...$totals,
        ]);

        $this->syncCustomFields($quotation, 'quotation', $customData, $companyId);
        $this->syncItems($quotation, $items, $totals);
        QuotationRevisionService::snapshot($quotation);

        app(QuotationApprovalService::class)->publishOnCreate($quotation->fresh(), (int) auth()->id());

        if ($request->filled('customer_artwork_id')) {
            app(QuotationArtworkLinkService::class)->linkFromLibrary(
                $quotation->fresh(),
                (int) $request->input('customer_artwork_id'),
                (int) auth()->id(),
            );
        }

        return $this->quickCreateResponse($quotation->id, $quotation->quotation_number, __('Quotation created and published.'));
    }

    public function createVendor(): View
    {
        $this->authorize('create', Vendor::class);

        return $this->lookupForm('admin.lookups.quick-create.vendor', array_merge(
            $this->lookupFormData->vendor(),
            [
                'title' => __('Create vendor'),
                'action' => route('admin.procurement.vendors.quick-store'),
            ],
        ));
    }

    public function storeVendor(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Vendor::class);

        ['companyId' => $companyId] = $this->resolveTenantIds($request);

        try {
            $validated = $request->validate([
                'vendor_name' => ['required', 'string', 'max:255'],
                'vendor_type' => ['required', Rule::enum(VendorType::class)],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['nullable', 'email', 'max:255'],
                'kra_pin' => ['nullable', 'string', 'max:50'],
                'address' => ['nullable', 'string'],
                'payment_terms' => ['nullable', 'string', 'max:255'],
                'status' => ['required', Rule::enum(VendorStatus::class)],
                'notes' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.vendor', array_merge(
                $this->lookupFormData->vendor(),
                [
                    'title' => __('Create vendor'),
                    'action' => route('admin.procurement.vendors.quick-store'),
                ],
            ));
        }

        $vendor = Vendor::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'vendor_code' => $this->nextNumber(DocumentType::Vendor, $companyId),
        ]);

        return $this->quickCreateResponse($vendor->id, $vendor->vendor_name, __('Vendor created.'));
    }

    public function createCategory(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return $this->lookupForm('admin.lookups.quick-create.category', array_merge(
            $this->lookupFormData->category(),
            [
                'title' => __('Create category'),
                'action' => route('admin.inventory.catalogue.categories.quick-store'),
            ],
        ));
    }

    public function storeCategory(Request $request): JsonResponse|Response
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds();

        try {
            $validated = $request->validate([
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [Rule::unique('inventory_categories', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)],
                ),
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'default_uom_id' => ['nullable', Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
                'reorder_behavior' => ['required', Rule::in(['standard', 'made_to_order', 'non_stock', 'critical'])],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.category', array_merge(
                $this->lookupFormData->category(),
                [
                    'title' => __('Create category'),
                    'action' => route('admin.inventory.catalogue.categories.quick-store'),
                ],
            ));
        }

        $category = InventoryCategory::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $this->resolveBranchScopedCode($request, 'name', InventoryCategory::class, $companyId, $branchId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($category->id, $category->name, __('Category created.'));
    }

    public function createSubcategory(Request $request): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        $categoryId = $request->integer('category_id') ?: null;
        $defaultCategoryId = $categoryId && InventoryCategory::query()->forTenant()->whereKey($categoryId)->exists()
            ? $categoryId
            : null;

        return $this->lookupForm('admin.lookups.quick-create.subcategory', array_merge(
            $this->lookupFormData->subcategory(),
            [
                'title' => __('Create subcategory'),
                'action' => route('admin.inventory.catalogue.subcategories.quick-store'),
                'defaultCategoryId' => $defaultCategoryId,
            ],
        ));
    }

    public function storeSubcategory(Request $request): JsonResponse|Response
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds();

        try {
            $validated = $request->validate([
                'inventory_category_id' => ['required', Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [
                        Rule::unique('inventory_subcategories', 'code')
                            ->where('company_id', $companyId)
                            ->where('branch_id', $branchId)
                            ->where('inventory_category_id', $request->integer('inventory_category_id')),
                    ],
                ),
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.subcategory', array_merge(
                $this->lookupFormData->subcategory(),
                [
                    'title' => __('Create subcategory'),
                    'action' => route('admin.inventory.catalogue.subcategories.quick-store'),
                ],
            ));
        }

        $subcategory = InventorySubcategory::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $this->resolveBranchScopedCode(
                $request,
                'name',
                InventorySubcategory::class,
                $companyId,
                $branchId,
                null,
                50,
                ['inventory_category_id' => $request->integer('inventory_category_id')],
            ),
            'is_active' => $request->boolean('is_active', true),
        ])->load('category');

        $label = trim(($subcategory->category?->name ? $subcategory->category->name.' / ' : '').$subcategory->name);

        return $this->quickCreateResponse($subcategory->id, $label, __('Subcategory created.'));
    }

    public function createBrand(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return $this->lookupForm('admin.lookups.quick-create.brand', [
            'title' => __('Create brand'),
            'action' => route('admin.inventory.catalogue.brands.quick-store'),
        ]);
    }

    public function storeBrand(Request $request): JsonResponse|Response
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds();

        try {
            $validated = $request->validate([
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [Rule::unique('brands', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)],
                ),
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.brand', [
                'title' => __('Create brand'),
                'action' => route('admin.inventory.catalogue.brands.quick-store'),
            ]);
        }

        unset($validated['logo']);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('catalogue/brands', 'public');
        }

        $brand = Brand::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $this->resolveBranchScopedCode($request, 'name', Brand::class, $companyId, $branchId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($brand->id, $brand->name, __('Brand created.'));
    }

    public function createUom(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return $this->lookupForm('admin.lookups.quick-create.uom', array_merge(
            $this->lookupFormData->unit(),
            [
                'title' => __('Create unit of measure'),
                'action' => route('admin.inventory.catalogue.uoms.quick-store'),
            ],
        ));
    }

    public function storeUom(Request $request): JsonResponse|Response
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds();

        try {
            $validated = $request->validate([
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [Rule::unique('units_of_measure', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)],
                ),
                'name' => ['required', 'string', 'max:255'],
                'base_unit_id' => ['nullable', Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
                'conversion_factor' => ['nullable', 'numeric', 'min:0.0001'],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.uom', array_merge(
                $this->lookupFormData->unit(),
                [
                    'title' => __('Create unit of measure'),
                    'action' => route('admin.inventory.catalogue.uoms.quick-store'),
                ],
            ));
        }

        if (empty($validated['base_unit_id'])) {
            $validated['base_unit_id'] = null;
            $validated['conversion_factor'] = 1;
        } else {
            $validated['conversion_factor'] = $validated['conversion_factor'] ?? 1;
        }

        $unit = UnitOfMeasure::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $this->resolveBranchScopedCode($request, 'name', UnitOfMeasure::class, $companyId, $branchId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($unit->id, $unit->name, __('Unit of measure created.'));
    }

    public function createItem(): View
    {
        $this->authorize('create', InventoryItem::class);

        return $this->lookupForm('admin.lookups.quick-create.item', array_merge(
            $this->lookupFormData->item(),
            [
                'title' => __('Create item'),
                'action' => route('admin.inventory.items.quick-store'),
            ],
        ));
    }

    public function storeItem(Request $request, CatalogueService $catalogue): JsonResponse|Response
    {
        $this->authorize('create', InventoryItem::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds();

        try {
            $validated = $this->validateItemQuickCreate($request, $companyId, $branchId);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.item', array_merge(
                $this->lookupFormData->item(),
                [
                    'title' => __('Create item'),
                    'action' => route('admin.inventory.items.quick-store'),
                ],
            ));
        }

        [$data, $customData] = $this->partitionCustomFields('inventory_item', $validated, $companyId, $branchId);
        $data['reorder_level'] = $data['reorder_level'] ?? 0;
        $data['reorder_quantity'] = $data['reorder_quantity'] ?? 0;
        $data['standard_cost'] = $data['standard_cost'] ?? 0;
        $data['sku'] = $this->resolveItemSku($data, $request, $catalogue);

        $item = InventoryItem::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->itemAttributes->sync($item, $request->input('attributes', []));
        $this->syncCustomFields($item, 'inventory_item', $customData, $companyId);

        $label = trim($item->item_name.($item->sku ? " ({$item->sku})" : ''));

        return $this->quickCreateResponse($item->id, $label, __('Item created.'));
    }

    public function createWarehouse(): View
    {
        $this->authorize('create', Warehouse::class);

        return $this->lookupForm('admin.lookups.quick-create.warehouse', array_merge(
            $this->lookupFormData->warehouse(),
            [
                'title' => __('Create warehouse'),
                'action' => route('admin.inventory.warehouses.quick-store'),
            ],
        ));
    }

    public function storeWarehouse(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Warehouse::class);

        ['companyId' => $companyId, 'branchId' => $tenantBranchId] = $this->resolveTenantIds();
        $branchId = (int) ($request->input('branch_id') ?: $tenantBranchId);

        try {
            $validated = $this->validateWarehouseQuickCreate($request, $companyId, $branchId);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.warehouse', array_merge(
                $this->lookupFormData->warehouse(),
                [
                    'title' => __('Create warehouse'),
                    'action' => route('admin.inventory.warehouses.quick-store'),
                ],
            ));
        }

        [$data, $customData] = $this->partitionCustomFields('warehouse.create', $validated, $companyId, $branchId);

        $warehouse = Warehouse::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->syncCustomFields($warehouse, 'warehouse.create', $customData, $companyId);

        return $this->quickCreateResponse($warehouse->id, "{$warehouse->code} - {$warehouse->name}", __('Warehouse created.'));
    }

    public function createSegment(): View
    {
        $this->authorize('create', CustomerSegment::class);

        return $this->lookupForm('admin.lookups.quick-create.segment', array_merge(
            $this->lookupFormData->segment(),
            [
                'title' => __('Create segment'),
                'action' => route('admin.crm.segments.quick-store'),
            ],
        ));
    }

    public function storeSegment(Request $request): JsonResponse|Response
    {
        $this->authorize('create', CustomerSegment::class);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        try {
            $validated = $request->validate([
                'company_id' => ['required', 'exists:companies,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [Rule::unique('customer_segments', 'code')->where('company_id', $companyId)],
                ),
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.segment', array_merge(
                $this->lookupFormData->segment(),
                [
                    'title' => __('Create segment'),
                    'action' => route('admin.crm.segments.quick-store'),
                ],
            ));
        }

        $segment = CustomerSegment::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'code' => $this->resolveCompanyScopedCode($request, 'name', CustomerSegment::class, $companyId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($segment->id, $segment->name, __('Segment created.'));
    }

    public function createDepartment(): View
    {
        $this->authorize('create', Department::class);

        return $this->lookupForm('admin.lookups.quick-create.department', array_merge(
            $this->lookupFormData->department(),
            [
                'title' => __('Create department'),
                'action' => route('admin.departments.quick-store'),
            ],
        ));
    }

    public function storeDepartment(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Department::class);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        try {
            $validated = $request->validate([
                'company_id' => ['required', 'exists:companies,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [Rule::unique('departments', 'code')->where('company_id', $companyId)],
                ),
                'description' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.department', array_merge(
                $this->lookupFormData->department(),
                [
                    'title' => __('Create department'),
                    'action' => route('admin.departments.quick-store'),
                ],
            ));
        }

        $department = Department::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'code' => $this->resolveCompanyScopedCode($request, 'name', Department::class, $companyId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->quickCreateResponse($department->id, $department->name, __('Department created.'));
    }

    public function createEmployee(): View
    {
        $this->authorize('create', Employee::class);

        return $this->lookupForm('admin.lookups.quick-create.employee', array_merge(
            $this->lookupFormData->employee(),
            [
                'title' => __('Create employee'),
                'action' => route('admin.employees.quick-store'),
            ],
        ));
    }

    public function storeEmployee(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Employee::class);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        $formData = array_merge(
            $this->lookupFormData->employee(),
            [
                'title' => __('Create employee'),
                'action' => route('admin.employees.quick-store'),
            ],
        );

        try {
            $validated = $request->validate([
                'company_id' => ['required', 'exists:companies,id'],
                'branch_id' => [
                    'required',
                    Rule::exists('branches', 'id')->where('company_id', $companyId),
                ],
                'department_id' => [
                    'nullable',
                    Rule::exists('departments', 'id')->where('company_id', $companyId),
                ],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'gender' => ['nullable', Rule::enum(Gender::class)],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'job_title_id' => [
                    'nullable',
                    Rule::exists('job_titles', 'id')->where('company_id', $companyId),
                ],
                'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
                'is_active' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.employee', $formData);
        }

        $employee = Employee::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'employee_number' => app(EmployeeNumberService::class)->nextForCompany($companyId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        app(JobTitleService::class)->syncEmployeeDesignation($employee);
        app(EmployeeOnboardingService::class)->ensureOnboarded(
            $employee,
            $validated['email'],
            $validated['activation_role'] ?? null,
        );

        $label = trim("{$employee->first_name} {$employee->last_name}")." ({$employee->employee_number})";

        return $this->quickCreateResponse($employee->id, $label, __('Employee created.'));
    }

    public function createOperator(): View
    {
        $this->authorize('create', Employee::class);

        return $this->lookupForm('admin.lookups.quick-create.operator', array_merge(
            $this->lookupFormData->employee(),
            [
                'title' => __('Create operator'),
                'action' => route('admin.operators.quick-store'),
            ],
        ));
    }

    public function storeOperator(Request $request): JsonResponse|Response
    {
        $this->authorize('create', Employee::class);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        $formData = array_merge(
            $this->lookupFormData->employee(),
            [
                'title' => __('Create operator'),
                'action' => route('admin.operators.quick-store'),
            ],
        );

        try {
            $validated = $request->validate([
                'company_id' => ['required', 'exists:companies,id'],
                'branch_id' => [
                    'required',
                    Rule::exists('branches', 'id')->where('company_id', $companyId),
                ],
                'department_id' => [
                    'nullable',
                    Rule::exists('departments', 'id')->where('company_id', $companyId),
                ],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'gender' => ['nullable', Rule::enum(Gender::class)],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'job_title_id' => [
                    'nullable',
                    Rule::exists('job_titles', 'id')->where('company_id', $companyId),
                ],
                'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
                'is_active' => ['boolean'],
                'activation_role' => ['nullable', 'string', 'max:100'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.operator', $formData);
        }

        if (empty($validated['activation_role'])) {
            $validated['activation_role'] = 'Production';
        }

        $employee = Employee::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'employee_number' => app(EmployeeNumberService::class)->nextForCompany($companyId),
            'is_active' => $request->boolean('is_active', true),
        ]);

        app(JobTitleService::class)->syncEmployeeDesignation($employee);
        app(EmployeeOnboardingService::class)->ensureOnboarded(
            $employee,
            $validated['email'],
            $validated['activation_role'] ?? 'Production',
        );

        $employee->loadMissing('user:id,name,employee_id');
        $user = $employee->user;

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => __('Operator login could not be created. Check HR onboarding settings.'),
            ]);
        }

        return $this->quickCreateResponse(
            $user->id,
            $user->name,
            __('Operator created. They can be assigned now; activation email was sent if required.'),
        );
    }

    protected function lookupForm(string $view, array $data): View
    {
        return view($view, $data);
    }

    protected function lookupValidationResponse(
        Request $request,
        ValidationException $exception,
        string $view,
        array $data,
    ): Response {
        return response()->view($view, array_merge($data, [
            'errors' => $exception->validator->errors(),
        ]), 422);
    }

    protected function activeCompanies()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return Company::query()->where('is_active', true)->orderBy('name')->get();
        }

        return Company::query()->where('id', auth()->user()->company_id)->get();
    }

    protected function uniqueCompanyCode(string $name): string
    {
        $base = Str::upper(Str::slug(Str::limit($name, 20, ''), '_')) ?: 'COMP';
        $code = $base;
        $suffix = 1;

        while (Company::query()->where('code', $code)->exists()) {
            $code = $base.'_'.$suffix;
            $suffix++;
        }

        return Str::limit($code, 50, '');
    }

    /**
     * @return array{companyId: int, branchId: int}
     */
    protected function resolveTenantIds(?Request $request = null): array
    {
        $request ??= request();

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) ($request->input('company_id') ?: tenant()->companyId() ?: auth()->user()->company_id)
            : (int) auth()->user()->company_id;

        $branchId = auth()->user()->hasRole('Super Admin')
            ? (int) ($request->input('branch_id') ?: tenant()->branchId() ?: auth()->user()->default_branch_id)
            : (int) (tenant()->branchId() ?: auth()->user()->default_branch_id);

        return compact('companyId', 'branchId');
    }

    protected function nextCustomerCode(int $companyId): string
    {
        $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id;

        return app(NumberingService::class)->next(
            DocumentType::Customer,
            $companyId,
            $branchId ? (int) $branchId : null,
        );
    }

    protected function nextNumber(DocumentType $type, int $companyId): string
    {
        $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id;

        return app(NumberingService::class)->next(
            $type,
            $companyId,
            $branchId ? (int) $branchId : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateCustomerQuickCreate(Request $request, int $companyId, int $branchId): array
    {
        $rules = $this->formSettings->mergeValidationRules('customer', [
            'customer_type' => [Rule::enum(CustomerType::class)],
            'company_name' => ['string', 'max:255'],
            'contact_person' => ['string', 'max:255'],
            'phone' => ['string', 'max:50'],
            'alternative_phone' => ['string', 'max:50'],
            'email' => ['email'],
            'kra_pin' => ['string', 'max:50'],
            'physical_address' => ['string'],
            'postal_address' => ['string'],
            'city' => ['string', 'max:100'],
            'website' => ['string', 'max:255'],
            'credit_limit' => ['numeric', 'min:0'],
            'payment_terms' => ['string', 'max:100'],
            'notes' => ['string'],
            'segment_ids' => ['array'],
            'segment_ids.*' => ['exists:customer_segments,id'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId);

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateLeadQuickCreate(Request $request, int $companyId, int $branchId): array
    {
        $rules = $this->formSettings->mergeValidationRules('lead', [
            'lead_source_id' => [Rule::exists('lead_sources', 'id')->where('company_id', $companyId)],
            'assigned_to' => ['exists:users,id'],
            'customer_id' => [Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'stage_id' => [Rule::exists('lead_stages', 'id')->where('company_id', $companyId)],
            'lead_name' => ['string', 'max:255'],
            'company_name' => ['string', 'max:255'],
            'phone' => ['string', 'max:50'],
            'email' => ['email'],
            'estimated_value' => ['numeric', 'min:0'],
            'probability' => ['integer', 'min:0', 'max:100'],
            'expected_close_date' => ['date', new DateNotInThePast],
            'status' => $this->statusOptions->validationRules('lead', $companyId, $branchId, false),
            'notes' => ['string'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId);

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateItemQuickCreate(Request $request, int $companyId, int $branchId): array
    {
        $this->formSettings->withoutHiddenInputs($request, 'inventory_item', $companyId, $branchId);

        return $request->validate($this->formSettings->mergeValidationRules('inventory_item', [
            'inventory_category_id' => [Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'subcategory_id' => [
                'nullable',
                Rule::exists('inventory_subcategories', 'id')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where(fn ($query) => $query->where('inventory_category_id', $request->integer('inventory_category_id'))),
            ],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'unit_of_measure_id' => [Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'sku' => ['string', 'max:50'],
            'item_name' => ['string', 'max:255'],
            'description' => ['string'],
            'reorder_level' => ['numeric', 'min:0'],
            'reorder_quantity' => ['numeric', 'min:0'],
            'standard_cost' => ['numeric', 'min:0'],
            'is_active' => ['boolean'],
            'stock_role' => ['required', Rule::enum(\App\Enums\InventoryStockRole::class)],
        ], $companyId, $branchId));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateWarehouseQuickCreate(Request $request, int $companyId, int $branchId): array
    {
        $this->formSettings->withoutHiddenInputs($request, 'warehouse.create', $companyId, $branchId);

        $rules = $this->relaxCodeRulesForCreate(
            $this->formSettings->mergeValidationRules('warehouse.create', [
                'code' => array_merge(
                    $this->nullableCodeRules(50),
                    [
                        Rule::unique('warehouses', 'code')
                            ->where('company_id', $companyId)
                            ->where('branch_id', $branchId),
                    ],
                ),
                'name' => ['required', 'string', 'max:255'],
                'branch_id' => ['required', Rule::exists('branches', 'id')->where('company_id', $companyId)->where('is_active', true)],
                'location' => ['string', 'max:255'],
                'notes' => ['string'],
                'description' => ['string'],
                'is_active' => ['boolean'],
            ], $companyId, $branchId, serverProvidedFields: ['branch_id']),
            50,
        );

        $validated = $request->validate($rules);
        $descriptionParts = [];

        if (filled($validated['location'] ?? null)) {
            $descriptionParts[] = __('Location: :location', ['location' => $validated['location']]);
        }

        if (filled($validated['notes'] ?? null)) {
            $descriptionParts[] = __('Notes: :notes', ['notes' => $validated['notes']]);
        }

        if (filled($validated['description'] ?? null)) {
            $descriptionParts[] = $validated['description'];
        }

        $validated['description'] = implode("\n\n", $descriptionParts);

        if (blank($validated['code'] ?? null)) {
            $validated['code'] = $this->resolveBranchScopedCode(
                $request,
                'name',
                Warehouse::class,
                $companyId,
                $branchId,
            );
        }

        return collect($validated)->only(['code', 'name', 'description', 'is_active'])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateQuotationHeader(Request $request, int $companyId, int $branchId): array
    {
        $rules = $this->formSettings->mergeValidationRules('quotation', [
            'customer_id' => [Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'lead_id' => [Rule::exists('leads', 'id')->where('company_id', $companyId)],
            'quotation_date' => ['date'],
            'valid_until' => ['date', 'after_or_equal:quotation_date', new DateNotInThePast],
            'currency' => ['string', 'size:3'],
            'notes' => ['string'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId);

        $data = $request->validate($rules);

        return $this->formSettings->applyDefaults('quotation', $data, $companyId, $branchId);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveItemSku(array $data, Request $request, CatalogueService $catalogue): string
    {
        if (filled($data['sku'] ?? null)) {
            return (string) $data['sku'];
        }

        $category = InventoryCategory::query()->findOrFail($data['inventory_category_id']);
        $subcategory = filled($data['subcategory_id'] ?? null)
            ? InventorySubcategory::query()->find($data['subcategory_id'])
            : null;
        $brandName = filled($data['brand_name'] ?? null) ? (string) $data['brand_name'] : null;

        return $catalogue->structuredSku($category, $subcategory, $brandName, (string) $data['item_name'], $request->input('sku_parts', []));
    }

    public function createFormStatus(Request $request): View
    {
        $this->authorize('update', new SettingsGovernance());

        $formKey = $request->string('form_key')->toString();
        abort_unless($this->statusOptions->formHasConfigurableStatus($formKey), 404);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? ($request->integer('company_id') ?: tenant()->companyId() ?? auth()->user()->company_id)
            : (int) auth()->user()->company_id;
        $branchId = $request->integer('branch_id') ?: tenant()->branchId();

        return $this->lookupForm('admin.lookups.quick-create.form-status', [
            'title' => __('Add status'),
            'action' => route('admin.form-statuses.quick-store'),
            'formKey' => $formKey,
            'formLabel' => config("form_registry.forms.{$formKey}.label") ?? Str::headline($formKey),
            'companyId' => $companyId,
            'branchId' => $branchId,
        ]);
    }

    public function storeFormStatus(Request $request): JsonResponse|Response
    {
        $this->authorize('update', new SettingsGovernance());

        $formKey = $request->string('form_key')->toString();
        abort_unless($this->statusOptions->formHasConfigurableStatus($formKey), 404);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;
        $branchId = $request->filled('branch_id') ? $request->integer('branch_id') : null;

        $formData = [
            'title' => __('Add status'),
            'action' => route('admin.form-statuses.quick-store'),
            'formKey' => $formKey,
            'formLabel' => config("form_registry.forms.{$formKey}.label") ?? Str::headline($formKey),
            'companyId' => $companyId,
            'branchId' => $branchId,
        ];

        try {
            $validated = $request->validate([
                'form_key' => ['required', 'string', Rule::in($this->statusOptions->formsWithConfigurableStatus())],
                'company_id' => ['required', 'exists:companies,id'],
                'branch_id' => ['nullable', 'exists:branches,id'],
                'value' => [
                    'required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/',
                    Rule::unique('form_status_options', 'value')
                        ->where('company_id', $companyId)
                        ->where('form_key', $formKey)
                        ->where(fn ($query) => $branchId
                            ? $query->where('branch_id', $branchId)
                            : $query->whereNull('branch_id')),
                ],
                'label' => ['required', 'string', 'max:120'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.form-status', $formData);
        }

        $option = $this->statusOptions->createOption(
            $validated['form_key'],
            $companyId,
            $branchId,
            $validated['value'],
            $validated['label'],
        );

        return $this->quickCreateStringResponse($option->value, $option->label, __('Status option created.'));
    }

    public function createPayrollGroup(): View
    {
        $this->authorize('create', \App\Models\Hr\EmployeeCompensation::class);

        return $this->lookupForm('admin.lookups.quick-create.payroll-group', [
            'title' => __('Create payroll group'),
            'action' => route('admin.payroll-groups.quick-store'),
        ]);
    }

    public function storePayrollGroup(Request $request): JsonResponse|Response
    {
        $this->authorize('create', \App\Models\Hr\EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? (int) auth()->user()?->company_id;

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:30'],
            ]);
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.payroll-group', [
                'title' => __('Create payroll group'),
                'action' => route('admin.payroll-groups.quick-store'),
            ]);
        }

        $group = app(PayrollGroupService::class)->create(
            $companyId,
            $validated['name'],
            $validated['code'] ?? null,
        );

        return $this->quickCreateStringResponse($group->code, $group->name, __('Payroll group created.'));
    }

    public function createCustomerArtwork(Request $request): View
    {
        $customerId = $request->integer('customer_id');
        $customer = null;

        if ($customerId) {
            $customer = Customer::query()->forTenant()->findOrFail($customerId);
            $this->authorize('update', $customer);
        }

        return $this->lookupForm('admin.lookups.quick-create.customer-artwork', [
            'title' => __('Create artwork'),
            'action' => route('admin.crm.customer-artworks.quick-store'),
            'customer' => $customer,
            'artworkTypes' => $customer
                ? app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->optionsForCompany((int) $customer->company_id)
                : [],
        ]);
    }

    public function storeCustomerArtwork(Request $request, CustomerArtworkService $service): JsonResponse|Response
    {
        try {
            $customer = Customer::query()->forTenant()->findOrFail($request->integer('customer_id'));
            $catalog = app(\App\Support\Crm\CustomerArtworkTypeCatalog::class);

            $validated = $request->validate([
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                'artwork_name' => ['required', 'string', 'max:255'],
                'artwork_type' => $catalog->validationRules((int) $customer->company_id, required: true),
                'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
            ]);
        } catch (ValidationException $exception) {
            $customer = Customer::query()->forTenant()->find($request->integer('customer_id'));

            return $this->lookupValidationResponse($request, $exception, 'admin.lookups.quick-create.customer-artwork', [
                'title' => __('Create artwork'),
                'action' => route('admin.crm.customer-artworks.quick-store'),
                'customer' => $customer,
                'artworkTypes' => $customer
                    ? app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->optionsForCompany((int) $customer->company_id)
                    : [],
            ]);
        }

        $customer = Customer::query()->forTenant()->findOrFail($validated['customer_id']);
        $this->authorize('update', $customer);

        $artwork = $service->uploadVersion(
            $customer,
            $request->file('file'),
            $validated['artwork_name'],
            $validated['artwork_type'],
            (int) auth()->id(),
        );

        $label = $artwork->artwork_name.' · '.$artwork->versionLabel();

        return $this->quickCreateResponse($artwork->id, $label, __('Artwork uploaded.'));
    }

    public function createPrintSpecification(Request $request): View
    {
        $customerId = $request->integer('customer_id');
        $customer = null;

        if ($customerId) {
            $customer = Customer::query()->forTenant()->findOrFail($customerId);
            $this->authorize('update', $customer);
        }

        return $this->lookupForm('admin.lookups.quick-create.print-specification', $this->printSpecificationQuickCreateFormData($customer));
    }

    public function editPrintSpecification(CustomerPrintSpecification $printSpecification): View
    {
        $printSpecification->loadMissing(['customer', 'inventoryItem', 'activeArtworkVersion']);
        $customer = $printSpecification->customer;
        $this->authorize('update', $customer);

        if ($printSpecification->isReadOnly()) {
            abort(403, __('This specification cannot be edited.'));
        }

        return $this->lookupForm(
            'admin.lookups.quick-create.print-specification',
            $this->printSpecificationQuickCreateFormData($customer, $printSpecification),
        );
    }

    public function updatePrintSpecification(
        Request $request,
        CustomerPrintSpecification $printSpecification,
        CustomerPrintSpecificationService $specifications,
        CustomerArtworkService $artworks,
    ): JsonResponse|Response {
        $printSpecification->loadMissing('customer');
        $customer = $printSpecification->customer;
        $this->authorize('update', $customer);

        try {
            $catalog = app(\App\Support\Crm\CustomerArtworkTypeCatalog::class);

            $validated = $request->validate(array_merge([
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                'product_name' => ['required', 'string', 'max:255'],
                'inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', Rule::enum(CustomerPrintSpecificationStatus::class)],
                'default_quantity' => ['nullable', 'numeric', 'min:0'],
                'default_unit_price' => ['nullable', 'numeric', 'min:0'],
                'artwork_file' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
                'artwork_type' => $catalog->validationRules((int) $customer->company_id),
            ], app(\App\Support\Production\PrintSpecificationJobFields::class)->validationRules()));
        } catch (ValidationException $exception) {
            return $this->lookupValidationResponse(
                $request,
                $exception,
                'admin.lookups.quick-create.print-specification',
                $this->printSpecificationQuickCreateFormData($customer, $printSpecification),
            );
        }

        abort_unless((int) $validated['customer_id'] === (int) $customer->id, 422);

        if (! empty($validated['inventory_item_id'])) {
            InventoryItem::query()->forTenant()->whereKey($validated['inventory_item_id'])->firstOrFail();
        }

        $spec = $specifications->update($printSpecification, $validated, (int) auth()->id());

        if ($request->hasFile('artwork_file')) {
            $artworks->uploadVersionForSpecification(
                $spec,
                $request->file('artwork_file'),
                (int) auth()->id(),
                null,
                $validated['artwork_type'] ?? app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->defaultCode(),
            );
            $spec = $spec->fresh(['inventoryItem', 'activeArtworkVersion']);
        }

        $label = trim($spec->specification_code.' · '.$spec->name);

        return $this->quickCreateResponse($spec->id, $label, __('Print specification updated.'));
    }

    public function storePrintSpecification(
        Request $request,
        CustomerPrintSpecificationService $specifications,
        CustomerArtworkService $artworks,
    ): JsonResponse|Response {
        try {
            $customer = Customer::query()->forTenant()->findOrFail($request->integer('customer_id'));
            $catalog = app(\App\Support\Crm\CustomerArtworkTypeCatalog::class);

            $validated = $request->validate(array_merge([
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                'product_name' => ['required', 'string', 'max:255'],
                'inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', Rule::enum(CustomerPrintSpecificationStatus::class)],
                'default_quantity' => ['nullable', 'numeric', 'min:0'],
                'default_unit_price' => ['nullable', 'numeric', 'min:0'],
                'artwork_file' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
                'artwork_type' => $catalog->validationRules((int) $customer->company_id),
            ], app(\App\Support\Production\PrintSpecificationJobFields::class)->validationRules()));
        } catch (ValidationException $exception) {
            $customer = Customer::query()->forTenant()->find($request->integer('customer_id'));

            return $this->lookupValidationResponse(
                $request,
                $exception,
                'admin.lookups.quick-create.print-specification',
                $this->printSpecificationQuickCreateFormData($customer),
            );
        }

        $customer = Customer::query()->forTenant()->findOrFail($validated['customer_id']);
        $this->authorize('update', $customer);

        if (! empty($validated['inventory_item_id'])) {
            InventoryItem::query()->forTenant()->whereKey($validated['inventory_item_id'])->firstOrFail();
        }

        $spec = $specifications->create($customer, $validated, (int) auth()->id());

        if ($request->hasFile('artwork_file')) {
            $artworks->uploadVersionForSpecification(
                $spec,
                $request->file('artwork_file'),
                (int) auth()->id(),
                null,
                $validated['artwork_type'] ?? app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->defaultCode(),
            );
            $spec = $spec->fresh(['inventoryItem', 'activeArtworkVersion']);
        }

        $label = trim($spec->specification_code.' · '.$spec->name);

        return $this->quickCreateResponse($spec->id, $label, __('Print specification created.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function printSpecificationQuickCreateFormData(
        ?Customer $customer,
        ?CustomerPrintSpecification $specification = null,
    ): array {
        $destination = old(
            'production_destination',
            $specification?->production_destination?->value ?? request('production_destination'),
        );

        return [
            'title' => $specification ? __('Edit print specification') : __('Create print specification'),
            'action' => $specification
                ? route('admin.crm.print-specifications.quick-update', $specification)
                : route('admin.crm.print-specifications.quick-store'),
            'customer' => $customer,
            'specification' => $specification,
            'statuses' => CustomerPrintSpecificationStatus::cases(),
            'artworkTypes' => $customer
                ? app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->optionsForCompany((int) $customer->company_id)
                : [],
            'defaultStatus' => $specification?->status?->value
                ?? CustomerPrintSpecificationStatus::Active->value,
            'preselectedDestination' => $destination,
        ];
    }
}
