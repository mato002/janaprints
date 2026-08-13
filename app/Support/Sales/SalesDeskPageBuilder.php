<?php

namespace App\Support\Sales;

use App\Enums\ProductionPriority;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Commercial\CommercialApprovalQueueService;
use App\Support\Crm\CustomerPrintSpecificationService;
use App\Support\Lookup\LookupOptionService;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use Illuminate\Http\Request;

class SalesDeskPageBuilder
{
    use ScopesToTenant;

    public function __construct(
        protected SalesDeskService $desk,
        protected CustomerPrintSpecificationService $printSpecifications,
        protected SalesDeskWorkQueueService $workQueue,
        protected LookupOptionService $lookups,
        protected SalesDeskWalkInPanelPresenter $walkInPanel,
        protected CommercialApprovalQueueService $approvalQueue,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();
        $activeView = SalesDeskViews::normalize($request->query('view'));

        if (SalesDeskViews::isPanelView($activeView)) {
            return array_merge(
                $this->basePayload($request, $user, $activeView),
                $this->panelPayload($activeView, $request),
            );
        }

        return $this->walkInPayload($request, $user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function basePayload(Request $request, $user, string $activeView): array
    {
        return [
            'operatorMode' => SalesOperatorMode::enabledFor($user),
            'activeSalesView' => $activeView,
            'searchUrl' => route('admin.sales.desk.customers.search'),
            'fullCommercialDeskUrl' => route('admin.workspaces.commercial.section', [
                'section' => 'sales',
                'tab' => 'sales-desk',
            ]),
            'printSpecifications' => [],
            'orderPresentation' => null,
            'step' => 1,
            'customer' => null,
            'specification' => null,
            'order' => null,
            'fastActions' => [],
            'workQueue' => [],
            'walkInPanel' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function walkInPayload(Request $request, $user): array
    {
        $step = max(1, min(5, (int) $request->query('step', 1)));
        $customer = $this->resolveCustomer($request);
        $specification = $this->resolveSpecification($request, $customer);
        $order = $this->resolveOrder($request, $customer);
        $orderPresentation = $order ? $this->desk->presentOrder($order) : null;

        if ($order) {
            $step = ! empty($orderPresentation['released_to_queue']) ? 5 : 4;
        } elseif ($specification && $customer) {
            $step = max($step, 3);
        } elseif ($customer) {
            $step = max($step, 2);
        }

        $specs = $customer
            ? $this->printSpecifications->selectableForOrderContext($customer)
            : [];

        $notice = $this->specificationNotice($request, $customer, $specification);
        $deskUrls = $this->desk->deskUrls($customer, $specification);
        $customerContext = $this->desk->presentCustomerContext($customer, $specification);

        // Product options for inline spec create (same source as lookup refresh — no extra round-trip).
        $inventoryItemOptions = $step === 2 && $customer
            ? $this->lookups->options('items', $request)
            : [];

        return [
            'operatorMode' => SalesOperatorMode::enabledFor($user),
            'activeSalesView' => SalesDeskViews::DESK,
            'step' => $step,
            'customer' => $customer,
            'specification' => $specification,
            'order' => $order,
            'orderPresentation' => $orderPresentation,
            'customerContext' => $customerContext,
            'walkInPanel' => $this->walkInPanel->present(
                $step,
                $customerContext,
                $specification,
                $specs,
                $order,
                $orderPresentation,
                $deskUrls,
            ),
            'workQueue' => $this->workQueue->present($request),
            'fastActions' => $this->desk->fastActions($customer, $specification, $order),
            'deskUrls' => $deskUrls,
            'printSpecifications' => $specs,
            'specificationNotice' => $notice['message'],
            'searchUrl' => route('admin.sales.desk.customers.search'),
            'orderPriorities' => ProductionPriority::cases(),
            'canSendToProduction' => $user?->can('sales_orders.production') ?? false,
            'inventoryItemOptions' => $inventoryItemOptions,
            'fullCommercialDeskUrl' => route('admin.workspaces.commercial.section', [
                'section' => 'sales',
                'tab' => 'sales-desk',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function panelPayload(string $view, Request $request): array
    {
        return match ($view) {
            SalesDeskViews::QUOTES => [
                'registerTitle' => __('Quotations'),
                'quotations' => $this->scopeToTenant(
                    Quotation::query()->with(['customer', 'branch', 'preparer'])
                )->latest('quotation_date')->paginate(15)->withQueryString(),
            ],
            SalesDeskViews::ORDERS => [
                'registerTitle' => __('Sales orders'),
                'orders' => $this->scopeToTenant(
                    SalesOrder::query()->with(['customer', 'branch', 'quotation', 'creator', 'jobCard', 'invoices'])
                )->latest('order_date')->paginate(15)->withQueryString(),
            ],
            SalesDeskViews::ARTWORK => [
                'registerTitle' => __('Artwork requests'),
                'requests' => $this->scopeToTenant(
                    ArtworkRequest::query()->with(['customer', 'branch', 'requester', 'assignedDesigner'])
                )->latest()->paginate(15)->withQueryString(),
            ],
            SalesDeskViews::APPROVALS => $this->approvalsPayload($request),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function approvalsPayload(Request $request): array
    {
        abort_unless($request->user()?->can('commercial.approvals.view'), 403);

        $companyId = tenant()->companyId() ?? $request->user()?->company_id;
        $branchId = tenant()->branchId() ?? $request->user()?->default_branch_id;

        $workspace = $this->approvalQueue->workspace($companyId, $branchId, [
            'tab' => $request->query('tab'),
            'type' => $request->query('type'),
            'q' => $request->query('q'),
            'branch_id' => $request->query('branch_id'),
            'requested_by' => $request->query('requested_by'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        return [
            'registerTitle' => __('Sales approvals'),
            'registerDescription' => __('Needs attention now — then searchable approval history.'),
            'approvals' => $workspace,
            'canAction' => $request->user()->can('commercial.approvals.action'),
            'canApproveQuotations' => $request->user()->can('quotations.approve'),
            'canRejectQuotations' => $request->user()->can('quotations.edit'),
            'canConfirmOrders' => $request->user()->can('sales_orders.confirm'),
            'canApproveArtwork' => $request->user()->can('artwork.approve'),
        ];
    }

    protected function resolveCustomer(Request $request): ?Customer
    {
        $key = $request->query('customer');
        if ($key === null || $key === '') {
            return null;
        }

        // MySQL coerces '9Yy…' → 9 when compared to integer id. Never whereKey() a public hash.
        $query = Customer::query()->forTenant();

        if (ctype_digit((string) $key)) {
            $customer = $query->where('id', (int) $key)->first();
        } else {
            $customer = $query->where('public_id', (string) $key)->first();
        }

        if ($customer && $request->user()?->can('view', $customer)) {
            return $customer;
        }

        return null;
    }

    protected function resolveSpecification(Request $request, ?Customer $customer): ?CustomerPrintSpecification
    {
        if (! $customer) {
            return null;
        }

        $id = $request->integer('specification');
        if (! $id) {
            return null;
        }

        // Resolve by explicit id tied to this customer (avoid branch-scoped misses / cross-customer leftovers in the URL).
        return CustomerPrintSpecification::query()
            ->where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)
            ->where('id', $id)
            ->with(['inventoryItem:id,item_name,sku,stock_role', 'activeArtworkVersion'])
            ->first();
    }

    /**
     * @return array{mismatch: bool, message: ?string}
     */
    protected function specificationNotice(Request $request, ?Customer $customer, ?CustomerPrintSpecification $specification): array
    {
        $id = $request->integer('specification');
        if (! $id || ! $customer || $specification) {
            return ['mismatch' => false, 'message' => null];
        }

        $foreign = CustomerPrintSpecification::query()
            ->where('company_id', $customer->company_id)
            ->where('id', $id)
            ->first();

        if ($foreign && (int) $foreign->customer_id !== (int) $customer->id) {
            return [
                'mismatch' => true,
                'message' => __('That print specification belongs to another customer. Create or select one for :customer.', [
                    'customer' => $customer->name,
                ]),
            ];
        }

        return [
            'mismatch' => true,
            'message' => __('Print specification not found for this customer. Create one or select an active specification.'),
        ];
    }

    protected function resolveOrder(Request $request, ?Customer $customer): ?SalesOrder
    {
        $key = $request->query('order');
        if ($key === null || $key === '') {
            return null;
        }

        $query = SalesOrder::query()->forTenant();

        if (ctype_digit((string) $key)) {
            $order = $query->where('id', (int) $key)->first();
        } else {
            $order = $query->where('public_id', (string) $key)->first();
        }

        if (! $order || ! $request->user()?->can('view', $order)) {
            return null;
        }

        if ($customer && (int) $order->customer_id !== (int) $customer->id) {
            return null;
        }

        return $order->loadMissing(['jobCard', 'customer']);
    }
}
