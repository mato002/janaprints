<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Models\UserBranch;
use App\Support\Sales\AccountsReceivableReconciliationService;
use App\Support\Sales\CustomerAgingService;
use App\Support\Sales\CustomerDepositService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerLedgerService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReceivablesBranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branchA;

    protected Branch $branchB;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branchA = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        $this->branchB = Branch::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'BR2',
            'name' => 'Branch Two',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branchA->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Accountant');
    }

    public function test_branch_a_aging_excludes_branch_b_receivables(): void
    {
        $customerA = $this->customerOnBranch($this->branchA);
        $customerB = $this->customerOnBranch($this->branchB);

        $invoiceA = $this->postInvoice($customerA, $this->branchA, 1000);
        $invoiceB = $this->postInvoice($customerB, $this->branchB, 2000);

        $this->setTenantBranch($this->branchA->id);

        $aging = app(CustomerAgingService::class)->build(['company_id' => $this->company->id]);

        $this->assertEqualsWithDelta(1160, $aging['grand_total'], 0.01);
        $this->assertCount(1, $aging['rows']);
        $this->assertSame($customerA->id, $aging['rows'][0]['customer_id']);

        $this->assertFalse(collect($aging['rows'])->pluck('customer_id')->contains($customerB->id));
        $this->assertNotEquals($invoiceB->balance_due, $aging['grand_total']);
    }

    public function test_branch_b_aging_isolated_from_branch_a(): void
    {
        $customerA = $this->customerOnBranch($this->branchA);
        $customerB = $this->customerOnBranch($this->branchB);

        $this->postInvoice($customerA, $this->branchA, 1500);
        $this->postInvoice($customerB, $this->branchB, 2500);

        $this->setTenantBranch($this->branchB->id);

        $aging = app(CustomerAgingService::class)->build(['company_id' => $this->company->id]);

        $this->assertEqualsWithDelta(2900, $aging['grand_total'], 0.01);
        $this->assertSame($customerB->id, $aging['rows'][0]['customer_id']);
    }

    public function test_hq_consolidated_aging_includes_all_branches(): void
    {
        $this->user->givePermissionTo('reports.consolidated.view');

        $customerA = $this->customerOnBranch($this->branchA);
        $customerB = $this->customerOnBranch($this->branchB);

        $this->postInvoice($customerA, $this->branchA, 1000);
        $this->postInvoice($customerB, $this->branchB, 2000);

        $this->setTenantBranch(null);

        $aging = app(CustomerAgingService::class)->build(['company_id' => $this->company->id]);

        $this->assertEqualsWithDelta(3480, $aging['grand_total'], 0.01);
        $this->assertCount(2, $aging['rows']);
    }

    public function test_branch_ledger_excludes_other_branch_transactions(): void
    {
        $customer = $this->customerOnBranch($this->branchA);

        $invoice = $this->postInvoice($customer, $this->branchA, 1000);

        $payment = app(CustomerPaymentService::class)->create($customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => 300,
            'allocations' => [
                ['customer_invoice_id' => $invoice->id, 'amount' => 300],
            ],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $this->setTenantBranch($this->branchA->id);
        $ledgerA = app(CustomerLedgerService::class)->build(['customer_id' => $customer->id]);
        $this->assertCount(2, $ledgerA['entries']);

        $this->setTenantBranch($this->branchB->id);
        $ledgerB = app(CustomerLedgerService::class)->build(['customer_id' => $customer->id]);
        $this->assertCount(0, $ledgerB['entries']);
        $this->assertEqualsWithDelta(0, $ledgerB['closing_balance'], 0.01);
    }

    public function test_reconciliation_operational_ar_is_branch_scoped(): void
    {
        $customerA = $this->customerOnBranch($this->branchA);
        $customerB = $this->customerOnBranch($this->branchB);

        $this->postInvoice($customerA, $this->branchA, 1000);
        $this->postInvoice($customerB, $this->branchB, 4000);

        $this->setTenantBranch($this->branchA->id);

        $report = app(AccountsReceivableReconciliationService::class)->build([
            'company_id' => $this->company->id,
        ]);

        $this->assertSame($this->branchA->id, $report['branch_id']);
        $this->assertEqualsWithDelta(1160, $report['summary']['operational_ar'], 0.01);
    }

    public function test_cross_branch_deposit_application_is_blocked(): void
    {
        $restricted = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branchA->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $restricted->assignRole('Sales');

        $customer = $this->customerOnBranch($this->branchA);
        $invoice = $this->postInvoice($customer, $this->branchA, 1000);
        $invoice->update(['branch_id' => $this->branchB->id]);

        $deposit = app(CustomerPaymentService::class)->create($customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => 500,
            'is_deposit' => true,
            'allocations' => [],
        ]);
        $deposit = app(CustomerPaymentService::class)->post($deposit, $this->user->id);

        $this->expectException(ValidationException::class);

        app(CustomerDepositService::class)->applyToInvoice(
            $deposit,
            $invoice->fresh(),
            500,
            $restricted->id,
        );
    }

    public function test_receivables_aging_requires_permission(): void
    {
        $restricted = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branchA->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        UserBranch::query()->firstOrCreate(
            [
                'user_id' => $restricted->id,
                'branch_id' => $this->branchA->id,
            ],
            [
                'is_primary' => true,
                'is_active' => true,
            ],
        );
        $role = Role::create(['name' => 'no-receivables-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo('crm.customers.view');
        $restricted->assignRole($role);

        $this->setTenantBranch($this->branchA->id);
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branchA->id]);

        $this->actingAs($restricted)
            ->get(route('admin.receivables.aging'))
            ->assertForbidden();
    }

    public function test_accountant_can_view_branch_scoped_aging(): void
    {
        $customer = $this->customerOnBranch($this->branchA);
        $this->postInvoice($customer, $this->branchA, 800);

        $this->setTenantBranch($this->branchA->id);
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branchA->id]);

        $this->actingAs($this->user)
            ->get(route('admin.receivables.aging'))
            ->assertOk()
            ->assertSee($customer->company_name, false);
    }

    protected function customerOnBranch(Branch $branch): Customer
    {
        return Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $branch->id,
        ]);
    }

    protected function postInvoice(Customer $customer, Branch $branch, float $subtotal): CustomerInvoice
    {
        $taxAmount = round($subtotal * 0.16, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'created_by' => $this->user->id,
        ]);
        $order->items()->create([
            'item_name' => 'Print job',
            'quantity' => 1,
            'unit_price' => $subtotal,
            'line_total' => $subtotal,
            'sort_order' => 1,
        ]);

        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($order, $this->user->id);
        $service->approve($invoice, $this->user->id);
        $service->post($invoice->fresh(), $this->user->id);

        return $invoice->fresh();
    }

    protected function setTenantBranch(?int $branchId): void
    {
        $branch = $branchId ? Branch::query()->find($branchId) : null;
        app()->instance(TenantContext::class, new TenantContext($this->company, $branch, false));
    }
}
