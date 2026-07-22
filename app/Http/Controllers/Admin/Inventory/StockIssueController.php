<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\ProductionConsumptionGovernance;
use App\Support\Inventory\ReturnsToStoreDesk;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Support\StockIssueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockIssueController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ReturnsToStoreDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected ProductionConsumptionGovernance $productionGovernance,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', StockIssue::class);

        $issues = $this->scopeToTenant(
            StockIssue::query()->with(['warehouse', 'issuer'])->latest('issue_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.issues.index', compact('issues'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StockIssue::class);

        $selectedWarehouseId = $request->integer('warehouse_id') ?: null;

        if ($selectedWarehouseId) {
            abort_unless(
                Warehouse::query()->forTenant()->where('is_active', true)->whereKey($selectedWarehouseId)->exists(),
                404,
            );
        }

        return view('admin.inventory.issues.create', [
            ...$this->formMeta($selectedWarehouseId),
            'selectedWarehouseId' => $selectedWarehouseId,
            'fromStoreDesk' => $this->wantsStoreDeskReturn($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockIssue::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $header = $this->validateHeader($request, $companyId, $branchId);
        [$header, $customData] = $this->partitionCustomFields('stock_issue.create', $header, $companyId, $branchId);
        $destination = StockIssueDestination::from($header['destination']);
        $this->productionGovernance->assertCanUseDestination(
            $request->user(),
            $destination,
            (int) $header['warehouse_id'],
            $request->input('production_override_reason'),
        );

        $allowedDestinations = array_map(
            fn (StockIssueDestination $allowed) => $allowed->value,
            $this->productionGovernance->allowedDestinationsFor($request->user(), (int) $header['warehouse_id']),
        );

        if (! in_array($destination->value, $allowedDestinations, true)) {
            throw ValidationException::withMessages([
                'destination' => $this->productionGovernance->blockedMessage(),
            ]);
        }

        if (($header['destination'] ?? null) === StockIssueDestination::Transfer->value && (int) $header['warehouse_id'] === (int) ($header['to_warehouse_id'] ?? 0)) {
            throw ValidationException::withMessages([
                'to_warehouse_id' => __('Transfer destination must be different from the source warehouse.'),
            ]);
        }
        $lines = $this->validateLines($request, $companyId, $branchId);
        $shouldPost = $request->input('intent') === 'post';

        $issue = StockIssue::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'issue_number' => $this->nextNumber($companyId, $branchId),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $issue->items()->create($line);
        }

        $this->syncCustomFields($issue, 'stock_issue.create', $customData, $companyId);

        if ($destination === StockIssueDestination::Production && filled($request->input('production_override_reason'))) {
            $this->productionGovernance->applyOverrideMetadata(
                $issue,
                $request->user(),
                (string) $request->input('production_override_reason'),
            );
        }

        $message = __('Issue saved as draft. Post it to update stock.');

        if ($shouldPost) {
            $this->authorize('post', $issue);

            try {
                StockIssueService::post($issue->fresh(['items', 'warehouse', 'toWarehouse']), (int) auth()->id());
                $message = __('Materials issued and posted to stock.');
            } catch (ValidationException $e) {
                return $this->issueReturnRedirect($request, $issue)
                    ->with('status', __('Issue saved as draft, but could not be posted.'))
                    ->withErrors($e->errors());
            }
        }

        return $this->issueReturnRedirect($request, $issue)->with('status', $message);
    }

    public function show(Request $request, StockIssue $issue): View
    {
        $this->authorize('view', $issue);

        $issue->load(['warehouse', 'toWarehouse', 'issuer', 'items.inventoryItem']);

        if ($this->wantsStoreDeskReturn($request)) {
            return view('admin.store.desk.issue-modal', compact('issue'));
        }

        return view('admin.inventory.issues.show', [
            'issue' => $issue,
            'fromStoreDesk' => false,
        ]);
    }

    public function post(Request $request, StockIssue $issue): RedirectResponse
    {
        $this->authorize('post', $issue);

        try {
            StockIssueService::post($issue, (int) auth()->id());
        } catch (ValidationException $e) {
            if ($this->wantsStoreDeskReturn($request)) {
                return redirect()->to($this->storeDeskUrl())->withErrors($e->validator);
            }

            return back()->withErrors($e->validator);
        }

        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl())->with('status', __('Issue posted to inventory.'));
        }

        return back()->with('status', __('Issue posted to inventory.'));
    }

    protected function issueReturnRedirect(Request $request, StockIssue $issue): RedirectResponse
    {
        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl());
        }

        return redirect()->route('admin.inventory.issues.show', $issue);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId, int $branchId): array
    {
        return $this->formSettings->validateRequest($request, 'stock_issue.create', [
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'to_warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'destination' => [Rule::enum(StockIssueDestination::class)],
            'issue_date' => ['date'],
            'notes' => ['string'],
        ], $companyId, $branchId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request, int $companyId, int $branchId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.inventory_item_id' => ['nullable', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lines = collect($validated['items'])
            ->filter(fn (array $line) => filled($line['inventory_item_id'] ?? null)
                || filled($line['quantity'] ?? null)
                || filled($line['unit_cost'] ?? null))
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => __('Stock issue must have at least one line.'),
            ]);
        }

        foreach ($lines as $index => $line) {
            if (! filled($line['inventory_item_id'] ?? null)) {
                throw ValidationException::withMessages([
                    "items.{$index}.inventory_item_id" => __('Item is required.'),
                ]);
            }

            if (! filled($line['quantity'] ?? null) || (float) $line['quantity'] <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => __('Quantity must be greater than zero.'),
                ]);
            }

            if (! filled($line['unit_cost'] ?? null)) {
                throw ValidationException::withMessages([
                    "items.{$index}.unit_cost" => __('Unit cost is required.'),
                ]);
            }
        }

        return $lines->all();
    }

    protected function nextNumber(int $companyId, int $branchId): string
    {
        return app(NumberingService::class)->next(
            DocumentType::StockIssue,
            $companyId,
            $branchId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?int $warehouseId = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $user = auth()->user();
        $destinations = $this->productionGovernance->allowedDestinationsFor($user, $warehouseId);
        $productionAllowed = in_array(StockIssueDestination::Production, $destinations, true);

        return [
            'warehouses' => Warehouse::query()
                ->forTenant()
                ->where('is_active', true)
                ->where('is_virtual', false)
                ->orderBy('name')
                ->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'destinations' => $destinations,
            'productionGovernance' => [
                'heading' => __('Production consumption governance'),
                'message' => $this->productionGovernance->blockedMessage(),
                'guidance' => $this->productionGovernance->redirectGuidance(),
                'production_destination_allowed' => $productionAllowed,
            ],
            'formFields' => $this->requiredSafeFields($this->formSettings->resolvedFields('stock_issue.create', $companyId, $branchId)),
        ];
    }

    protected function requiredSafeFields(array $fields): array
    {
        foreach ([
            'warehouse_id' => __('Source Warehouse'),
            'destination' => __('Reason / Destination'),
            'issue_date' => __('Issue Date'),
        ] as $field => $label) {
            $fields[$field] = [
                ...($fields[$field] ?? []),
                'label' => $fields[$field]['label'] ?? $label,
                'required' => true,
                'visible' => true,
                'hidden' => false,
                'read_only' => false,
            ];
        }

        foreach ([
            'to_warehouse_id' => __('Destination Warehouse'),
            'notes' => __('Notes'),
            'inventory_item_id' => __('Item'),
            'quantity' => __('Quantity'),
            'unit_cost' => __('Unit Cost'),
        ] as $field => $label) {
            $fields[$field] = [
                ...($fields[$field] ?? []),
                'label' => $fields[$field]['label'] ?? $label,
                'required' => false,
                'visible' => true,
                'hidden' => false,
                'read_only' => false,
            ];
        }

        return $fields;
    }

    protected function assertCanUseDestination(StockIssueDestination $destination): void
    {
        $user = auth()->user();

        if ($destination === StockIssueDestination::Transfer) {
            abort_unless($user?->can('inventory.transfer'), 403);

            return;
        }

        abort_unless($user?->can('inventory.issue'), 403);
    }
}
