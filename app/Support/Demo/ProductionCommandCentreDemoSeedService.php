<?php

namespace App\Support\Demo;

use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Department;
use App\Models\Inventory\InventoryItem;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleItem;
use App\Models\Pos\PosSession;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionSession;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerInvoiceLine;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\CustomerPaymentAllocation;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Services\Assets\MachineProfileService;
use App\Support\Production\ProductionSpecificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionCommandCentreDemoSeedService
{
    /** @var array<string, WorkCenter|null> */
    protected array $workCenters = [];

    public function run(?Command $command = null): void
    {
        $ctx = $this->resolveContext();

        if ($ctx === null) {
            $command?->warn('Production command centre demo skipped: company JANA / branch HQ not found.');

            return;
        }

        if ($this->alreadySeeded($ctx)) {
            $refreshed = $this->refreshDigitalAndOffsetForToday($ctx);
            $command?->warn("Production command centre demo already exists (DEMO-CC-* job cards).");
            $command?->info("  Refreshed {$refreshed} Digital/Offset queue rows to today's date so department boards show jobs.");

            return;
        }

        $this->loadWorkCenters($ctx);
        $this->ensureCustomers($ctx);
        $this->ensureOutsourceVendor($ctx);
        $this->ensureProductionMachineProfiles($ctx);

        DB::transaction(function () use ($ctx, $command) {
            foreach ($this->scenarios() as $index => $scenario) {
                $this->seedScenario($ctx, $scenario, $index);
            }

            $this->backfillQueuesForExistingJobs($ctx);
            $this->seedTodayCommercialActivity($ctx);

            $queueCount = ProductionQueue::query()
                ->where('company_id', $ctx->company->id)
                ->whereHas('jobCard', fn ($q) => $q->where('job_card_number', 'like', 'DEMO-CC-%'))
                ->count();

            $command?->info(sprintf(
                '  Production showcase: %d command-centre job cards, %d queue entries.',
                count($this->scenarios()),
                $queueCount,
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

        return new OperationalDemoContext(
            company: $company,
            branch: $branch,
            department: $department,
            admin: $admin,
            salesUser: User::query()->where('email', 'sales@janaprints.local')->first(),
            digitalWorkCenter: WorkCenter::query()
                ->where('company_id', $company->id)
                ->where('branch_id', $branch->id)
                ->where('code', 'DIGITAL')
                ->first(),
        );
    }

    protected function alreadySeeded(OperationalDemoContext $ctx): bool
    {
        return ProductionJobCard::query()
            ->where('company_id', $ctx->company->id)
            ->where('job_card_number', 'DEMO-CC-0001')
            ->exists();
    }

    /**
     * Department boards default-filter to production_queues.created_at = today.
     * Existing DEMO-CC Digital/Offset rows were often seeded with older dates.
     */
    protected function refreshDigitalAndOffsetForToday(OperationalDemoContext $ctx): int
    {
        $this->loadWorkCenters($ctx);

        $workCenterIds = collect([$this->workCenters['DIGITAL']?->id, $this->workCenters['OFFSET']?->id])
            ->filter()
            ->values()
            ->all();

        if ($workCenterIds === []) {
            return 0;
        }

        $queues = ProductionQueue::query()
            ->where('company_id', $ctx->company->id)
            ->whereIn('work_center_id', $workCenterIds)
            ->whereHas('jobCard', fn ($q) => $q->where('job_card_number', 'like', 'DEMO-CC-%'))
            ->with('jobCard')
            ->get();

        $now = now();
        $refreshed = 0;

        foreach ($queues as $index => $queue) {
            $queueAt = today()->setTime(8 + ($index % 6), 15 + ($index % 3) * 10);
            $queue->forceFill([
                'created_at' => $queueAt,
                'updated_at' => $queue->status === ProductionQueueStatus::Completed ? $now : $queueAt,
            ])->saveQuietly();

            $job = $queue->jobCard;
            if ($job !== null) {
                $isOverdue = $job->required_date !== null
                    && Carbon::parse($job->required_date)->lt(today());

                $job->forceFill([
                    'created_at' => $queueAt,
                    'planned_start_date' => $queueAt->toDateString(),
                    'required_date' => $isOverdue
                        ? today()->subDays(2)->toDateString()
                        : ($job->required_date ?? today()->addDays(2)->toDateString()),
                    'planned_end_date' => $isOverdue
                        ? today()->subDays(2)->toDateString()
                        : ($job->planned_end_date ?? today()->addDays(2)->toDateString()),
                    'updated_at' => $now,
                ])->saveQuietly();
            }

            $refreshed++;
        }

        return $refreshed;
    }

    protected function loadWorkCenters(OperationalDemoContext $ctx): void
    {
        foreach (['DIGITAL', 'OFFSET', 'LARGE_FORMAT', 'FINISHING', 'PACKAGING'] as $code) {
            $this->workCenters[$code] = WorkCenter::query()
                ->where('company_id', $ctx->company->id)
                ->where('branch_id', $ctx->branch->id)
                ->where('code', $code)
                ->first();
        }
    }

    protected function ensureCustomers(OperationalDemoContext $ctx): void
    {
        $existing = Customer::query()
            ->where('company_id', $ctx->company->id)
            ->orderBy('id')
            ->limit(12)
            ->get();

        if ($existing->isNotEmpty()) {
            $ctx->customers = $existing;

            return;
        }

        $profiles = [
            ['company_name' => 'Showcase Retail Ltd', 'contact' => 'Alice Kamau', 'city' => 'Nairobi'],
            ['company_name' => 'Summit Events Co', 'contact' => 'Brian Otieno', 'city' => 'Nairobi'],
            ['company_name' => 'Harbor Logistics', 'contact' => 'Diana Wanjiku', 'city' => 'Mombasa'],
        ];

        $ctx->customers = collect();

        foreach ($profiles as $index => $profile) {
            $ctx->customers->push(Customer::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_code' => sprintf('CC-DEMO-%03d', $index + 1),
                'company_name' => $profile['company_name'],
                'contact_person' => $profile['contact'],
                'customer_type' => CustomerType::Corporate,
                'status' => CustomerStatus::Active,
                'city' => $profile['city'],
                'phone' => '0712'.fake()->numerify('######'),
                'email' => Str::slug($profile['company_name']).'@demo.local',
            ]));
        }
    }

    protected function ensureOutsourceVendor(OperationalDemoContext $ctx): void
    {
        $vendor = Vendor::query()->firstOrCreate(
            ['company_id' => $ctx->company->id, 'vendor_code' => 'VEND-OUTPRINT'],
            [
                'vendor_name' => 'Premier Print Partners',
                'phone' => '0207654321',
                'email' => 'jobs@premierprint.demo',
                'payment_terms' => 'Net 14',
                'status' => \App\Enums\VendorStatus::Active,
                'is_production_vendor' => true,
            ],
        );

        if (! $vendor->is_production_vendor) {
            $vendor->update(['is_production_vendor' => true]);
        }
    }

    protected function ensureProductionMachineProfiles(OperationalDemoContext $ctx): void
    {
        $profiles = [
            'FA-PR-001' => ['machine_code' => 'CN910', 'machine_type' => 'Digital Press'],
            'FA-PR-002' => ['machine_code' => 'RL640', 'machine_type' => 'Large Format'],
        ];

        $service = app(MachineProfileService::class);

        foreach ($profiles as $assetNumber => $data) {
            $asset = FixedAsset::query()
                ->where('company_id', $ctx->company->id)
                ->where('asset_number', $assetNumber)
                ->first();

            if (! $asset || $asset->machineProfile) {
                continue;
            }

            $service->createForAsset($asset, [
                ...$data,
                'hourly_capacity' => 2,
                'shift_capacity' => 10,
            ], (int) $ctx->admin->id);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function scenarios(): array
    {
        return [
            // Digital — all queue stages
            $this->row('0001', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::Waiting, ProductionJobCardStatus::Queued, 'Digital business cards', 500, 18, [
                'colour_mode' => 'CMYK', 'finished_size' => '90x55mm', 'ups' => 24, 'estimated_sheets' => 22, 'lamination' => false,
            ], ['required_offset' => 5]),
            $this->row('0002', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::InProgress, ProductionJobCardStatus::InProduction, 'Digital flyers A5', 2000, 12, [
                'colour_mode' => 'CMYK', 'finished_size' => '148x210mm', 'ups' => 2, 'estimated_sheets' => 1050, 'lamination' => true,
            ], ['session' => true, 'machine' => 'FA-PR-001']),
            $this->row('0003', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::Paused, ProductionJobCardStatus::OnHold, 'Digital letterheads', 5000, 8, [
                'colour_mode' => 'CMYK', 'finished_size' => 'A4', 'ups' => 1, 'estimated_sheets' => 5200,
            ]),
            $this->row('0004', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::Completed, ProductionJobCardStatus::Completed, 'Digital name badges', 200, 45, [
                'colour_mode' => 'CMYK', 'finished_size' => '100x70mm', 'ups' => 8, 'estimated_sheets' => 28,
            ], ['completed_today' => true, 'qc' => QualityCheckResult::Passed]),
            $this->row('0005', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::InProgress, ProductionJobCardStatus::InProduction, 'Digital posters A3', 100, 95, [
                'colour_mode' => 'CMYK', 'finished_size' => '297x420mm', 'ups' => 1, 'estimated_sheets' => 110,
            ], ['overdue' => true, 'session' => true, 'machine' => 'FA-PR-001']),
            $this->row('0006', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::Assigned, ProductionJobCardStatus::Queued, 'Legacy job — no spec', 300, 20, null),

            // Offset
            $this->row('0007', ProductionType::Offset, 'OFFSET', ProductionQueueStatus::Queued, ProductionJobCardStatus::Queued, 'Offset brochure 8pp', 1000, 85, [
                'colour_mode' => 'CMYK', 'sheet_size' => '640x880mm', 'finished_size' => '210x297mm', 'binding_type' => 'Saddle stitch',
                'lamination' => true, 'ups' => 8, 'estimated_sheets' => 140,
            ]),
            $this->row('0008', ProductionType::Offset, 'OFFSET', ProductionQueueStatus::InProgress, ProductionJobCardStatus::InProduction, 'Offset annual report', 500, 320, [
                'colour_mode' => 'CMYK + spot', 'sheet_size' => '640x880mm', 'finished_size' => '210x297mm', 'binding_type' => 'Perfect bind',
                'ups' => 4, 'estimated_sheets' => 135, 'lamination' => false,
            ], ['session' => true]),
            $this->row('0009', ProductionType::Offset, 'OFFSET', ProductionQueueStatus::InProgress, ProductionJobCardStatus::QualityCheck, 'Offset catalogue', 2000, 45, [
                'colour_mode' => 'CMYK', 'sheet_size' => '640x880mm', 'finished_size' => '210x297mm', 'binding_type' => 'Wire-O',
                'ups' => 16, 'estimated_sheets' => 130,
            ], ['qc' => QualityCheckResult::Passed]),
            $this->row('0010', ProductionType::Offset, 'OFFSET', ProductionQueueStatus::Completed, ProductionJobCardStatus::ReadyForDispatch, 'Offset invoice books', 50, 420, [
                'colour_mode' => '1+1', 'sheet_size' => '640x880mm', 'finished_size' => 'A5', 'numbering_required' => true,
                'ups' => 4, 'estimated_sheets' => 14,
            ], ['qc' => QualityCheckResult::Failed]),

            // Large format
            $this->row('0011', ProductionType::LargeFormat, 'LARGE_FORMAT', ProductionQueueStatus::Waiting, ProductionJobCardStatus::Queued, 'Roll-up banner 850x2000', 3, 4500, [
                'finished_size' => '850x2000mm', 'material_inventory_sku' => 'RAW-BANNER-FL', 'eyelets' => false,
            ]),
            $this->row('0012', ProductionType::LargeFormat, 'LARGE_FORMAT', ProductionQueueStatus::InProgress, ProductionJobCardStatus::InProduction, 'Building wrap panel', 1, 85000, [
                'finished_size' => '2400x1200mm', 'material_inventory_sku' => 'RAW-VINYL-G', 'eyelets' => true, 'finishing_type' => 'Welded hem',
            ], ['session' => true, 'machine' => 'FA-PR-002']),
            $this->row('0013', ProductionType::LargeFormat, 'LARGE_FORMAT', ProductionQueueStatus::Completed, ProductionJobCardStatus::Completed, 'Vehicle branding partial', 1, 45000, [
                'finished_size' => '1800x600mm', 'material_inventory_sku' => 'RAW-VINYL-G', 'eyelets' => false,
            ], ['completed_today' => true]),

            // Finishing
            $this->row('0014', ProductionType::Finishing, 'FINISHING', ProductionQueueStatus::InProgress, ProductionJobCardStatus::InProduction, 'Foiling + spot UV menus', 500, 65, [
                'foiling' => true, 'spot_uv' => true, 'lamination' => true, 'finished_size' => 'A4',
            ], ['session' => true]),
            $this->row('0015', ProductionType::Finishing, 'FINISHING', ProductionQueueStatus::Assigned, ProductionJobCardStatus::Queued, 'Embossed wedding invites', 200, 120, [
                'embossing' => true, 'die_cutting' => true, 'finished_size' => '150x150mm',
            ]),
            $this->row('0016', ProductionType::Finishing, 'FINISHING', ProductionQueueStatus::Completed, ProductionJobCardStatus::QualityCheck, 'Die-cut packaging sleeves', 1000, 25, [
                'die_cutting' => true, 'creasing' => true, 'perforation' => true,
            ], ['qc' => QualityCheckResult::ReworkRequired]),

            // Packaging
            $this->row('0017', ProductionType::Packaging, 'PACKAGING', ProductionQueueStatus::Waiting, ProductionJobCardStatus::Queued, 'Product cartons — 500 units', 500, 35, [
                'finished_size' => '200x150x80mm', 'die_cutting' => true,
            ]),
            $this->row('0018', ProductionType::Packaging, 'PACKAGING', ProductionQueueStatus::InProgress, ProductionJobCardStatus::InProduction, 'Gift boxes with insert', 250, 95, [
                'finished_size' => '250x250x100mm', 'foiling' => true,
            ], ['session' => true]),

            // Outsource
            $this->row('0019', ProductionType::Offset, 'OFFSET', ProductionQueueStatus::Assigned, ProductionJobCardStatus::Outsourced, 'Outsourced perfect bind books', 300, 280, [
                'colour_mode' => 'CMYK', 'binding_type' => 'Perfect bind', 'ups' => 2, 'estimated_sheets' => 160,
            ], ['outsource' => 'active']),
            $this->row('0020', ProductionType::Digital, 'DIGITAL', ProductionQueueStatus::Waiting, ProductionJobCardStatus::Outsourced, 'Outsourced wide-format posters', 50, 180, [
                'finished_size' => '841x1189mm', 'colour_mode' => 'CMYK',
            ], ['outsource' => 'overdue']),
            $this->row('0021', ProductionType::Offset, 'OFFSET', ProductionQueueStatus::Completed, ProductionJobCardStatus::Outsourced, 'Outsourced — returned to vendor QC', 100, 520, [
                'colour_mode' => 'CMYK', 'binding_type' => 'Saddle stitch',
            ], ['outsource' => 'returned']),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $spec
     * @param  array<string, mixed>  $flags
     * @return array<string, mixed>
     */
    protected function row(
        string $suffix,
        ProductionType $type,
        string $workCenterCode,
        ProductionQueueStatus $queueStatus,
        ProductionJobCardStatus $jobStatus,
        string $title,
        int|float $qty,
        float $unitPrice,
        ?array $spec = [],
        array $flags = [],
    ): array {
        return compact('suffix', 'type', 'workCenterCode', 'queueStatus', 'jobStatus', 'title', 'qty', 'unitPrice', 'spec', 'flags');
    }

    /**
     * @param  array<string, mixed>  $scenario
     */
    protected function seedScenario(OperationalDemoContext $ctx, array $scenario, int $index): void
    {
        $customer = $ctx->customers[$index % $ctx->customers->count()];
        $creator = User::query()->where('email', 'production@janaprints.local')->first() ?? $ctx->admin;
        $operator = User::query()->where('email', 'production@janaprints.local')->first() ?? $ctx->admin;
        $designer = User::query()->where('email', 'designer@janaprints.local')->first();

        $flags = $scenario['flags'] ?? [];
        $isPrintLane = in_array($scenario['workCenterCode'], ['DIGITAL', 'OFFSET'], true);

        // Digital/Offset department boards default-filter to queue created_at = today.
        $createdAt = isset($flags['completed_today'])
            ? today()->setTime(9, 30)
            : (isset($flags['overdue'])
                ? today()->setTime(7, 45)
                : ($isPrintLane
                    ? today()->setTime(8 + ($index % 6), 15 + ($index % 3) * 10)
                    : today()->subDays(2)->setTime(10, 0)));

        $requiredDate = isset($flags['overdue'])
            ? today()->subDays(2)
            : today()->addDays((int) ($flags['required_offset'] ?? ($isPrintLane ? 2 : 7)));

        if ($isPrintLane && ! isset($flags['overdue']) && ! isset($flags['completed_today'])) {
            $requiredDate = today()->addDays((int) ($flags['required_offset'] ?? 2));
        }

        $subtotal = round((float) $scenario['qty'] * (float) $scenario['unitPrice'], 2);
        $tax = round($subtotal * 0.16, 2);
        $total = $subtotal + $tax;

        $quotation = Quotation::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'DEMO-CC-QT-'.$scenario['suffix'],
            'quotation_date' => $createdAt->toDateString(),
            'valid_until' => $requiredDate->toDateString(),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'discount_amount' => 0,
            'total_amount' => $total,
            'status' => QuotationStatus::Converted,
            'revision_number' => 1,
            'prepared_by' => $ctx->admin->id,
            'notes' => 'Command centre showcase quotation.',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'item_name' => $scenario['title'],
            'description' => 'Showcase quotation line.',
            'quantity' => $scenario['qty'],
            'unit_price' => $scenario['unitPrice'],
            'line_total' => $subtotal,
            'sort_order' => 1,
        ]);

        $artwork = ArtworkRequest::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'request_number' => 'DEMO-CC-AW-'.$scenario['suffix'],
            'title' => $scenario['title'].' artwork',
            'description' => 'Command centre showcase artwork.',
            'requested_by' => $ctx->admin->id,
            'assigned_designer_id' => $designer?->id,
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::Approved,
            'due_date' => today()->addDays(5)->toDateString(),
            'current_version' => 1,
        ]);

        $orderStatus = match ($scenario['jobStatus']) {
            ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch => SalesOrderStatus::Completed,
            ProductionJobCardStatus::Outsourced => SalesOrderStatus::InProduction,
            default => SalesOrderStatus::InProduction,
        };

        $order = SalesOrder::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'order_number' => 'DEMO-CC-SO-'.$scenario['suffix'],
            'order_date' => $createdAt->toDateString(),
            'required_date' => $requiredDate->toDateString(),
            'status' => $orderStatus,
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'discount_amount' => 0,
            'total_amount' => $total,
            'notes' => 'Production command centre showcase order.',
            'created_by' => $ctx->admin->id,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $line = SalesOrderItem::query()->create([
            'sales_order_id' => $order->id,
            'item_name' => $scenario['title'],
            'description' => 'Showcase line for department registers.',
            'quantity' => $scenario['qty'],
            'unit_price' => $scenario['unitPrice'],
            'line_total' => $subtotal,
            'sort_order' => 1,
        ]);

        $paperItem = InventoryItem::query()
            ->where('company_id', $ctx->company->id)
            ->where('sku', 'RAW-ART350')
            ->first();

        $specPayload = null;
        if ($scenario['spec'] !== null) {
            $specPayload = array_merge([
                'production_type' => $scenario['type'],
                'product_description' => $scenario['title'],
                'quantity' => $scenario['qty'],
                'paper_inventory_item_id' => $paperItem?->id,
            ], $scenario['spec']);

            if (isset($specPayload['material_inventory_sku'])) {
                $material = InventoryItem::query()
                    ->where('company_id', $ctx->company->id)
                    ->where('sku', $specPayload['material_inventory_sku'])
                    ->first();
                $specPayload['material_inventory_item_id'] = $material?->id;
                unset($specPayload['material_inventory_sku']);
            }

            $spec = app(ProductionSpecificationService::class)
                ->createForSalesOrderItem($line, $specPayload, $creator);
        }

        $machine = isset($flags['machine'])
            ? FixedAsset::query()->where('company_id', $ctx->company->id)->where('asset_number', $flags['machine'])->first()
            : null;

        $vendor = Vendor::query()
            ->where('company_id', $ctx->company->id)
            ->where('vendor_code', 'VEND-OUTPRINT')
            ->first();

        $outsourceMeta = $this->outsourceMeta($flags['outsource'] ?? null, $total, $vendor?->id);

        $job = ProductionJobCard::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'sales_order_id' => $order->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'job_card_number' => 'DEMO-CC-'.$scenario['suffix'],
            'production_type' => $scenario['type'],
            'priority' => ProductionPriority::Normal,
            'planned_start_date' => $createdAt->toDateString(),
            'planned_end_date' => $requiredDate->toDateString(),
            'required_date' => $requiredDate->toDateString(),
            'actual_start_date' => in_array($scenario['jobStatus'], [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Outsourced,
            ], true) ? $createdAt->copy()->addDay() : null,
            'actual_end_date' => ($flags['completed_today'] ?? false) || in_array($scenario['jobStatus'], [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
            ], true) ? today()->setTime(16, 0) : null,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $creator->id,
            'assigned_machine_asset_id' => $machine?->id,
            ...$outsourceMeta,
            'created_at' => $createdAt,
            'updated_at' => ($flags['completed_today'] ?? false) ? today() : $createdAt,
        ]);

        if (isset($spec)) {
            app(ProductionSpecificationService::class)->linkToJobCard($spec, $job);
        }

        $workCenter = $this->workCenters[$scenario['workCenterCode']] ?? null;

        if ($workCenter === null) {
            return;
        }

        $queueCreated = ($flags['completed_today'] ?? false)
            ? today()->setTime(10, 0)
            : ($isPrintLane ? today()->setTime(8 + ($index % 6), 15 + ($index % 3) * 10) : $createdAt);

        $queue = ProductionQueue::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'production_job_card_id' => $job->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => (int) $scenario['suffix'],
            'assigned_operator_id' => in_array($scenario['queueStatus'], [
                ProductionQueueStatus::Assigned,
                ProductionQueueStatus::InProgress,
                ProductionQueueStatus::Paused,
                ProductionQueueStatus::Completed,
            ], true) ? $operator->id : null,
            'status' => $scenario['queueStatus'],
            'created_at' => $queueCreated,
            'updated_at' => ($flags['completed_today'] ?? false) ? today() : $queueCreated,
        ]);

        $job->update(['status' => $scenario['jobStatus']]);

        if (($flags['session'] ?? false) && $scenario['queueStatus'] === ProductionQueueStatus::InProgress) {
            ProductionSession::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'production_job_card_id' => $job->id,
                'operator_user_id' => $operator->id,
                'started_at' => today()->setTime(8, 30),
                'ended_at' => null,
                'expected_quantity' => $scenario['qty'],
                'produced_quantity' => round((float) $scenario['qty'] * 0.45, 3),
                'waste_quantity' => max(1, (int) round((float) $scenario['qty'] * 0.02)),
            ]);
        }

        if (isset($flags['qc'])) {
            QualityCheck::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'production_job_card_id' => $job->id,
                'checked_by' => $creator->id,
                'result' => $flags['qc'],
                'comments' => 'Showcase QC record for command centre demo.',
                'checked_at' => today()->subHours(2),
                'inspection_date' => today()->toDateString(),
            ]);
        }

        if (($index % 4) === 0 && $scenario['jobStatus'] !== ProductionJobCardStatus::Outsourced) {
            $this->seedInvoiceForOrder($ctx, $order, $job, $line, $index);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function outsourceMeta(?string $mode, float $sellingPrice, ?int $vendorId): array
    {
        if ($mode === null || $vendorId === null) {
            return [];
        }

        $quoted = round($sellingPrice * 0.55, 2);
        $issueDate = today()->subDays($mode === 'overdue' ? 12 : 5);

        return [
            'outsource_vendor_id' => $vendorId,
            'outsource_issue_date' => $issueDate->toDateString(),
            'outsource_expected_return' => ($mode === 'overdue'
                ? today()->subDays(2)
                : today()->addDays(4))->toDateString(),
            'outsource_quoted_cost' => $quoted,
            'outsource_actual_cost' => $mode === 'returned' ? $quoted : null,
            'outsource_notes' => match ($mode) {
                'overdue' => 'Awaiting return from vendor — overdue.',
                'returned' => 'Returned from vendor; pending internal QC.',
                default => 'Sent to Premier Print Partners for production.',
            },
            'outsourced_at' => $issueDate->copy()->setTime(9, 0),
            'returned_at' => $mode === 'returned' ? today()->subDay()->setTime(15, 0) : null,
        ];
    }

    protected function seedInvoiceForOrder(
        OperationalDemoContext $ctx,
        SalesOrder $order,
        ProductionJobCard $job,
        SalesOrderItem $line,
        int $index,
    ): void {
        $poster = User::query()->where('email', 'accountant@janaprints.local')->first() ?? $ctx->admin;
        $invoiceDate = Carbon::parse($order->order_date)->addDays(3)->toDateString();
        $total = (float) $order->total_amount;
        $paid = match ($index % 3) {
            0 => $total,
            1 => round($total / 2, 2),
            default => 0.0,
        };

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'production_job_card_id' => $job->id,
            'invoice_number' => 'DEMO-CC-INV-'.$job->job_card_number,
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => $invoiceDate,
            'due_date' => Carbon::parse($invoiceDate)->addDays(30)->toDateString(),
            'currency' => 'KES',
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'discount_amount' => 0,
            'total_amount' => $total,
            'amount_paid' => $paid,
            'balance_due' => round($total - $paid, 2),
            'billing_percent' => 100,
            'posted_by' => $poster->id,
            'posted_at' => Carbon::parse($invoiceDate)->addHours(2),
            'created_by' => $poster->id,
        ]);

        CustomerInvoiceLine::query()->create([
            'customer_invoice_id' => $invoice->id,
            'sales_order_item_id' => $line->id,
            'item_name' => $line->item_name,
            'description' => $line->description,
            'quantity' => $line->quantity,
            'unit_price' => $line->unit_price,
            'line_subtotal' => $line->line_total,
            'line_total' => $line->line_total,
            'sort_order' => 1,
        ]);

        if ($paid <= 0) {
            return;
        }

        $payment = CustomerPayment::query()->create([
            'company_id' => $ctx->company->id,
            'branch_id' => $ctx->branch->id,
            'customer_id' => $order->customer_id,
            'payment_number' => 'DEMO-CC-PAY-'.$job->job_card_number,
            'payment_date' => $invoiceDate,
            'amount' => $paid,
            'payment_method' => CustomerPaymentMethod::Bank,
            'status' => CustomerPaymentStatus::Posted,
            'reference' => 'CC-DEMO-'.Str::upper(Str::random(6)),
            'posted_by' => $poster->id,
            'posted_at' => Carbon::parse($invoiceDate)->addHours(4),
            'created_by' => $poster->id,
        ]);

        CustomerPaymentAllocation::query()->create([
            'customer_payment_id' => $payment->id,
            'customer_invoice_id' => $invoice->id,
            'amount' => $paid,
        ]);
    }

    protected function backfillQueuesForExistingJobs(OperationalDemoContext $ctx): void
    {
        $typeToWorkCenter = [
            ProductionType::Digital->value => 'DIGITAL',
            ProductionType::Offset->value => 'OFFSET',
            ProductionType::LargeFormat->value => 'LARGE_FORMAT',
            ProductionType::Finishing->value => 'FINISHING',
            ProductionType::Packaging->value => 'PACKAGING',
        ];

        ProductionJobCard::query()
            ->where('company_id', $ctx->company->id)
            ->where('branch_id', $ctx->branch->id)
            ->where('job_card_number', 'not like', 'DEMO-CC-%')
            ->whereDoesntHave('queues')
            ->whereNotIn('status', [ProductionJobCardStatus::Draft, ProductionJobCardStatus::Cancelled])
            ->each(function (ProductionJobCard $job) use ($ctx, $typeToWorkCenter) {
                $code = $typeToWorkCenter[$job->production_type?->value ?? ''] ?? 'DIGITAL';
                $workCenter = $this->workCenters[$code] ?? $this->workCenters['DIGITAL'];

                if ($workCenter === null) {
                    return;
                }

                $queueStatus = match ($job->status) {
                    ProductionJobCardStatus::InProduction => ProductionQueueStatus::InProgress,
                    ProductionJobCardStatus::OnHold => ProductionQueueStatus::Paused,
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::QualityCheck => ProductionQueueStatus::Completed,
                    ProductionJobCardStatus::Outsourced => ProductionQueueStatus::Assigned,
                    default => ProductionQueueStatus::Waiting,
                };

                ProductionQueue::query()->create([
                    'company_id' => $job->company_id,
                    'branch_id' => $job->branch_id,
                    'production_job_card_id' => $job->id,
                    'work_center_id' => $workCenter->id,
                    'queue_position' => app(\App\Support\Production\ProductionQueueService::class)
                        ->nextQueuePosition($workCenter),
                    'status' => $queueStatus,
                    'created_at' => $job->created_at ?? now(),
                    'updated_at' => $job->updated_at ?? now(),
                ]);
            });
    }

    protected function seedTodayCommercialActivity(OperationalDemoContext $ctx): void
    {
        $customer = $ctx->customers->first();
        $cashier = $ctx->salesUser ?? $ctx->admin;
        $poster = User::query()->where('email', 'accountant@janaprints.local')->first() ?? $ctx->admin;

        if ($customer === null) {
            return;
        }

        if (! SalesOrder::query()->where('order_number', 'DEMO-CC-TODAY-SO')->exists()) {
            $artwork = ArtworkRequest::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'request_number' => 'DEMO-CC-TODAY-AW',
                'title' => 'Today sales register order artwork',
                'description' => 'Daily sales register showcase.',
                'requested_by' => $ctx->admin->id,
                'priority' => ArtworkPriority::Normal,
                'status' => ArtworkRequestStatus::Approved,
                'due_date' => today()->addDays(3)->toDateString(),
                'current_version' => 1,
            ]);

            $subtotal = 12500.00;
            $tax = 2000.00;
            $total = 14500.00;

            $order = SalesOrder::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'artwork_request_id' => $artwork->id,
                'order_number' => 'DEMO-CC-TODAY-SO',
                'order_date' => today()->toDateString(),
                'required_date' => today()->addDays(7)->toDateString(),
                'status' => SalesOrderStatus::Confirmed,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $total,
                'notes' => 'Today’s order for Daily Sales Register.',
                'created_by' => $ctx->admin->id,
                'created_at' => today()->setTime(10, 15),
                'updated_at' => today()->setTime(10, 15),
            ]);

            SalesOrderItem::query()->create([
                'sales_order_id' => $order->id,
                'item_name' => 'Express business cards — 1000 pcs',
                'description' => 'Daily sales register line.',
                'quantity' => 1000,
                'unit_price' => 12.50,
                'line_total' => $subtotal,
                'sort_order' => 1,
            ]);
        }

        if (! PosSale::query()->where('sale_number', 'DEMO-CC-TODAY-POS')->exists()) {
            $session = PosSession::query()->firstOrCreate(
                [
                    'company_id' => $ctx->company->id,
                    'session_number' => 'DEMO-CC-TODAY-SES',
                ],
                [
                    'branch_id' => $ctx->branch->id,
                    'cashier_id' => $cashier->id,
                    'terminal' => 'Front Counter — Today',
                    'opening_float' => 5000,
                    'opening_cash' => 5000,
                    'expected_cash' => 8500,
                    'expected_mpesa' => 4200,
                    'expected_card' => 0,
                    'expected_bank' => 0,
                    'expected_total' => 12700,
                    'actual_cash' => 8500,
                    'variance' => 0,
                    'variance_requires_approval' => false,
                    'status' => PosSessionStatus::Open,
                    'opened_at' => today()->setTime(8, 0),
                    'opened_by' => $cashier->id,
                ],
            );

            $lineTotal = 3200.00;
            $tax = 512.00;
            $total = 3712.00;

            $sale = PosSale::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'pos_session_id' => $session->id,
                'cashier_id' => $cashier->id,
                'sale_number' => 'DEMO-CC-TODAY-POS',
                'sale_date' => today()->toDateString(),
                'status' => PosSaleStatus::Paid,
                'subtotal' => $lineTotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $total,
                'amount_paid' => $total,
                'balance_due' => 0,
                'is_walk_in' => true,
                'created_at' => today()->setTime(11, 45),
                'updated_at' => today()->setTime(11, 45),
            ]);

            PosSaleItem::query()->create([
                'pos_sale_id' => $sale->id,
                'description' => 'Same-day digital prints — A4 x 50',
                'quantity' => 50,
                'unit_price' => 64.00,
                'line_total' => $lineTotal,
            ]);

            PosPayment::query()->create([
                'pos_sale_id' => $sale->id,
                'payment_method' => PosPaymentMethod::Mpesa,
                'amount' => $total,
                'reference' => 'MPX'.today()->format('md').'001',
            ]);
        }

        if (! CustomerPayment::query()->where('payment_number', 'DEMO-CC-TODAY-PAY')->exists()) {
            $payment = CustomerPayment::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'payment_number' => 'DEMO-CC-TODAY-PAY',
                'payment_date' => today()->toDateString(),
                'amount' => 5800.00,
                'payment_method' => CustomerPaymentMethod::Mpesa,
                'status' => CustomerPaymentStatus::Posted,
                'reference' => 'MPX'.today()->format('md').'PAY',
                'posted_by' => $poster->id,
                'posted_at' => today()->setTime(14, 30),
                'created_by' => $poster->id,
                'created_at' => today()->setTime(14, 30),
                'updated_at' => today()->setTime(14, 30),
            ]);

            $invoice = CustomerInvoice::query()
                ->where('company_id', $ctx->company->id)
                ->where('status', CustomerInvoiceStatus::Posted)
                ->where('balance_due', '>', 0)
                ->orderByDesc('id')
                ->first();

            if ($invoice !== null) {
                $amount = min(5800.00, (float) $invoice->balance_due);

                CustomerPaymentAllocation::query()->create([
                    'customer_payment_id' => $payment->id,
                    'customer_invoice_id' => $invoice->id,
                    'amount' => $amount,
                ]);

                $invoice->update([
                    'amount_paid' => (float) $invoice->amount_paid + $amount,
                    'balance_due' => max(0, round((float) $invoice->balance_due - $amount, 2)),
                ]);
            }
        }
    }
}
