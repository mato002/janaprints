<?php

namespace App\Support\Demo;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\AssetAcquisitionSource;
use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Enums\AttendanceStatus;
use App\Enums\CommercialComplaintPriority;
use App\Enums\CommercialComplaintSource;
use App\Enums\CommercialComplaintStatus;
use App\Enums\CommercialPriceBookStatus;
use App\Enums\CommercialTicketChannel;
use App\Enums\CommercialTicketPriority;
use App\Enums\CommercialTicketStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\DepreciationMethod;
use App\Enums\EmploymentStatus;
use App\Enums\FixedAssetStatus;
use App\Enums\Gender;
use App\Enums\InventoryDocumentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Enums\PublicContactMessageStatus;
use App\Enums\PublicQuoteRequestPriority;
use App\Enums\PublicQuoteRequestStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\StockReceiptSource;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Enums\WebsiteGalleryCategory;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Commercial\CommercialComplaint;
use App\Models\Commercial\CommercialPriceBook;
use App\Models\Commercial\CommercialPriceBookItem;
use App\Models\Commercial\CommercialSupportTicket;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\StockReceiptItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Warehouse;
use App\Models\JobTitle;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleItem;
use App\Models\Pos\PosSession;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\PublicContactMessage;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerInvoiceLine;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\CustomerPaymentAllocation;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Models\WebsiteGalleryItem;
use App\Support\Production\ProductionQueueService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OperationalDemoSeedService
{
    /** @var list<array{company_name: string, contact: string, city: string, phone: string, email: string}> */
    private array $customerProfiles = [
        ['company_name' => 'Safari Capital Ltd', 'contact' => 'James Mutua', 'city' => 'Nairobi', 'phone' => '0711001001', 'email' => 'james@safaricapital.demo'],
        ['company_name' => 'Lakeview Academy', 'contact' => 'Mary Wanjiru', 'city' => 'Kisumu', 'phone' => '0711001002', 'email' => 'mary@lakeviewacademy.demo'],
        ['company_name' => 'Coastal Events Kenya', 'contact' => 'Amina Hassan', 'city' => 'Mombasa', 'phone' => '0711001003', 'email' => 'amina@coastalevents.demo'],
        ['company_name' => 'Highlands NGO Trust', 'contact' => 'Peter Ochieng', 'city' => 'Nakuru', 'phone' => '0711001004', 'email' => 'peter@highlandsngo.demo'],
        ['company_name' => 'Rift Logistics Group', 'contact' => 'David Kamau', 'city' => 'Eldoret', 'phone' => '0711001005', 'email' => 'david@riftlogistics.demo'],
        ['company_name' => 'Westlands Retail Hub', 'contact' => 'Grace Njeri', 'city' => 'Nairobi', 'phone' => '0711001006', 'email' => 'grace@westlandsretail.demo'],
        ['company_name' => 'Thika Manufacturing Co', 'contact' => 'Samuel Mwangi', 'city' => 'Thika', 'phone' => '0711001007', 'email' => 'samuel@thikamfg.demo'],
        ['company_name' => 'Greenfield Clinic', 'contact' => 'Lucy Akinyi', 'city' => 'Nairobi', 'phone' => '0711001008', 'email' => 'lucy@greenfieldclinic.demo'],
        ['company_name' => 'Savanna Hotels Group', 'contact' => 'Brian Otieno', 'city' => 'Nairobi', 'phone' => '0711001009', 'email' => 'brian@savannahotels.demo'],
        ['company_name' => 'Metro Church Network', 'contact' => 'Faith Chebet', 'city' => 'Nairobi', 'phone' => '0711001010', 'email' => 'faith@metrochurch.demo'],
        ['company_name' => 'Pioneer Insurance Brokers', 'contact' => 'Eric Kiprop', 'city' => 'Nairobi', 'phone' => '0711001011', 'email' => 'eric@pioneerinsurance.demo'],
        ['company_name' => 'Sunrise Primary School', 'contact' => 'Jane Muthoni', 'city' => 'Machakos', 'phone' => '0711001012', 'email' => 'jane@sunriseprimary.demo'],
        ['company_name' => 'Urban Fitness Studios', 'contact' => 'Kevin Odhiambo', 'city' => 'Nairobi', 'phone' => '0711001013', 'email' => 'kevin@urbanfitness.demo'],
        ['company_name' => 'Heritage Museum Trust', 'contact' => 'Wanjiku Maina', 'city' => 'Nairobi', 'phone' => '0711001014', 'email' => 'wanjiku@heritagemuseum.demo'],
        ['company_name' => 'Blue Ocean Exports', 'contact' => 'Hassan Ali', 'city' => 'Mombasa', 'phone' => '0711001015', 'email' => 'hassan@blueocean.demo'],
    ];

    /** @var list<array{title: string, qty: int, price: float}> */
    private array $printProducts = [
        ['title' => 'Business Cards — 500 pcs', 'qty' => 500, 'price' => 18],
        ['title' => 'A5 Flyers — 2000 pcs', 'qty' => 2000, 'price' => 12],
        ['title' => 'Brochure 8pp — 1000 pcs', 'qty' => 1000, 'price' => 85],
        ['title' => 'Roll-up Banner — 3 pcs', 'qty' => 3, 'price' => 4500],
        ['title' => 'Branded T-Shirts — 100 pcs', 'qty' => 100, 'price' => 650],
        ['title' => 'Vehicle Branding — partial wrap', 'qty' => 1, 'price' => 45000],
        ['title' => 'Invoice Books — 50 books', 'qty' => 50, 'price' => 420],
        ['title' => 'Corporate Letterheads — 5000 sheets', 'qty' => 5000, 'price' => 8],
    ];

    public function run(?Command $command = null): void
    {
        $ctx = $this->resolveContext();

        if ($ctx === null) {
            $command?->warn('Operational demo skipped: company JANA / branch HQ not found.');

            return;
        }

        if ($this->alreadySeeded($ctx)) {
            $command?->warn('Operational demo data already exists — skipping. Run migrate:fresh --seed for a clean dataset.');

            return;
        }

        DB::transaction(function () use ($ctx, $command) {
            $this->seedExtendedStaff($ctx);
            $this->seedCustomers($ctx);
            $this->seedLeads($ctx);
            $this->seedInventory($ctx);
            $this->seedVendors($ctx);
            $this->seedCommercialReference($ctx);
            $this->seedSalesPipeline($ctx);
            $this->seedProduction($ctx);
            $this->seedFinance($ctx);
            $this->seedProcurement($ctx);
            $this->seedCommercialIssues($ctx);
            $this->seedPublicChannel($ctx);
            $this->seedWebsite($ctx);
            $this->seedHrActivity($ctx);
            $this->seedAssets($ctx);
            $this->seedPos($ctx);

            $command?->info(sprintf(
                '  Created: %d customers, %d quotations, %d sales orders, %d job cards, %d invoices.',
                $ctx->customers->count(),
                Quotation::query()->where('company_id', $ctx->company->id)->count(),
                SalesOrder::query()->where('company_id', $ctx->company->id)->count(),
                ProductionJobCard::query()->where('company_id', $ctx->company->id)->count(),
                CustomerInvoice::query()->where('company_id', $ctx->company->id)->count(),
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
            sheetUom: UnitOfMeasure::query()->where('company_id', $company->id)->where('code', 'SHEET')->first(),
            pieceUom: UnitOfMeasure::query()->where('company_id', $company->id)->where('code', 'PIECE')->first(),
            paperCategory: InventoryCategory::query()->where('company_id', $company->id)->where('code', 'PAPER')->first(),
            digitalWorkCenter: WorkCenter::query()->where('company_id', $company->id)->where('code', 'DIGITAL')->first(),
            websiteLeadSource: LeadSource::query()->where('company_id', $company->id)->where('name', 'Website')->first()
                ?? LeadSource::query()->where('company_id', $company->id)->first(),
            newLeadStage: LeadStage::query()->where('company_id', $company->id)->where('slug', 'new')->first()
                ?? LeadStage::query()->where('company_id', $company->id)->orderBy('sort_order')->first(),
            wonLeadStage: LeadStage::query()->where('company_id', $company->id)->where('slug', 'won')->first(),
        );

        $ctx->users = User::query()->where('company_id', $company->id)->get();
        $this->initializeDocumentCounters($ctx);

        return $ctx;
    }

    public function bootstrapSupplementCounters(OperationalDemoContext $ctx): void
    {
        $this->initializeDocumentCounters($ctx);
    }

    protected function alreadySeeded(OperationalDemoContext $ctx): bool
    {
        return Customer::query()
            ->where('company_id', $ctx->company->id)
            ->where('customer_code', 'CUST-0101')
            ->exists();
    }

    protected function initializeDocumentCounters(OperationalDemoContext $ctx): void
    {
        $prefixes = [
            'DEMO-QT' => Quotation::class,
            'DEMO-AW' => ArtworkRequest::class,
            'DEMO-SO' => SalesOrder::class,
            'DEMO-JC' => ProductionJobCard::class,
            'DEMO-INV' => CustomerInvoice::class,
            'DEMO-RCP' => CustomerPayment::class,
            'DEMO-PO' => PurchaseOrder::class,
            'DEMO-TKT' => CommercialSupportTicket::class,
            'DEMO-POS-SES' => PosSession::class,
            'DEMO-POS-SALE' => PosSale::class,
            'DEMO-SUP-QT' => Quotation::class,
            'DEMO-SUP-AW' => ArtworkRequest::class,
            'DEMO-SUP-SO' => SalesOrder::class,
            'DEMO-SUP-JC' => ProductionJobCard::class,
            'DEMO-SUP-INV' => CustomerInvoice::class,
            'DEMO-SUP-RCP' => CustomerPayment::class,
            'DEMO-SUP-SI' => StockIssue::class,
        ];

        foreach ($prefixes as $prefix => $model) {
            $column = match ($model) {
                Quotation::class => 'quotation_number',
                ArtworkRequest::class => 'request_number',
                SalesOrder::class => 'order_number',
                ProductionJobCard::class => 'job_card_number',
                CustomerInvoice::class => 'invoice_number',
                CustomerPayment::class => 'payment_number',
                PurchaseOrder::class => 'po_number',
                CommercialSupportTicket::class => 'ticket_number',
                PosSession::class => 'session_number',
                PosSale::class => 'sale_number',
                StockIssue::class => 'issue_number',
                default => null,
            };

            if ($column === null) {
                continue;
            }

            $latest = $model::query()
                ->where('company_id', $ctx->company->id)
                ->where($column, 'like', $prefix.'-%')
                ->orderByDesc('id')
                ->value($column);

            if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches)) {
                $ctx->counters[$prefix] = (int) $matches[1];
            }
        }
    }

    protected function seedExtendedStaff(OperationalDemoContext $ctx): void
    {
        $password = Hash::make(env('DEMO_USER_PASSWORD', 'password'));

        $staff = [
            ['num' => 'EMP-0005', 'first' => 'Brian', 'last' => 'Kariuki', 'email' => 'designer@janaprints.local', 'title' => 'Graphic Designer', 'role' => 'Designer'],
            ['num' => 'EMP-0006', 'first' => 'Alice', 'last' => 'Wambui', 'email' => 'production@janaprints.local', 'title' => 'Production Supervisor', 'role' => 'Production'],
            ['num' => 'EMP-0007', 'first' => 'Joseph', 'last' => 'Mutiso', 'email' => 'storekeeper@janaprints.local', 'title' => 'Storekeeper', 'role' => 'Storekeeper'],
            ['num' => 'EMP-0008', 'first' => 'Ruth', 'last' => 'Achieng', 'email' => 'accountant@janaprints.local', 'title' => 'Accountant', 'role' => 'Accountant'],
            ['num' => 'EMP-0009', 'first' => 'Daniel', 'last' => 'Kipchoge', 'email' => 'hr@janaprints.local', 'title' => 'HR Officer', 'role' => 'HR'],
        ];

        foreach ($staff as $row) {
            $jobTitle = JobTitle::query()->where('company_id', $ctx->company->id)->where('title', $row['title'])->first();

            $employee = Employee::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'employee_number' => $row['num']],
                [
                    'branch_id' => $ctx->branch->id,
                    'department_id' => $ctx->department->id,
                    'job_title_id' => $jobTitle?->id,
                    'first_name' => $row['first'],
                    'last_name' => $row['last'],
                    'gender' => Gender::Other,
                    'email' => $row['email'],
                    'designation' => $row['title'],
                    'hire_date' => $ctx->periodStart->copy()->subMonths(6)->toDateString(),
                    'employment_status' => EmploymentStatus::Active,
                    'is_active' => true,
                ],
            );

            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => "{$row['first']} {$row['last']}",
                    'password' => $password,
                    'company_id' => $ctx->company->id,
                    'default_branch_id' => $ctx->branch->id,
                    'employee_id' => $employee->id,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->syncRoles([$row['role']]);

            $employee->update([
                'activation_role' => $row['role'],
                'activation_status' => \App\Enums\EmailIdentity\EmployeeActivationStatus::Activated,
            ]);
        }

        $ctx->users = User::query()->where('company_id', $ctx->company->id)->get();
    }

    protected function seedCustomers(OperationalDemoContext $ctx): void
    {
        foreach ($this->customerProfiles as $index => $profile) {
            $ctx->customers->push(Customer::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'customer_code' => 'CUST-'.str_pad((string) ($index + 101), 4, '0', STR_PAD_LEFT)],
                [
                    'branch_id' => $ctx->branch->id,
                    'customer_type' => CustomerType::Corporate,
                    'company_name' => $profile['company_name'],
                    'contact_person' => $profile['contact'],
                    'phone' => $profile['phone'],
                    'email' => $profile['email'],
                    'city' => $profile['city'],
                    'status' => CustomerStatus::Active,
                    'credit_limit' => random_int(100000, 800000),
                    'payment_terms' => 'Net 30',
                ],
            ));
        }
    }

    protected function seedLeads(OperationalDemoContext $ctx): void
    {
        $stages = LeadStage::query()->where('company_id', $ctx->company->id)->orderBy('sort_order')->get();
        $assignee = $ctx->salesUser ?? $ctx->admin;

        $leadNames = [
            'Annual report printing enquiry',
            'Fleet branding for 12 vans',
            'School prospectus 2026 run',
            'Restaurant menu redesign',
            'Election campaign posters',
            'Pharmacy label printing',
            'Hotel welcome pack collateral',
            'Church conference banners',
            'Real estate brochure launch',
            'NGO donor report bindery',
            'Supermarket shelf wobblers',
            'University graduation programmes',
            'Gym membership cards',
            'Law firm stationery refresh',
            'Coffee shop packaging boxes',
        ];

        foreach ($leadNames as $index => $name) {
            $createdAt = $ctx->dateInPeriod($index * 4);
            $stage = $stages->get($index % max(1, $stages->count()));
            $status = match ($stage?->slug) {
                'won' => LeadStatus::Won,
                'lost' => LeadStatus::Lost,
                default => LeadStatus::Open,
            };

            Lead::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'lead_source_id' => $ctx->websiteLeadSource?->id,
                'assigned_to' => $assignee->id,
                'stage_id' => $stage?->id,
                'lead_name' => $name,
                'company_name' => fake()->company(),
                'phone' => '07'.fake()->numerify('########'),
                'email' => fake()->unique()->safeEmail(),
                'estimated_value' => random_int(15000, 250000),
                'probability' => random_int(20, 90),
                'expected_close_date' => $createdAt->copy()->addDays(random_int(7, 30))->toDateString(),
                'status' => $status,
                'notes' => 'Demo lead seeded for pipeline testing.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    protected function seedInventory(OperationalDemoContext $ctx): void
    {
        if (! $ctx->mainWarehouse || ! $ctx->sheetUom) {
            return;
        }

        $items = [
            ['sku' => 'RAW-ART350', 'name' => 'Art Paper 350gsm A2', 'cost' => 12.50, 'qty' => 2500],
            ['sku' => 'RAW-GLOSS130', 'name' => 'Gloss Paper 130gsm A3', 'cost' => 6.80, 'qty' => 5000],
            ['sku' => 'RAW-INK-C', 'name' => 'Cyan Process Ink 1L', 'cost' => 42.00, 'qty' => 80],
            ['sku' => 'RAW-INK-M', 'name' => 'Magenta Process Ink 1L', 'cost' => 42.00, 'qty' => 75],
            ['sku' => 'RAW-INK-Y', 'name' => 'Yellow Process Ink 1L', 'cost' => 42.00, 'qty' => 70],
            ['sku' => 'RAW-INK-K', 'name' => 'Black Process Ink 1L', 'cost' => 38.00, 'qty' => 90],
            ['sku' => 'RAW-BANNER-FL', 'name' => 'Frontlit Banner 440gsm', 'cost' => 18.00, 'qty' => 1200],
            ['sku' => 'RAW-VINYL-G', 'name' => 'Gloss Vinyl Roll', 'cost' => 22.00, 'qty' => 600],
            ['sku' => 'RAW-LAM-MAT', 'name' => 'Matte Lamination Film', 'cost' => 3.20, 'qty' => 3000],
            ['sku' => 'RAW-BOX-STD', 'name' => 'Standard Packaging Box', 'cost' => 2.10, 'qty' => 800],
        ];

        foreach ($items as $index => $row) {
            $item = InventoryItem::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'branch_id' => $ctx->branch->id, 'sku' => $row['sku']],
                [
                    'inventory_category_id' => $ctx->paperCategory?->id,
                    'unit_of_measure_id' => $ctx->sheetUom->id,
                    'item_name' => $row['name'],
                    'standard_cost' => $row['cost'],
                    'reorder_level' => 200,
                    'reorder_quantity' => 500,
                    'is_active' => true,
                ],
            );

            InventoryValuation::query()->updateOrCreate(
                [
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'inventory_item_id' => $item->id,
                    'warehouse_id' => $ctx->mainWarehouse->id,
                ],
                [
                    'quantity_on_hand' => $row['qty'],
                    'average_unit_cost' => $row['cost'],
                    'last_calculated_at' => $ctx->randomDateInPeriod(),
                ],
            );

            if ($index === 0) {
                $receiptDate = $ctx->dateInPeriod(2)->toDateString();
                $receiptNumber = 'DEMO-SR-OPENING';

                $receipt = StockReceipt::query()->firstOrCreate(
                    [
                        'company_id' => $ctx->company->id,
                        'receipt_number' => $receiptNumber,
                    ],
                    [
                        'branch_id' => $ctx->branch->id,
                        'warehouse_id' => $ctx->mainWarehouse->id,
                        'source' => StockReceiptSource::Adjustment,
                        'receipt_date' => $receiptDate,
                        'status' => InventoryDocumentStatus::Posted,
                        'notes' => 'Opening stock — demo seed',
                        'received_by' => $ctx->admin->id,
                        'posted_at' => $ctx->dateInPeriod(2),
                    ],
                );

                StockReceiptItem::query()->firstOrCreate(
                    [
                        'stock_receipt_id' => $receipt->id,
                        'inventory_item_id' => $item->id,
                    ],
                    [
                        'quantity' => $row['qty'],
                        'unit_cost' => $row['cost'],
                    ],
                );
            }
        }
    }

    protected function seedVendors(OperationalDemoContext $ctx): void
    {
        $vendors = [
            ['code' => 'VEND-PAPER', 'name' => 'Kenya Paper Supplies Ltd', 'type' => VendorType::Supplier],
            ['code' => 'VEND-INK', 'name' => 'ColorTech Inks East Africa', 'type' => VendorType::Supplier],
            ['code' => 'VEND-MACH', 'name' => 'PrintEquip Services', 'type' => VendorType::ServiceProvider],
            ['code' => 'VEND-FREIGHT', 'name' => 'Swift Cargo Kenya', 'type' => VendorType::Contractor],
            ['code' => 'VEND-UTIL', 'name' => 'Industrial Area Utilities', 'type' => VendorType::ServiceProvider],
        ];

        foreach ($vendors as $row) {
            Vendor::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'vendor_code' => $row['code']],
                [
                    'vendor_name' => $row['name'],
                    'vendor_type' => $row['type'],
                    'phone' => '020'.fake()->numerify('#######'),
                    'email' => Str::slug($row['code']).'@vendor.demo',
                    'payment_terms' => 'Net 30',
                    'status' => VendorStatus::Active,
                ],
            );
        }
    }

    protected function seedCommercialReference(OperationalDemoContext $ctx): void
    {
        $book = CommercialPriceBook::query()->firstOrCreate(
            ['company_id' => $ctx->company->id, 'code' => 'STANDARD-2026'],
            [
                'branch_id' => $ctx->branch->id,
                'name' => 'Standard Print Price Book 2026',
                'currency' => 'KES',
                'status' => CommercialPriceBookStatus::Active,
                'starts_at' => $ctx->periodStart,
                'is_default' => true,
                'description' => 'Demo price book for testing quotes and commercial workspace.',
                'created_by' => $ctx->admin->id,
            ],
        );

        foreach (array_slice($this->printProducts, 0, 5) as $index => $product) {
            CommercialPriceBookItem::query()->firstOrCreate(
                ['price_book_id' => $book->id, 'service_code' => 'PB-'.($index + 1)],
                [
                    'description' => $product['title'],
                    'unit_price' => $product['price'],
                    'minimum_quantity' => 1,
                    'status' => CommercialPriceBookStatus::Active,
                    'effective_from' => $ctx->periodStart,
                ],
            );
        }
    }

    protected function seedSalesPipeline(OperationalDemoContext $ctx): void
    {
        $quotationStatuses = [
            QuotationStatus::Draft,
            QuotationStatus::Sent,
            QuotationStatus::Accepted,
            QuotationStatus::Converted,
            QuotationStatus::Rejected,
            QuotationStatus::Expired,
        ];

        $orderStatuses = [
            SalesOrderStatus::Confirmed,
            SalesOrderStatus::ReadyForProduction,
            SalesOrderStatus::InProduction,
            SalesOrderStatus::Completed,
            SalesOrderStatus::Delivered,
            SalesOrderStatus::Closed,
        ];

        $preparer = $ctx->salesUser ?? $ctx->admin;
        $designer = User::query()->where('email', 'designer@janaprints.local')->first();

        foreach ($ctx->customers->take(12) as $index => $customer) {
            $quoteDate = $ctx->dateInPeriod($index * 5)->toDateString();
            $product = $this->printProducts[$index % count($this->printProducts)];
            $qty = (float) $product['qty'];
            $unitPrice = (float) $product['price'];
            $subtotal = round($qty * $unitPrice, 2);
            $tax = round($subtotal * 0.16, 2);
            $total = $subtotal + $tax;
            $qStatus = $quotationStatuses[$index % count($quotationStatuses)];

            $quotation = Quotation::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'quotation_number' => $ctx->nextNumber('DEMO-QT'),
                'quotation_date' => $quoteDate,
                'valid_until' => Carbon::parse($quoteDate)->addDays(14)->toDateString(),
                'currency' => 'KES',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $total,
                'status' => $qStatus,
                'revision_number' => 1,
                'prepared_by' => $preparer->id,
                'notes' => 'Demo quotation for '.$product['title'],
                'created_at' => $quoteDate,
                'updated_at' => $quoteDate,
            ]);

            QuotationItem::query()->create([
                'quotation_id' => $quotation->id,
                'item_name' => $product['title'],
                'description' => 'Professional print run with artwork approval workflow.',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $subtotal,
                'sort_order' => 1,
            ]);

            CustomerActivity::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'user_id' => $preparer->id,
                'activity_type' => ActivityType::FollowUp,
                'status' => ActivityStatus::Completed,
                'subject' => 'Quotation '.$quotation->quotation_number.' created',
                'description' => 'Follow up within 48 hours.',
                'activity_at' => Carbon::parse($quoteDate),
            ]);

            if (! in_array($qStatus, [QuotationStatus::Accepted, QuotationStatus::Converted], true)) {
                continue;
            }

            $artwork = ArtworkRequest::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'quotation_id' => $quotation->id,
                'request_number' => $ctx->nextNumber('DEMO-AW'),
                'title' => $product['title'].' artwork',
                'description' => 'Prepare print-ready artwork with brand colours.',
                'requested_by' => $preparer->id,
                'assigned_designer_id' => $designer?->id,
                'priority' => ArtworkPriority::Normal,
                'status' => $index % 3 === 0 ? ArtworkRequestStatus::Approved : ArtworkRequestStatus::InDesign,
                'due_date' => Carbon::parse($quoteDate)->addDays(5)->toDateString(),
                'current_version' => 1,
            ]);

            $this->seedDemoArtworkVersion($artwork, $designer?->id ?? $preparer->id);

            $orderDate = Carbon::parse($quoteDate)->addDays(3)->toDateString();
            $oStatus = $orderStatuses[min($index, count($orderStatuses) - 1)];

            $order = SalesOrder::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customer->id,
                'quotation_id' => $quotation->id,
                'artwork_request_id' => $artwork->id,
                'order_number' => $ctx->nextNumber('DEMO-SO'),
                'order_date' => $orderDate,
                'required_date' => Carbon::parse($orderDate)->addDays(10)->toDateString(),
                'status' => $oStatus,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $total,
                'notes' => 'Demo sales order linked to quotation '.$quotation->quotation_number,
                'created_by' => $preparer->id,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            SalesOrderItem::query()->create([
                'sales_order_id' => $order->id,
                'item_name' => $product['title'],
                'description' => 'Linked to quotation line 1',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $subtotal,
                'sort_order' => 1,
            ]);

            if ($qStatus === QuotationStatus::Accepted) {
                $quotation->update(['status' => QuotationStatus::Converted]);
            }
        }
    }

    protected function seedProduction(OperationalDemoContext $ctx): void
    {
        $statuses = [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
        ];

        $creator = User::query()->where('email', 'production@janaprints.local')->first() ?? $ctx->admin;

        ProductionQueueService::withoutQueueEnforcement(function () use ($ctx, $statuses, $creator) {
            SalesOrder::query()
                ->where('company_id', $ctx->company->id)
                ->whereIn('status', [
                    SalesOrderStatus::ReadyForProduction,
                    SalesOrderStatus::InProduction,
                    SalesOrderStatus::Completed,
                    SalesOrderStatus::Delivered,
                    SalesOrderStatus::Closed,
                ])
                ->each(function (SalesOrder $order, int $index) use ($ctx, $statuses, $creator) {
                    if (ProductionJobCard::query()->where('sales_order_id', $order->id)->exists()) {
                        return;
                    }

                    $start = Carbon::parse($order->order_date)->addDays(2);
                    $status = $statuses[min($index, count($statuses) - 1)];

                    ProductionJobCard::query()->create([
                        'company_id' => $ctx->company->id,
                        'branch_id' => $ctx->branch->id,
                        'sales_order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'quotation_id' => $order->quotation_id,
                        'artwork_request_id' => $order->artwork_request_id,
                        'job_card_number' => $ctx->nextNumber('DEMO-JC'),
                        'production_type' => ProductionType::Digital,
                        'priority' => ProductionPriority::Normal,
                        'planned_start_date' => $start->toDateString(),
                        'planned_end_date' => $start->copy()->addDays(5)->toDateString(),
                        'actual_start_date' => $start,
                        'actual_end_date' => in_array($status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true)
                            ? $start->copy()->addDays(4)
                            : null,
                        'status' => $status,
                        'created_by' => $creator->id,
                    ]);
                });
        });
    }

    protected function seedFinance(OperationalDemoContext $ctx): void
    {
        $poster = User::query()->where('email', 'accountant@janaprints.local')->first() ?? $ctx->admin;

        SalesOrder::query()
            ->with('jobCard')
            ->where('company_id', $ctx->company->id)
            ->whereIn('status', [
                SalesOrderStatus::Completed,
                SalesOrderStatus::Delivered,
                SalesOrderStatus::Closed,
            ])
            ->each(function (SalesOrder $order, int $index) use ($ctx, $poster) {
                $invoiceDate = Carbon::parse($order->order_date)->addDays(8)->toDateString();
                $total = (float) $order->total_amount;
                $paid = $index % 3 === 0 ? $total : ($index % 3 === 1 ? round($total / 2, 2) : 0);

                $invoice = CustomerInvoice::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'customer_id' => $order->customer_id,
                    'sales_order_id' => $order->id,
                    'production_job_card_id' => $order->jobCard?->id,
                    'invoice_number' => $ctx->nextNumber('DEMO-INV'),
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

                SalesOrderItem::query()->where('sales_order_id', $order->id)->each(function (SalesOrderItem $line) use ($invoice) {
                    CustomerInvoiceLine::query()->create([
                        'customer_invoice_id' => $invoice->id,
                        'sales_order_item_id' => $line->id,
                        'item_name' => $line->item_name,
                        'description' => $line->description,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->unit_price,
                        'line_subtotal' => $line->line_total,
                        'line_total' => $line->line_total,
                        'sort_order' => $line->sort_order,
                    ]);
                });

                if ($paid <= 0) {
                    return;
                }

                $payment = CustomerPayment::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'customer_id' => $order->customer_id,
                    'payment_number' => $ctx->nextNumber('DEMO-RCP'),
                    'payment_date' => Carbon::parse($invoiceDate)->addDays(5)->toDateString(),
                    'payment_method' => CustomerPaymentMethod::Bank,
                    'reference' => 'DEMO-BANK-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'currency' => 'KES',
                    'amount' => $paid,
                    'allocated_amount' => $paid,
                    'unallocated_amount' => 0,
                    'status' => CustomerPaymentStatus::Posted,
                    'notes' => 'Demo customer payment',
                    'posted_by' => $poster->id,
                    'posted_at' => Carbon::parse($invoiceDate)->addDays(5)->addHours(3),
                    'created_by' => $poster->id,
                ]);

                CustomerPaymentAllocation::query()->create([
                    'customer_payment_id' => $payment->id,
                    'customer_invoice_id' => $invoice->id,
                    'amount' => $paid,
                ]);
            });
    }

    protected function seedProcurement(OperationalDemoContext $ctx): void
    {
        $vendor = Vendor::query()->where('company_id', $ctx->company->id)->where('vendor_code', 'VEND-PAPER')->first();
        $item = InventoryItem::query()->where('company_id', $ctx->company->id)->where('sku', 'RAW-ART350')->first();

        if (! $vendor || ! $item) {
            return;
        }

        $statuses = [
            PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::PartiallyReceived,
            PurchaseOrderStatus::Received,
            PurchaseOrderStatus::Closed,
        ];

        foreach ($statuses as $index => $status) {
            $orderDate = $ctx->dateInPeriod(10 + ($index * 12))->toDateString();
            $qty = 500 + ($index * 250);
            $unitCost = 12.50;
            $subtotal = round($qty * $unitCost, 2);
            $tax = round($subtotal * 0.16, 2);

            $po = PurchaseOrder::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'vendor_id' => $vendor->id,
                'po_number' => $ctx->nextNumber('DEMO-PO'),
                'order_date' => $orderDate,
                'expected_delivery_date' => Carbon::parse($orderDate)->addDays(7)->toDateString(),
                'status' => $status,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $subtotal + $tax,
                'prepared_by' => $ctx->admin->id,
                'approved_by' => $ctx->admin->id,
                'approved_at' => Carbon::parse($orderDate)->addHours(4),
                'notes' => 'Demo paper stock purchase order',
            ]);

            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $po->id,
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'line_total' => $subtotal,
            ]);
        }
    }

    protected function seedCommercialIssues(OperationalDemoContext $ctx): void
    {
        $customers = $ctx->customers->take(5);
        $assignee = $ctx->salesUser ?? $ctx->admin;

        $complaints = [
            ['subject' => 'Colour mismatch on business cards', 'priority' => CommercialComplaintPriority::High, 'status' => CommercialComplaintStatus::Open],
            ['subject' => 'Late delivery for brochure order', 'priority' => CommercialComplaintPriority::Medium, 'status' => CommercialComplaintStatus::Investigating],
            ['subject' => 'Damaged cartons on arrival', 'priority' => CommercialComplaintPriority::Low, 'status' => CommercialComplaintStatus::Resolved],
            ['subject' => 'Invoice amount discrepancy', 'priority' => CommercialComplaintPriority::High, 'status' => CommercialComplaintStatus::Closed],
            ['subject' => 'Artwork approval delay', 'priority' => CommercialComplaintPriority::Medium, 'status' => CommercialComplaintStatus::Open],
        ];

        foreach ($complaints as $index => $row) {
            $created = $ctx->dateInPeriod(20 + ($index * 8));

            CommercialComplaint::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customers->get($index % $customers->count())?->id,
                'subject' => $row['subject'],
                'description' => 'Demo complaint logged for customer service workspace testing.',
                'source' => CommercialComplaintSource::Email,
                'priority' => $row['priority'],
                'status' => $row['status'],
                'assigned_to' => $assignee->id,
                'reported_by' => $ctx->admin->id,
                'resolved_at' => in_array($row['status'], [CommercialComplaintStatus::Resolved, CommercialComplaintStatus::Closed], true) ? $created->copy()->addDays(3) : null,
                'closed_at' => $row['status'] === CommercialComplaintStatus::Closed ? $created->copy()->addDays(5) : null,
                'resolution_notes' => $row['status'] === CommercialComplaintStatus::Closed ? 'Credit note issued and reprint scheduled.' : null,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }

        $tickets = [
            ['subject' => 'Reprint request — faded logo', 'status' => CommercialTicketStatus::Open],
            ['subject' => 'Quote revision for banner sizes', 'status' => CommercialTicketStatus::WaitingCustomer],
            ['subject' => 'Delivery tracking update', 'status' => CommercialTicketStatus::Resolved],
            ['subject' => 'Payment receipt not received', 'status' => CommercialTicketStatus::Closed],
        ];

        foreach ($tickets as $index => $row) {
            $created = $ctx->dateInPeriod(25 + ($index * 10));

            CommercialSupportTicket::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'customer_id' => $customers->get($index % $customers->count())?->id,
                'ticket_number' => $ctx->nextNumber('DEMO-TKT'),
                'subject' => $row['subject'],
                'description' => 'Demo support ticket for commercial helpdesk testing.',
                'channel' => CommercialTicketChannel::Phone,
                'priority' => CommercialTicketPriority::Medium,
                'status' => $row['status'],
                'assigned_to' => $assignee->id,
                'created_by' => $ctx->admin->id,
                'resolved_at' => in_array($row['status'], [CommercialTicketStatus::Resolved, CommercialTicketStatus::Closed], true) ? $created->copy()->addDays(2) : null,
                'closed_at' => $row['status'] === CommercialTicketStatus::Closed ? $created->copy()->addDays(4) : null,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }
    }

    protected function seedPublicChannel(OperationalDemoContext $ctx): void
    {
        $assignee = $ctx->salesUser ?? $ctx->admin;

        $requests = [
            ['name' => 'Michael Otieno', 'company' => 'Startup Hub Nairobi', 'service' => 'Business Cards', 'status' => PublicQuoteRequestStatus::Pending],
            ['name' => 'Sarah Kimani', 'company' => 'Wellness Clinic', 'service' => 'Brochures', 'status' => PublicQuoteRequestStatus::Reviewing],
            ['name' => 'Tom Mburu', 'company' => 'Events Plus', 'service' => 'Banners', 'status' => PublicQuoteRequestStatus::Quoted],
            ['name' => 'Linda Adongo', 'company' => 'Fashion Boutique', 'service' => 'Packaging', 'status' => PublicQuoteRequestStatus::Closed],
            ['name' => 'Chris Waweru', 'company' => 'Tech Meetup KE', 'service' => 'Flyers', 'status' => PublicQuoteRequestStatus::Pending],
            ['name' => 'Spam Bot', 'company' => 'Unknown', 'service' => 'SEO Services', 'status' => PublicQuoteRequestStatus::Spam],
        ];

        foreach ($requests as $index => $row) {
            $created = $ctx->dateInPeriod($index * 6);

            PublicQuoteRequest::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => $row['name'],
                'company' => $row['company'],
                'phone' => '07'.fake()->numerify('########'),
                'email' => Str::slug($row['name']).'@public.demo',
                'service_needed' => $row['service'],
                'quantity' => (string) random_int(100, 5000),
                'deadline' => $created->copy()->addDays(14)->toDateString(),
                'message' => 'Demo public quote request from website contact form.',
                'status' => $row['status'],
                'priority' => PublicQuoteRequestPriority::Normal,
                'expected_value' => random_int(10000, 120000),
                'probability' => random_int(30, 80),
                'source' => 'website',
                'assigned_to' => $row['status'] === PublicQuoteRequestStatus::Spam ? null : $assignee->id,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }

        PublicContactMessage::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'John Kamau',
            'email' => 'john@contact.demo',
            'phone' => '0712345678',
            'subject' => 'Bulk printing enquiry',
            'message' => 'We need 10,000 flyers for a campaign next month. Please call back.',
            'status' => PublicContactMessageStatus::Unread,
            'created_at' => $ctx->dateInPeriod(15),
            'updated_at' => $ctx->dateInPeriod(15),
        ]);

        PublicContactMessage::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Helen Chepkoech',
            'email' => 'helen@contact.demo',
            'phone' => '0723456789',
            'subject' => 'Partnership opportunity',
            'message' => 'Looking to partner on corporate gifting packages.',
            'status' => PublicContactMessageStatus::Read,
            'created_at' => $ctx->dateInPeriod(40),
            'updated_at' => $ctx->dateInPeriod(41),
        ]);
    }

    protected function seedWebsite(OperationalDemoContext $ctx): void
    {
        $gallery = [
            ['title' => 'Corporate Rebrand Suite', 'category' => WebsiteGalleryCategory::BusinessCards, 'featured' => true, 'image' => '/images/storefront/gallery/business-cards.jpg'],
            ['title' => 'NGO Annual Report', 'category' => WebsiteGalleryCategory::Brochures, 'featured' => true, 'image' => '/images/storefront/gallery/brochures.jpg'],
            ['title' => 'Retail Packaging Launch', 'category' => WebsiteGalleryCategory::Packaging, 'featured' => false, 'image' => '/images/storefront/gallery/packaging.jpg'],
            ['title' => 'Exhibition Roll-Up Set', 'category' => WebsiteGalleryCategory::EventsExhibitions, 'featured' => true, 'image' => '/images/storefront/gallery/events.jpg'],
            ['title' => 'Fleet Branding Programme', 'category' => WebsiteGalleryCategory::VehicleBranding, 'featured' => false, 'image' => '/images/storefront/gallery/vehicle-branding.jpg'],
        ];

        foreach ($gallery as $index => $row) {
            WebsiteGalleryItem::query()->firstOrCreate(
                ['slug' => Str::slug($row['title'])],
                [
                    'uuid' => (string) Str::uuid(),
                    'title' => $row['title'],
                    'category' => $row['category']->value,
                    'description' => 'Demo gallery project showcasing Jana Prints capabilities.',
                    'location' => 'Nairobi, Kenya',
                    'quantity_label' => random_int(500, 5000).' units',
                    'timeline_label' => random_int(3, 10).' business days',
                    'materials_label' => 'Premium stock with finishing',
                    'outcome' => 'Delivered on time with client approval.',
                    'image_path' => $row['image'],
                    'alt_text' => $row['title'].' — Jana Prints portfolio',
                    'is_featured' => $row['featured'],
                    'is_published' => true,
                    'sort_order' => $index + 1,
                    'created_by' => $ctx->admin->id,
                    'updated_by' => $ctx->admin->id,
                ],
            );
        }
    }

    protected function seedHrActivity(OperationalDemoContext $ctx): void
    {
        $leaveType = LeaveType::query()->where('company_id', $ctx->company->id)->first();

        Employee::query()->where('company_id', $ctx->company->id)->each(function (Employee $employee, int $index) use ($ctx, $leaveType) {
            for ($week = 0; $week < 8; $week++) {
                $date = $ctx->periodStart->copy()->addWeeks($week)->addDays($index % 5);

                AttendanceRecord::query()->firstOrCreate(
                    [
                        'company_id' => $ctx->company->id,
                        'branch_id' => $ctx->branch->id,
                        'employee_id' => $employee->id,
                        'attendance_date' => $date->toDateString(),
                    ],
                    [
                        'clock_in_at' => $date->copy()->setTime(8, 5),
                        'clock_out_at' => $date->copy()->setTime(17, 10),
                        'actual_hours' => 8.5,
                        'scheduled_hours' => 8,
                        'status' => AttendanceStatus::Present,
                    ],
                );
            }

            if ($leaveType && $index < 3) {
                $start = $ctx->dateInPeriod(30 + ($index * 10))->toDateString();

                LeaveRequest::query()->firstOrCreate(
                    [
                        'company_id' => $ctx->company->id,
                        'employee_id' => $employee->id,
                        'start_date' => $start,
                    ],
                    [
                        'branch_id' => $ctx->branch->id,
                        'leave_type_id' => $leaveType->id,
                        'end_date' => Carbon::parse($start)->addDays(3)->toDateString(),
                        'days_requested' => 4,
                        'reason' => 'Family commitment — demo leave request',
                        'status' => LeaveRequestStatus::Approved,
                        'hr_approved_by_user_id' => $ctx->admin->id,
                        'hr_approved_at' => Carbon::parse($start)->subDays(5),
                    ],
                );
            }
        });
    }

    protected function seedAssets(OperationalDemoContext $ctx): void
    {
        $category = AssetCategory::query()->where('code', 'PRINT')->first()
            ?? AssetCategory::query()->first();

        if (! $category) {
            return;
        }

        $assets = [
            ['number' => 'FA-PR-001', 'name' => 'Canon imagePRESS C910', 'serial' => 'CN910-DEMO-001'],
            ['number' => 'FA-PR-002', 'name' => 'Roland VersaCAMM VS-640i', 'serial' => 'RL640-DEMO-002'],
            ['number' => 'FA-VH-001', 'name' => 'Delivery Van — KCA 123A', 'serial' => 'VH-DEMO-001'],
        ];

        foreach ($assets as $index => $row) {
            $cost = random_int(800000, 3500000);

            FixedAsset::query()->firstOrCreate(
                ['company_id' => $ctx->company->id, 'asset_number' => $row['number']],
                [
                    'branch_id' => $ctx->branch->id,
                    'asset_category_id' => $category->id,
                    'acquisition_source' => AssetAcquisitionSource::Manual,
                    'asset_name' => $row['name'],
                    'serial_number' => $row['serial'],
                    'acquisition_date' => $ctx->periodStart->copy()->subYears(2)->addMonths($index)->toDateString(),
                    'capitalization_date' => $ctx->periodStart->copy()->subYears(2)->addMonths($index)->toDateString(),
                    'acquisition_cost' => $cost,
                    'residual_value' => 50000,
                    'useful_life_years' => 5,
                    'depreciation_method' => DepreciationMethod::StraightLine,
                    'depreciation_start_date' => $ctx->periodStart->copy()->subYears(2)->addMonths($index)->toDateString(),
                    'accumulated_depreciation' => round($cost * 0.2, 2),
                    'net_book_value' => round($cost * 0.8, 2),
                    'status' => FixedAssetStatus::Active,
                ],
            );
        }
    }

    protected function seedPos(OperationalDemoContext $ctx): void
    {
        $cashier = $ctx->salesUser ?? $ctx->admin;

        for ($sessionIndex = 0; $sessionIndex < 3; $sessionIndex++) {
            $openedAt = $ctx->dateInPeriod(20 + ($sessionIndex * 20), 8);
            $closedAt = $openedAt->copy()->setTime(18, 0);

            $session = PosSession::query()->create([
                'company_id' => $ctx->company->id,
                'branch_id' => $ctx->branch->id,
                'cashier_id' => $cashier->id,
                'session_number' => $ctx->nextNumber('DEMO-POS-SES'),
                'terminal' => 'Front Counter '.($sessionIndex + 1),
                'opening_float' => 5000,
                'opening_cash' => 5000,
                'expected_cash' => 15000 + ($sessionIndex * 2000),
                'expected_mpesa' => 8000,
                'expected_card' => 5000,
                'expected_bank' => 0,
                'expected_total' => 28000 + ($sessionIndex * 2000),
                'actual_cash' => 14950 + ($sessionIndex * 2000),
                'variance' => -50,
                'variance_requires_approval' => false,
                'status' => PosSessionStatus::Closed,
                'opened_at' => $openedAt,
                'closed_at' => $closedAt,
                'opened_by' => $cashier->id,
                'closed_by' => $cashier->id,
            ]);

            for ($saleIndex = 0; $saleIndex < 5; $saleIndex++) {
                $product = $this->printProducts[$saleIndex % count($this->printProducts)];
                $qty = max(1, (int) ($product['qty'] / 100));
                $lineTotal = round($qty * $product['price'], 2);
                $tax = round($lineTotal * 0.16, 2);
                $total = $lineTotal + $tax;
                $soldAt = $openedAt->copy()->addHours(2 + $saleIndex);

                $sale = PosSale::query()->create([
                    'company_id' => $ctx->company->id,
                    'branch_id' => $ctx->branch->id,
                    'pos_session_id' => $session->id,
                    'cashier_id' => $cashier->id,
                    'sale_number' => $ctx->nextNumber('DEMO-POS-SALE'),
                    'sale_date' => $soldAt->toDateString(),
                    'status' => PosSaleStatus::Paid,
                    'subtotal' => $lineTotal,
                    'tax_amount' => $tax,
                    'discount_amount' => 0,
                    'total_amount' => $total,
                    'amount_paid' => $total,
                    'balance_due' => 0,
                    'is_walk_in' => true,
                ]);

                PosSaleItem::query()->create([
                    'pos_sale_id' => $sale->id,
                    'description' => $product['title'],
                    'quantity' => $qty,
                    'unit_price' => $product['price'],
                    'line_total' => $lineTotal,
                ]);

                PosPayment::query()->create([
                    'pos_sale_id' => $sale->id,
                    'payment_method' => $saleIndex % 2 === 0 ? PosPaymentMethod::Cash : PosPaymentMethod::Mpesa,
                    'amount' => $total,
                    'reference' => $saleIndex % 2 === 0 ? null : 'MPX'.fake()->numerify('######'),
                ]);
            }
        }
    }

    protected function seedDemoArtworkVersion(ArtworkRequest $artwork, int $uploadedBy): void
    {
        $path = "artwork/{$artwork->company_id}/{$artwork->id}/versions/demo-v1.pdf";
        Storage::disk('local')->put($path, '%PDF-1.4 demo artwork placeholder');

        ArtworkVersion::query()->create([
            'artwork_request_id' => $artwork->id,
            'version_number' => 1,
            'file_path' => $path,
            'original_name' => 'demo-artwork-v1.pdf',
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('local')->size($path),
            'uploaded_by' => $uploadedBy,
            'notes' => 'Demo artwork file',
        ]);
    }
}
