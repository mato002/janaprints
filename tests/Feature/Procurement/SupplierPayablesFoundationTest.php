<?php

namespace Tests\Feature\Procurement;

use App\Enums\SupplierBillLineType;
use App\Enums\SupplierBillStatus;
use App\Enums\SupplierPaymentMethod;
use App\Enums\SupplierPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Procurement\SupplierAgingService;
use App\Support\Procurement\SupplierBillService;
use App\Support\Procurement\SupplierPaymentService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\SupplierPayablesPostingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPayablesFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(SupplierPayablesPostingSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        $this->vendor = Vendor::factory()->create([
            'company_id' => $this->company->id,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_supplier_bill_posts_to_accounts_payable(): void
    {
        $billService = app(SupplierBillService::class);

        $bill = $billService->createBill([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $this->vendor->id,
            'bill_date' => now()->toDateString(),
            'currency' => 'KES',
        ], [
            [
                'item_name' => 'Paper stock',
                'line_type' => SupplierBillLineType::Inventory->value,
                'quantity' => 10,
                'unit_cost' => 100,
                'tax_rate' => 0,
            ],
            [
                'item_name' => 'Courier fee',
                'line_type' => SupplierBillLineType::Expense->value,
                'quantity' => 1,
                'unit_cost' => 50,
                'tax_rate' => 0,
            ],
        ], $this->user->id);

        $billService->approve($bill, $this->user->id);
        $bill = $billService->post($bill->fresh(), $this->user->id);

        $this->assertEquals(SupplierBillStatus::Posted, $bill->status);
        $this->assertNotNull($bill->posted_journal_id);
        $this->assertEquals(1050.0, (float) $bill->total_amount);
        $this->assertEquals(1050.0, (float) $bill->balance_due);

        $journal = Journal::query()->find($bill->posted_journal_id);
        $this->assertNotNull($journal);
        $this->assertEquals('supplier_bill.posted', $journal->posting_event);
    }

    public function test_supplier_payment_allocates_and_marks_bill_paid(): void
    {
        $billService = app(SupplierBillService::class);
        $paymentService = app(SupplierPaymentService::class);

        $bill = $billService->createBill([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $this->vendor->id,
            'bill_date' => now()->toDateString(),
            'currency' => 'KES',
        ], [
            ['item_name' => 'Supplies', 'line_type' => 'inventory', 'quantity' => 1, 'unit_cost' => 500, 'tax_rate' => 0],
        ], $this->user->id);

        $billService->approve($bill, $this->user->id);
        $bill = $billService->post($bill->fresh(), $this->user->id);

        $payment = $paymentService->create($this->vendor, $this->user->id, [
            'branch_id' => $this->branch->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => SupplierPaymentMethod::Bank,
            'amount' => 500,
            'allocations' => [
                ['supplier_bill_id' => $bill->id, 'amount' => 500],
            ],
        ]);

        $payment = $paymentService->post($payment, $this->user->id);

        $bill->refresh();
        $this->assertEquals(SupplierPaymentStatus::Posted, $payment->status);
        $this->assertEquals(SupplierBillStatus::Paid, $bill->status);
        $this->assertEquals(0.0, (float) $bill->balance_due);
    }

    public function test_aging_includes_outstanding_bill(): void
    {
        $billService = app(SupplierBillService::class);
        $bill = $billService->createBill([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $this->vendor->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->subDays(45)->toDateString(),
            'currency' => 'KES',
        ], [
            ['item_name' => 'Late bill', 'line_type' => 'expense', 'quantity' => 1, 'unit_cost' => 300, 'tax_rate' => 0],
        ], $this->user->id);
        $billService->approve($bill, $this->user->id);
        $billService->post($bill->fresh(), $this->user->id);

        $aging = app(SupplierAgingService::class)->build([
            'company_id' => $this->company->id,
            'as_of_date' => now()->toDateString(),
        ]);

        $this->assertGreaterThan(0, $aging['grand_total']);
    }

    public function test_payables_routes_require_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)
            ->get(route('admin.payables.bills.index'))
            ->assertForbidden();
    }
}
