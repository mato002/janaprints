<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
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
    use ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
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
            ...$this->formMeta(),
            'selectedWarehouseId' => $selectedWarehouseId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockIssue::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $header = $this->validateHeader($request, $companyId, $branchId);
        $this->assertCanUseDestination(StockIssueDestination::from($header['destination']));
        if (($header['destination'] ?? null) === StockIssueDestination::Transfer->value && (int) $header['warehouse_id'] === (int) ($header['to_warehouse_id'] ?? 0)) {
            throw ValidationException::withMessages([
                'to_warehouse_id' => __('Transfer destination must be different from the source warehouse.'),
            ]);
        }
        $lines = $this->validateLines($request, $companyId, $branchId);

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

        return redirect()->route('admin.inventory.issues.show', $issue)->with('status', __('Issue created.'));
    }

    public function show(StockIssue $issue): View
    {
        $this->authorize('view', $issue);

        $issue->load(['warehouse', 'toWarehouse', 'issuer', 'items.inventoryItem']);

        return view('admin.inventory.issues.show', compact('issue'));
    }

    public function post(StockIssue $issue): RedirectResponse
    {
        $this->authorize('post', $issue);

        try {
            StockIssueService::post($issue, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Issue posted to inventory.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId, int $branchId): array
    {
        $rules = $this->formSettings->mergeValidationRules('stock_issue.create', [
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'to_warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'destination' => [Rule::enum(StockIssueDestination::class)],
            'issue_date' => ['date'],
            'notes' => ['string'],
        ], $companyId, $branchId);

        $rules['warehouse_id'] = ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)];
        $rules['destination'] = ['required', Rule::enum(StockIssueDestination::class)];
        $rules['issue_date'] = ['required', 'date'];
        $rules['to_warehouse_id'] = ['nullable', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)];
        $rules['notes'] = ['nullable', 'string'];

        return $request->validate($rules);
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
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'destinations' => StockIssueDestination::cases(),
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
