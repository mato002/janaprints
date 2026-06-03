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

    public function index(): View
    {
        $this->authorize('viewAny', StockIssue::class);

        $issues = $this->scopeToTenant(
            StockIssue::query()->with(['warehouse', 'issuer'])->latest('issue_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.issues.index', compact('issues'));
    }

    public function create(): View
    {
        $this->authorize('create', StockIssue::class);

        return view('admin.inventory.issues.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockIssue::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $header = $this->validateHeader($request, $companyId, $branchId);
        $this->assertCanUseDestination(StockIssueDestination::from($header['destination']));
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
        return $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'to_warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'destination' => ['required', Rule::enum(StockIssueDestination::class)],
            'issue_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request, int $companyId, int $branchId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        return $validated['items'];
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
        return [
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'destinations' => StockIssueDestination::cases(),
        ];
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
