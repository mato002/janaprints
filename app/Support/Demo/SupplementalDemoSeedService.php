<?php

namespace App\Support\Demo;

use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\InventoryDocumentStatus;
use App\Enums\PerformanceRating;
use App\Enums\PerformanceReviewCycle;
use App\Enums\PerformanceReviewStatus;
use App\Enums\DepreciationRunStatus;
use App\Enums\PosSaleStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Enums\PublicQuoteRequestPriority;
use App\Enums\PublicQuoteRequestStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\RecruitmentPipelineStage;
use App\Enums\SalesOrderStatus;
use App\Enums\StockIssueDestination;
use App\Enums\SupplierPaymentMethod;
use App\Enums\SupplierBillStatus;
use App\Enums\TrainingAssignmentStatus;
use App\Enums\TrainingType;
use App\Enums\VacancyStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\Candidate;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\JobApplication;
use App\Models\Hr\PayrollRun;
use App\Models\Hr\PerformanceReview;
use App\Models\Hr\TrainingProgram;
use App\Models\Hr\Vacancy;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockIssueItem;
use App\Models\Inventory\Warehouse;
use App\Models\JobTitle;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosSale;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\CustomerPaymentAllocation;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Tax\TaxPeriod;
use App\Models\User;
use App\Services\Assets\DepreciationRunService;
use App\Support\Accounting\JournalPostingService;
use App\Support\Accounting\PosAccountingPostingService;
use App\Support\Hr\PayrollRunService;
use App\Support\Procurement\SupplierBillService;
use App\Support\Procurement\SupplierPaymentService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\StockIssueService;
use App\Support\Tax\TaxReturnService;
use Carbon\Carbon;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SupplementalDemoSeedService
{
    /** @var list<array{title: string, qty: int, price: float}> */
    private array $printProducts = [
        ['title' => 'Business Cards — 500 pcs', 'qty' => 500, 'price' => 18],
        ['title' => 'A5 Flyers — 2000 pcs', 'qty' => 2000, 'price' => 12],
        ['title' => 'Brochure 8pp — 1000 pcs', 'qty' => 1000, 'price' => 85],
        ['title' => 'Roll-up Banner — 3 pcs', 'qty' => 3, 'price' => 4500],
        ['title' => 'Branded T-Shirts — 100 pcs', 'qty' => 100, 'price' => 650],
        ['title' => 'Vehicle Branding — partial wrap', 'qty' => 1, 'price' => 45000],
    ];

    public function __construct(
        protected CustomerInvoiceService $customerInvoices,
        protected CustomerPaymentService $customerPayments,
        protected SupplierBillService $supplierBills,
        protected SupplierPaymentService $supplierPayments,
        protected PosAccountingPostingService $posPosting,
        protected PayrollRunService $payrollRuns,
        protected DepreciationRunService $depreciationRuns,
        protected JournalPostingService $journalPosting,
        protected TaxReturnService $taxReturns,
    ) {}

    /**
     * @param  list<string>  $sections  accounting|sales|hr|supply_chain|assets|commercial
     */
    public function run(?Command $command = null, array $sections = []): void
    {
        $ctx = $this->resolveContext();

        if ($ctx === null) {
            $command?->warn('Supplemental demo skipped: company JANA / branch HQ not found.');

            return;
        }

        $all = $sections === [];
        $run = static fn (string $section): bool => $all || in_array($section, $sections, true);

        Model::withoutEvents(function () use ($ctx, $command, $run) {
            if ($run('sales')) {
                $this->seedExtendedSalesPipeline($ctx, $command);
            }

            if ($run('accounting')) {
                $this->seedAccounting($ctx, $command);
            }

            if ($run('hr')) {
                $this->seedHr($ctx, $command);
            }

            if ($run('supply_chain')) {
                $this->seedSupplyChain($ctx, $command);
            }

            if ($run('assets')) {
                $this->seedAssetDepreciation($ctx, $command);
            }

            if ($run('commercial')) {
                $this->seedCommercial($ctx, $command);
            }

            $command?->info(sprintf(
                '  Totals: %d journals, %d supplier bills, %d payroll runs, %d sales orders, %d stock issues.',
                Journal::query()->where('company_id', $ctx->company->id)->count(),
                SupplierBill::query()->where('company_id', $ctx->company->id)->count(),
                PayrollRun::query()->where('company_id', $ctx->company->id)->count(),
                SalesOrder::query()->where('company_id', $ctx->company->id)->count(),
                StockIssue::query()->where('company_id', $ctx->company->id)->count(),
            ));
        });
    }

    protected function resolveContext(): ?OperationalDemoContext
    {
        $company = Company::query()->where('code', 'JANA')->first();
        $branch = Branch::query()->where('company_id', $company?->id)->where('code', 'HQ')->first();
        $department = Department::query()->where('company_id', $company?->id)->where('code', 'ADMIN')->first();
        $admin = User::query()->where('email', 'admin@janaprints.local')->first()
            ?? User::query()->where('company_id', $company?->id)->first();

        if (! $company || ! $branch || ! $department || ! $admin) {
            return null;
        }

        $ctx = new OperationalDemoContext(
            company: $company,
            branch: $branch,
            department: $department,
            admin: $admin,
            salesUser: User::query()->where('email', 'sales@janaprints.local')->first(),
            mainWarehouse: Warehouse::query()->where('company_id', $company->id)->where('code', 'MAIN')->first(),
        );

        $ctx->customers = Customer::query()
            ->where('company_id', $company->id)
            ->orderBy('customer_code')
            ->get();

        app(OperationalDemoSeedService::class)->bootstrapSupplementCounters($ctx);

        return $ctx;
    }

    protected function seedExtendedSalesPipeline(OperationalDemoContext $ctx, ?Command $command): void
    {
        if (SalesOrder::query()->where('company_id', $ctx->company->id)->where('order_number', 'like', 'DEMO-SUP-SO-%')->exists()) {
            $command?->line('  Sales pipeline supplement already present — skipping new orders.');
        } else {
            $preparer = $ctx->salesUser ?? $ctx->admin;
            $customersWithOrders = SalesOrder::query()
                ->where('company_id', $ctx->company->id)
                ->pluck('customer_id');

            $customers = Customer::query()
                ->where('company_id', $ctx->company->id)
                ->whereNotIn('id', $customersWithOrders)
                ->orderBy('customer_code')
                ->limit(8)
                ->get();

            if ($customers->isEmpty()) {
                $command?->line('  No customers without sales orders — skipping new orders.');
            } else {
                DB::transaction(function () use ($ctx, $customers, $preparer) {
                    $designer = User::query()->where('email', 'designer@janaprints.local')->first();
                    $orderStatuses = [
                        SalesOrderStatus::Confirmed,
                        SalesOrderStatus::ReadyForProduction,
                        SalesOrderStatus::InProduction,
                        SalesOrderStatus::Completed,
                        SalesOrderStatus::Delivered,
                        SalesOrderStatus::Closed,
                    ];

                    foreach ($customers as $index => $customer) {
                        $quoteDate = $ctx->dateInPeriod(20 + ($index * 4))->toDateString();
                        $product = $this->printProducts[$index % count($this->printProducts)];
                        $qty = (float) $product['qty'];
                        $unitPrice = (float) $product['price'];
                        $subtotal = round($qty * $unitPrice, 2);
                        $tax = round($subtotal * 0.16, 2);
                        $total = $subtotal + $tax;

                        $quotation = Quotation::query()->create([
                            'company_id' => $ctx->company->id,
                            'branch_id' => $ctx->branch->id,
                            'customer_id' => $customer->id,
                            'quotation_number' => $ctx->nextNumber('DEMO-SUP-QT'),
                            'quotation_date' => $quoteDate,
                            'valid_until' => Carbon::parse($quoteDate)->addDays(14)->toDateString(),
                            'currency' => 'KES',
                            'subtotal' => $subtotal,
                            'tax_amount' => $tax,
                            'discount_amount' => 0,
                            'total_amount' => $total,
                            'status' => QuotationStatus::Accepted,
                            'revision_number' => 1,
                            'prepared_by' => $preparer->id,
                            'notes' => 'Supplemental demo quotation',
                        ]);

                        QuotationItem::query()->create([
                            'quotation_id' => $quotation->id,
                            'item_name' => $product['title'],
                            'description' => 'Supplemental print order',
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'line_total' => $subtotal,
                            'sort_order' => 1,
                        ]);

                        $orderDate = Carbon::parse($quoteDate)->addDays(2)->toDateString();
                        $status = $orderStatuses[$index % count($orderStatuses)];

                        $artwork = ArtworkRequest::query()->create([
                            'company_id' => $ctx->company->id,
                            'branch_id' => $ctx->branch->id,
                            'customer_id' => $customer->id,
                            'quotation_id' => $quotation->id,
                            'request_number' => $ctx->nextNumber('DEMO-SUP-AW'),
                            'title' => $product['title'].' artwork',
                            'description' => 'Supplemental demo artwork request',
                            'requested_by' => $preparer->id,
                            'assigned_designer_id' => $designer?->id,
                            'priority' => ArtworkPriority::Normal,
                            'status' => ArtworkRequestStatus::Approved,
                            'due_date' => Carbon::parse($quoteDate)->addDays(5)->toDateString(),
                            'current_version' => 1,
                        ]);

                        $order = SalesOrder::query()->create([
                            'company_id' => $ctx->company->id,
                            'branch_id' => $ctx->branch->id,
                            'customer_id' => $customer->id,
                            'quotation_id' => $quotation->id,
                            'artwork_request_id' => $artwork->id,
                            'order_number' => $ctx->nextNumber('DEMO-SUP-SO'),
                            'order_date' => $orderDate,
                            'required_date' => Carbon::parse($orderDate)->addDays(10)->toDateString(),
                            'status' => $status,
                            'subtotal' => $subtotal,
                            'tax_amount' => $tax,
                            'discount_amount' => 0,
                            'total_amount' => $total,
                            'notes' => 'Supplemental demo sales order',
                            'created_by' => $preparer->id,
                        ]);

                        SalesOrderItem::query()->create([
                            'sales_order_id' => $order->id,
                            'item_name' => $product['title'],
                            'description' => 'Supplemental order line',
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'line_total' => $subtotal,
                            'sort_order' => 1,
                        ]);
                    }
                });
            }
        }

        $this->seedJobCardsForOpenOrders($ctx);
        $this->seedInvoicesForCompletedOrders($ctx, $command);

        $command?->info('  Extended sales pipeline seeded.');
    }

    protected function seedJobCardsForOpenOrders(OperationalDemoContext $ctx): void
    {
        $creator = User::query()->where('email', 'production@janaprints.local')->first() ?? $ctx->admin;

        SalesOrder::query()
            ->where('company_id', $ctx->company->id)
            ->whereIn('status', [
                SalesOrderStatus::ReadyForProduction,
                SalesOrderStatus::InProduction,
                SalesOrderStatus::Completed,
                SalesOrderStatus::Delivered,
                SalesOrderStatus::Closed,
            ])
            ->whereDoesntHave('jobCard')
            ->each(function (SalesOrder $order, int $index) use ($ctx, $creator) {
                $start = Carbon::parse($order->order_date)->addDays(2);

                ProductionJobCard::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'sales_order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'quotation_id' => $order->quotation_id,
                    'artwork_request_id' => $order->artwork_request_id,
                    'job_card_number' => $ctx->nextNumber('DEMO-SUP-JC'),
                    'production_type' => ProductionType::Digital,
                    'priority' => ProductionPriority::Normal,
                    'planned_start_date' => $start->toDateString(),
                    'planned_end_date' => $start->copy()->addDays(5)->toDateString(),
                    'actual_start_date' => $start,
                    'actual_end_date' => $index % 2 === 0 ? $start->copy()->addDays(4) : null,
                    'status' => ProductionJobCardStatus::InProduction,
                    'created_by' => $creator->id,
                ]);
            });
    }

    protected function seedInvoicesForCompletedOrders(OperationalDemoContext $ctx, ?Command $command): void
    {
        $poster = User::query()->where('email', 'accountant@janaprints.local')->first() ?? $ctx->admin;

        SalesOrder::query()
            ->with(['items', 'jobCard'])
            ->where('company_id', $ctx->company->id)
            ->whereIn('status', [
                SalesOrderStatus::Completed,
                SalesOrderStatus::Delivered,
                SalesOrderStatus::Closed,
            ])
            ->whereDoesntHave('invoices')
            ->each(function (SalesOrder $order, int $index) use ($ctx, $poster, $command) {
                $invoiceDate = Carbon::parse($order->order_date)->addDays(7)->toDateString();
                $total = (float) $order->total_amount;

                $invoice = CustomerInvoice::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'customer_id' => $order->customer_id,
                    'sales_order_id' => $order->id,
                    'production_job_card_id' => $order->jobCard?->id,
                    'invoice_number' => $ctx->nextNumber('DEMO-SUP-INV'),
                    'invoice_type' => CustomerInvoiceType::Standard,
                    'invoice_date' => $invoiceDate,
                    'due_date' => Carbon::parse($invoiceDate)->addDays(30)->toDateString(),
                    'currency' => 'KES',
                    'status' => CustomerInvoiceStatus::Approved,
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'discount_amount' => 0,
                    'total_amount' => $total,
                    'amount_paid' => 0,
                    'balance_due' => $total,
                    'billing_percent' => 100,
                    'approved_by' => $poster->id,
                    'approved_at' => Carbon::parse($invoiceDate)->addHours(1),
                    'created_by' => $poster->id,
                ]);

                foreach ($order->items as $line) {
                    $invoice->lines()->create([
                        'sales_order_item_id' => $line->id,
                        'item_name' => $line->item_name,
                        'description' => $line->description,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->unit_price,
                        'line_subtotal' => $line->line_total,
                        'line_total' => $line->line_total,
                        'sort_order' => $line->sort_order,
                    ]);
                }

                $payment = null;
                $paid = $index % 2 === 0 ? $total : round($total / 2, 2);

                if ($paid > 0) {
                    $payment = CustomerPayment::query()->create([
                        'company_id' => $ctx->company->id,
                        'branch_id' => $ctx->branch->id,
                        'customer_id' => $order->customer_id,
                        'payment_number' => $ctx->nextNumber('DEMO-SUP-RCP'),
                        'payment_date' => Carbon::parse($invoiceDate)->addDays(3)->toDateString(),
                        'payment_method' => CustomerPaymentMethod::Bank,
                        'reference' => 'SUP-BANK-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                        'currency' => 'KES',
                        'amount' => $paid,
                        'allocated_amount' => $paid,
                        'unallocated_amount' => 0,
                        'status' => CustomerPaymentStatus::Draft,
                        'notes' => 'Supplemental demo payment',
                        'created_by' => $poster->id,
                    ]);

                    CustomerPaymentAllocation::query()->create([
                        'customer_payment_id' => $payment->id,
                        'customer_invoice_id' => $invoice->id,
                        'amount' => $paid,
                    ]);
                }

                try {
                    $this->customerInvoices->post($invoice->fresh(['lines']), $poster->id);

                    if ($payment !== null) {
                        $this->customerPayments->post($payment->fresh(['allocations.invoice']), $poster->id);
                    }
                } catch (Throwable $e) {
                    $command?->warn('  Invoice post skipped: '.$invoice->invoice_number.' — '.$e->getMessage());
                }
            });
    }

    protected function seedAccounting(OperationalDemoContext $ctx, ?Command $command): void
    {
        $poster = User::query()->where('email', 'accountant@janaprints.local')->first() ?? $ctx->admin;

        $this->ensurePostingFoundation($command);
        $this->postExistingCustomerDocuments($ctx, $poster, $command);
        $this->postExistingPosSales($ctx, $poster, $command);
        $this->seedSupplierPayables($ctx, $poster, $command);
        $this->seedEmployeeCompensation($ctx);
        $this->seedPayrollRuns($ctx, $poster, $command);
        $this->seedManualJournals($ctx, $poster, $command);
        $this->seedTaxReturns($ctx, $poster, $command);

        $command?->info('  Accounting / GL supplement complete.');
    }

    protected function ensurePostingFoundation(?Command $command): void
    {
        Artisan::call('db:seed', ['--class' => JanaPrintsPostingEngineSeeder::class, '--no-interaction' => true]);
        $command?->line(trim(Artisan::output()));
    }

    protected function postExistingCustomerDocuments(OperationalDemoContext $ctx, User $poster, ?Command $command): void
    {
        CustomerInvoice::query()
            ->where('company_id', $ctx->company->id)
            ->whereNull('posted_journal_id')
            ->where('status', CustomerInvoiceStatus::Posted)
            ->each(function (CustomerInvoice $invoice) use ($poster, $command) {
                $invoice->update([
                    'status' => CustomerInvoiceStatus::Approved,
                    'posted_by' => null,
                    'posted_at' => null,
                ]);

                try {
                    $this->customerInvoices->post($invoice->fresh(['lines']), $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Invoice GL post failed: '.$invoice->invoice_number.' — '.$e->getMessage());
                }
            });

        CustomerInvoice::query()
            ->where('company_id', $ctx->company->id)
            ->whereNull('posted_journal_id')
            ->where('status', CustomerInvoiceStatus::Approved)
            ->each(function (CustomerInvoice $invoice) use ($poster, $command) {
                try {
                    $this->customerInvoices->post($invoice->fresh(['lines']), $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Approved invoice post failed: '.$invoice->invoice_number.' — '.$e->getMessage());
                }
            });

        CustomerPayment::query()
            ->where('company_id', $ctx->company->id)
            ->whereNull('posted_journal_id')
            ->each(function (CustomerPayment $payment) use ($poster, $command) {
                if ($payment->status === CustomerPaymentStatus::Posted) {
                    $payment->update([
                        'status' => CustomerPaymentStatus::Draft,
                        'posted_by' => null,
                        'posted_at' => null,
                    ]);
                }

                try {
                    $this->customerPayments->post($payment->fresh(['allocations.invoice']), $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Payment GL post failed: '.$payment->payment_number.' — '.$e->getMessage());
                }
            });
    }

    protected function postExistingPosSales(OperationalDemoContext $ctx, User $poster, ?Command $command): void
    {
        PosSale::query()
            ->where('company_id', $ctx->company->id)
            ->where('status', PosSaleStatus::Paid)
            ->with('payments')
            ->each(function (PosSale $sale) use ($poster, $command) {
                try {
                    $this->posPosting->postPaidSale($sale, $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  POS GL post failed: '.$sale->sale_number.' — '.$e->getMessage());
                }
            });

        $posted = PosPayment::query()
            ->whereHas('sale', fn ($q) => $q->where('company_id', $ctx->company->id))
            ->whereNotNull('posted_journal_id')
            ->count();

        $command?->line("  POS payments posted to GL: {$posted}");
    }

    protected function seedSupplierPayables(OperationalDemoContext $ctx, User $poster, ?Command $command): void
    {
        PurchaseOrder::query()
            ->with(['items', 'vendor'])
            ->where('company_id', $ctx->company->id)
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::PartiallyReceived,
                PurchaseOrderStatus::Received,
            ])
            ->each(function (PurchaseOrder $order, int $index) use ($poster, $command) {
                if (SupplierBill::query()->where('purchase_order_id', $order->id)->exists()) {
                    return;
                }

                try {
                    $bill = $this->supplierBills->createFromPurchaseOrder($order, $poster->id, [
                        'bill_date' => Carbon::parse($order->order_date)->addDays(10)->toDateString(),
                        'default_tax_rate' => 16,
                    ]);
                    $bill = $this->supplierBills->approve($bill, $poster->id);
                    $this->supplierBills->post($bill, $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Supplier bill skipped for PO '.$order->po_number.' — '.$e->getMessage());
                }
            });

        SupplierBill::query()
            ->where('company_id', $ctx->company->id)
            ->where('status', SupplierBillStatus::Approved)
            ->each(function (SupplierBill $bill) use ($poster, $command) {
                try {
                    $this->supplierBills->post($bill, $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Supplier bill post skipped for '.$bill->bill_number.' — '.$e->getMessage());
                }
            });

        SupplierBill::query()
            ->with('vendor')
            ->where('company_id', $ctx->company->id)
            ->where('status', SupplierBillStatus::Posted)
            ->where('balance_due', '>', 0)
            ->each(function (SupplierBill $bill, int $index) use ($poster, $command) {
                if ($index % 2 !== 0) {
                    return;
                }

                $payAmount = round((float) $bill->balance_due / 2, 2);

                if ($payAmount <= 0) {
                    return;
                }

                try {
                    $payment = $this->supplierPayments->create($bill->vendor, $poster->id, [
                        'branch_id' => $bill->branch_id,
                        'payment_date' => Carbon::parse($bill->bill_date)->addDays(5)->toDateString(),
                        'payment_method' => SupplierPaymentMethod::Bank,
                        'amount' => $payAmount,
                        'reference' => 'SUP-AP-'.str_pad((string) $bill->id, 4, '0', STR_PAD_LEFT),
                        'allocations' => [
                            ['supplier_bill_id' => $bill->id, 'amount' => $payAmount],
                        ],
                    ]);
                    $this->supplierPayments->post($payment, $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Supplier payment skipped for bill '.$bill->bill_number.' — '.$e->getMessage());
                }
            });
    }

    protected function seedEmployeeCompensation(OperationalDemoContext $ctx): void
    {
        $salaries = [85000, 72000, 68000, 55000, 48000, 42000, 95000, 60000, 38000];

        Employee::query()
            ->where('company_id', $ctx->company->id)
            ->where('is_active', true)
            ->whereDoesntHave('compensation')
            ->orderBy('employee_number')
            ->each(function (Employee $employee, int $index) use ($salaries) {
                $basic = $salaries[$index % count($salaries)];

                EmployeeCompensation::query()->create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $basic,
                    'house_allowance' => round($basic * 0.15, 2),
                    'transport_allowance' => 5000,
                    'medical_allowance' => 3000,
                    'effective_from' => now()->subMonths(6)->startOfMonth()->toDateString(),
                    'is_active' => true,
                ]);
            });
    }

    protected function seedPayrollRuns(OperationalDemoContext $ctx, User $poster, ?Command $command): void
    {
        if (PayrollRun::query()->where('company_id', $ctx->company->id)->where('reference', 'like', 'PR-DEMO-%')->exists()) {
            return;
        }

        for ($monthsAgo = 2; $monthsAgo >= 0; $monthsAgo--) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            try {
                $run = $this->payrollRuns->create($ctx->company->id, [
                    'branch_id' => $ctx->branch->id,
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'pay_date' => $end->copy()->addDays(5)->toDateString(),
                    'notes' => 'Supplemental demo payroll',
                ], $poster);
                $run->update(['reference' => 'PR-DEMO-'.$start->format('Ym')]);

                $run = $this->payrollRuns->calculate($run, $poster);

                if ($run->employee_count < 1) {
                    continue;
                }

                $run = $this->payrollRuns->approve($run, $poster);
                $this->payrollRuns->post($run, $poster);
            } catch (Throwable $e) {
                $command?->warn('  Payroll run skipped for '.$start->format('Y-m').' — '.$e->getMessage());
            }
        }
    }

    protected function seedManualJournals(OperationalDemoContext $ctx, User $poster, ?Command $command): void
    {
        if (Journal::query()->where('company_id', $ctx->company->id)->where('reference', 'like', 'DEMO-SUP-JNL-%')->exists()) {
            return;
        }

        $period = AccountingPeriod::query()
            ->where('company_id', $ctx->company->id)
            ->where('is_current', true)
            ->first()
            ?? AccountingPeriod::query()
                ->where('company_id', $ctx->company->id)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

        if (! $period) {
            $command?->warn('  No open accounting period — skipping manual journals.');

            return;
        }

        $bank = GlAccount::query()->where('company_id', $ctx->company->id)->where('code', '1210')->first();
        $rent = GlAccount::query()->where('company_id', $ctx->company->id)->where('code', '6200')->first()
            ?? GlAccount::query()->where('company_id', $ctx->company->id)->where('code', '6100')->first();
        $utilities = GlAccount::query()->where('company_id', $ctx->company->id)->where('code', '6300')->first()
            ?? GlAccount::query()->where('company_id', $ctx->company->id)->where('code', '6100')->first();

        if (! $bank || ! $rent || ! $utilities) {
            return;
        }

        $entries = [
            ['ref' => 'DEMO-SUP-JNL-001', 'desc' => 'Demo supplemental — office rent', 'debit' => $rent->id, 'amount' => 85000],
            ['ref' => 'DEMO-SUP-JNL-002', 'desc' => 'Demo supplemental — utilities', 'debit' => $utilities->id, 'amount' => 18500],
            ['ref' => 'DEMO-SUP-JNL-003', 'desc' => 'Demo supplemental — internet & telecom', 'debit' => $utilities->id, 'amount' => 9200],
        ];

        foreach ($entries as $entry) {
            try {
                $journal = $this->journalPosting->createDraft([
                    'accounting_period_id' => $period->id,
                    'branch_id' => $ctx->branch->id,
                    'journal_date' => $period->start_date->toDateString(),
                    'reference' => $entry['ref'],
                    'description' => $entry['desc'],
                ], [
                    ['gl_account_id' => $entry['debit'], 'debit' => $entry['amount'], 'credit' => 0],
                    ['gl_account_id' => $bank->id, 'debit' => 0, 'credit' => $entry['amount']],
                ], $poster->id);

                $this->journalPosting->post($journal, $poster->id);
            } catch (Throwable $e) {
                $command?->warn('  Manual journal skipped: '.$entry['ref'].' — '.$e->getMessage());
            }
        }
    }

    protected function seedTaxReturns(OperationalDemoContext $ctx, User $poster, ?Command $command): void
    {
        TaxPeriod::query()
            ->where('company_id', $ctx->company->id)
            ->orderByDesc('start_date')
            ->limit(3)
            ->each(function (TaxPeriod $period) use ($poster, $command) {
                try {
                    $this->taxReturns->buildDraft($period, $poster->id);
                } catch (Throwable $e) {
                    $command?->warn('  Tax return skipped for '.$period->code.' — '.$e->getMessage());
                }
            });
    }

    protected function seedHr(OperationalDemoContext $ctx, ?Command $command): void
    {
        $this->seedEmployeeCompensation($ctx);
        $this->seedRecruitment($ctx, $command);
        $this->seedPerformanceReviews($ctx, $command);
        $this->seedTraining($ctx, $command);

        $command?->info('  HR supplement complete.');
    }

    protected function seedRecruitment(OperationalDemoContext $ctx, ?Command $command): void
    {
        if (JobApplication::query()->where('company_id', $ctx->company->id)->where('reference', 'like', 'DEMO-SUP-APP-%')->exists()) {
            return;
        }

        $jobTitle = JobTitle::query()->where('company_id', $ctx->company->id)->first();
        $hrUser = User::query()->where('email', 'hr@janaprints.local')->first() ?? $ctx->admin;

        $vacancy = Vacancy::query()->firstOrCreate(
            ['company_id' => $ctx->company->id, 'reference' => 'DEMO-SUP-VAC-001'],
            [
                'branch_id' => $ctx->branch->id,
                'department_id' => $ctx->department->id,
                'job_title_id' => $jobTitle?->id,
                'title' => 'Graphic Designer',
                'description' => 'Produce print-ready artwork for corporate clients.',
                'positions' => 2,
                'filled_count' => 0,
                'status' => VacancyStatus::Open,
                'published_at' => now()->subWeeks(3),
                'closing_date' => now()->addWeeks(4)->toDateString(),
                'created_by_user_id' => $hrUser->id,
            ],
        );

        $candidates = [
            ['first' => 'Grace', 'last' => 'Mwangi', 'email' => 'grace.mwangi@candidate.demo', 'stage' => RecruitmentPipelineStage::Interview],
            ['first' => 'Ian', 'last' => 'Mutua', 'email' => 'ian.mutua@candidate.demo', 'stage' => RecruitmentPipelineStage::Screening],
            ['first' => 'Patricia', 'last' => 'Njoki', 'email' => 'patricia.njoki@candidate.demo', 'stage' => RecruitmentPipelineStage::Offer],
            ['first' => 'Victor', 'last' => 'Omondi', 'email' => 'victor.omondi@candidate.demo', 'stage' => RecruitmentPipelineStage::Applied],
        ];

        foreach ($candidates as $index => $row) {
            $candidate = Candidate::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'email' => $row['email']],
                [
                    'first_name' => $row['first'],
                    'last_name' => $row['last'],
                    'phone' => '07'.fake()->numerify('########'),
                    'resume_notes' => 'Demo candidate CV on file.',
                    'source' => 'website',
                ],
            );

            JobApplication::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'reference' => 'DEMO-SUP-APP-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'vacancy_id' => $vacancy->id,
                    'candidate_id' => $candidate->id,
                    'stage' => $row['stage'],
                    'applied_at' => now()->subDays(20 - ($index * 3)),
                    'notes' => 'Supplemental demo application',
                    'created_by_user_id' => $hrUser->id,
                ],
            );
        }
    }

    protected function seedPerformanceReviews(OperationalDemoContext $ctx, ?Command $command): void
    {
        if (PerformanceReview::query()->where('company_id', $ctx->company->id)->where('reference', 'like', 'DEMO-SUP-PR-%')->exists()) {
            return;
        }

        $reviewer = User::query()->where('email', 'hr@janaprints.local')->first() ?? $ctx->admin;
        $periodStart = now()->subMonths(3)->startOfMonth();
        $periodEnd = now()->subMonth()->endOfMonth();

        Employee::query()
            ->where('company_id', $ctx->company->id)
            ->where('is_active', true)
            ->orderBy('employee_number')
            ->limit(8)
            ->each(function (Employee $employee, int $index) use ($ctx, $reviewer, $periodStart, $periodEnd) {
                PerformanceReview::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $employee->branch_id ?? $ctx->branch->id,
                    'employee_id' => $employee->id,
                    'reference' => 'DEMO-SUP-PR-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'cycle' => PerformanceReviewCycle::Quarterly,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'rating' => [PerformanceRating::Excellent, PerformanceRating::Good, PerformanceRating::Average][$index % 3],
                    'status' => PerformanceReviewStatus::Submitted,
                    'production_output' => random_int(80, 120),
                    'attendance_percent' => random_int(88, 100),
                    'quality_percent' => random_int(85, 98),
                    'job_completion_percent' => random_int(75, 100),
                    'composite_score' => random_int(70, 95),
                    'strengths' => 'Reliable delivery and teamwork.',
                    'improvements' => 'Continue cross-training on digital presses.',
                    'manager_notes' => 'Supplemental demo performance review.',
                    'reviewed_by_user_id' => $reviewer->id,
                    'reviewed_at' => now()->subDays(5),
                ]);
            });
    }

    protected function seedTraining(OperationalDemoContext $ctx, ?Command $command): void
    {
        if (TrainingProgram::query()->where('company_id', $ctx->company->id)->where('title', 'Workplace Safety Induction')->exists()
            && EmployeeTrainingAssignment::query()->where('company_id', $ctx->company->id)->where('reference', 'like', 'DEMO-SUP-TRN-%')->exists()) {
            return;
        }

        $assigner = User::query()->where('email', 'hr@janaprints.local')->first() ?? $ctx->admin;

        $program = TrainingProgram::query()->firstOrCreate(
            ['company_id' => $ctx->company->id, 'title' => 'Workplace Safety Induction'],
            [
                'type' => TrainingType::Compliance,
                'description' => 'Mandatory safety training for production staff.',
                'duration_hours' => 4,
                'requires_certification' => true,
                'certificate_validity_days' => 365,
                'skill_tags' => ['safety', 'compliance'],
                'is_active' => true,
            ],
        );

        Employee::query()
            ->where('company_id', $ctx->company->id)
            ->where('is_active', true)
            ->orderBy('employee_number')
            ->limit(6)
            ->each(function (Employee $employee, int $index) use ($ctx, $program, $assigner) {
                EmployeeTrainingAssignment::query()->firstOrCreate(
                    [
                        'company_id' => $ctx->company->id,
                        'employee_id' => $employee->id,
                        'training_program_id' => $program->id,
                    ],
                    [
                        'reference' => 'DEMO-SUP-TRN-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                        'status' => $index % 3 === 0 ? TrainingAssignmentStatus::Assigned : TrainingAssignmentStatus::Completed,
                        'due_date' => now()->addDays(14)->toDateString(),
                        'hours_completed' => $index % 3 === 0 ? 0 : 4,
                        'certificate_reference' => $index % 3 === 0 ? null : 'CERT-'.strtoupper(Str::random(6)),
                        'certificate_expires_at' => $index % 3 === 0 ? null : now()->addMonths(10)->toDateString(),
                        'assigned_at' => now()->subWeeks(2),
                        'completed_at' => $index % 3 === 0 ? null : now()->subWeek(),
                        'assigned_by_user_id' => $assigner->id,
                        'completed_by_user_id' => $index % 3 === 0 ? null : $assigner->id,
                    ],
                );
            });
    }

    protected function seedSupplyChain(OperationalDemoContext $ctx, ?Command $command): void
    {
        if (StockIssue::query()->where('company_id', $ctx->company->id)->where('issue_number', 'like', 'DEMO-SUP-SI-%')->exists()) {
            $command?->line('  Stock issues already seeded.');

            return;
        }

        $warehouse = $ctx->mainWarehouse;
        $item = InventoryItem::query()->where('company_id', $ctx->company->id)->where('sku', 'RAW-ART350')->first();
        $issuer = User::query()->where('email', 'storekeeper@janaprints.local')->first() ?? $ctx->admin;

        if (! $warehouse || ! $item) {
            $command?->warn('  Warehouse or inventory item missing — skipping stock issues.');

            return;
        }

        $quantities = [120, 80, 200];

        foreach ($quantities as $index => $qty) {
            try {
                $issue = StockIssue::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'warehouse_id' => $warehouse->id,
                    'issue_number' => $ctx->nextNumber('DEMO-SUP-SI'),
                    'destination' => StockIssueDestination::Production,
                    'issue_date' => $ctx->dateInPeriod(30 + ($index * 8))->toDateString(),
                    'status' => InventoryDocumentStatus::Draft,
                    'notes' => 'Supplemental demo production consumption',
                    'issued_by' => $issuer->id,
                ]);

                StockIssueItem::query()->create([
                    'stock_issue_id' => $issue->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_cost' => 12.5,
                ]);

                StockIssueService::post($issue, $issuer->id);
            } catch (Throwable $e) {
                $command?->warn('  Stock issue skipped — '.$e->getMessage());
            }
        }

        $command?->info('  Supply chain supplement complete.');
    }

    protected function seedAssetDepreciation(OperationalDemoContext $ctx, ?Command $command): void
    {
        $poster = User::query()->where('email', 'accountant@janaprints.local')->first() ?? $ctx->admin;

        for ($monthsAgo = 2; $monthsAgo >= 0; $monthsAgo--) {
            $period = now()->subMonths($monthsAgo)->format('Y-m');

            try {
                $run = $this->depreciationRuns->createDraft($ctx->company->id, $period, $poster->id, $ctx->branch->id);

                if ($run->status === DepreciationRunStatus::Completed) {
                    continue;
                }

                $this->depreciationRuns->preview($run);
                $this->depreciationRuns->execute($run, $poster->id, postJournals: true);
            } catch (Throwable $e) {
                $command?->warn('  Depreciation run skipped for '.$period.' — '.$e->getMessage());
            }
        }

        $command?->info('  Asset depreciation supplement complete.');
    }

    protected function seedCommercial(OperationalDemoContext $ctx, ?Command $command): void
    {
        if (PublicQuoteRequest::query()->where('email', 'like', '%@supplement.demo')->exists()) {
            return;
        }

        $assignee = $ctx->salesUser ?? $ctx->admin;
        $rows = [
            ['name' => 'Dennis Kiplagat', 'company' => 'AgriTech Solutions', 'service' => 'Product Catalogues'],
            ['name' => 'Mercy Wanjala', 'company' => 'City Pharmacy', 'service' => 'Prescription Pads'],
            ['name' => 'George Mutinda', 'company' => 'Sports Club KE', 'service' => 'Jersey Printing'],
        ];

        foreach ($rows as $index => $row) {
            PublicQuoteRequest::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => $row['name'],
                'company' => $row['company'],
                'phone' => '07'.fake()->numerify('########'),
                'email' => Str::slug($row['name']).'@supplement.demo',
                'service_needed' => $row['service'],
                'quantity' => (string) random_int(200, 3000),
                'deadline' => now()->addDays(10 + $index)->toDateString(),
                'message' => 'Supplemental demo public quote request.',
                'status' => PublicQuoteRequestStatus::Reviewing,
                'priority' => PublicQuoteRequestPriority::Normal,
                'expected_value' => random_int(15000, 90000),
                'probability' => random_int(40, 85),
                'source' => 'website',
                'assigned_to' => $assignee->id,
                'created_at' => $ctx->dateInPeriod(5 + ($index * 7)),
                'updated_at' => $ctx->dateInPeriod(5 + ($index * 7)),
            ]);
        }

        $command?->info('  Commercial supplement complete.');
    }
}
