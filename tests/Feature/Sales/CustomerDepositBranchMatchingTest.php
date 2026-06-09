<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerDepositApplication;
use App\Models\Sales\CustomerInvoice;
use App\Models\User;
use App\Support\Sales\CustomerDepositService;
use App\Support\Sales\CustomerInvoiceService;
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
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerDepositBranchMatchingTest extends TestCase
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

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branchA, false));
    }

    public function test_same_branch_deposit_application_succeeds(): void
    {
        $customer = $this->customerOnBranch($this->branchA);
        $invoice = $this->postInvoice($customer, $this->branchA, 1000);
        $deposit = $this->postDeposit($customer, $this->branchA, 500);

        $application = app(CustomerDepositService::class)->applyToInvoice(
            $deposit,
            $invoice,
            500,
            $this->user->id,
        );

        $this->assertSame($this->branchA->id, $application->source_branch_id);
        $this->assertSame($this->branchA->id, $application->target_branch_id);
        $this->assertFalse($application->is_cross_branch);
        $this->assertNull($application->override_reason);
        $this->assertSame($this->user->id, $application->created_by);
    }

    public function test_cross_branch_deposit_application_is_blocked_without_permission(): void
    {
        $restricted = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branchA->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $restricted->assignRole('Sales');

        $customer = $this->customerOnBranch($this->branchA);
        $invoice = $this->postInvoice($customer, $this->branchB, 1000);
        $deposit = $this->postDeposit($customer, $this->branchA, 500);

        $this->expectException(ValidationException::class);

        try {
            app(CustomerDepositService::class)->applyToInvoice(
                $deposit,
                $invoice,
                500,
                $restricted->id,
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('invoice', $exception->errors());

            throw $exception;
        }
    }

    public function test_cross_branch_deposit_application_allowed_with_finance_override(): void
    {
        Permission::findOrCreate('finance.cross_branch.allocate', 'web');
        $this->user->givePermissionTo('finance.cross_branch.allocate');

        $customer = $this->customerOnBranch($this->branchA);
        $invoice = $this->postInvoice($customer, $this->branchB, 1000);
        $deposit = $this->postDeposit($customer, $this->branchA, 500);

        $application = app(CustomerDepositService::class)->applyToInvoice(
            $deposit,
            $invoice,
            500,
            $this->user->id,
            ['override_reason' => 'Customer paid at HQ for Branch Two job'],
        );

        $this->assertSame($this->branchA->id, $application->source_branch_id);
        $this->assertSame($this->branchB->id, $application->target_branch_id);
        $this->assertTrue($application->is_cross_branch);
        $this->assertSame('Customer paid at HQ for Branch Two job', $application->override_reason);
        $this->assertEqualsWithDelta(500, (float) $invoice->fresh()->amount_paid, 0.01);
    }

    public function test_cross_branch_override_requires_reason(): void
    {
        Permission::findOrCreate('finance.cross_branch.allocate', 'web');
        $this->user->givePermissionTo('finance.cross_branch.allocate');

        $customer = $this->customerOnBranch($this->branchA);
        $invoice = $this->postInvoice($customer, $this->branchB, 1000);
        $deposit = $this->postDeposit($customer, $this->branchA, 500);

        $this->expectException(ValidationException::class);

        try {
            app(CustomerDepositService::class)->applyToInvoice(
                $deposit,
                $invoice,
                500,
                $this->user->id,
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('override_reason', $exception->errors());

            throw $exception;
        }
    }

    public function test_cross_branch_allocation_records_audit_trail(): void
    {
        Permission::findOrCreate('finance.cross_branch.allocate', 'web');
        $this->user->givePermissionTo('finance.cross_branch.allocate');
        $this->actingAs($this->user);

        $customer = $this->customerOnBranch($this->branchA);
        $invoice = $this->postInvoice($customer, $this->branchB, 1000);
        $deposit = $this->postDeposit($customer, $this->branchA, 500);
        $reason = 'Central treasury allocation approved by CFO';

        $application = app(CustomerDepositService::class)->applyToInvoice(
            $deposit,
            $invoice,
            500,
            $this->user->id,
            ['override_reason' => $reason],
        );

        $this->assertDatabaseHas('customer_deposit_applications', [
            'id' => $application->id,
            'source_branch_id' => $this->branchA->id,
            'target_branch_id' => $this->branchB->id,
            'is_cross_branch' => true,
            'override_reason' => $reason,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'cross_branch_deposit_application',
            'model_type' => CustomerDepositApplication::class,
            'model_id' => $application->id,
            'user_id' => $this->user->id,
        ]);
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
        $order = \App\Models\Sales\SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => $subtotal,
            'tax_amount' => round($subtotal * 0.16, 2),
            'total_amount' => round($subtotal * 1.16, 2),
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

        return $service->post($invoice->fresh(), $this->user->id);
    }

    protected function postDeposit(Customer $customer, Branch $branch, float $amount): \App\Models\Sales\CustomerPayment
    {
        if ($customer->branch_id !== $branch->id) {
            $customer->update(['branch_id' => $branch->id]);
        }

        $payment = app(CustomerPaymentService::class)->create($customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => $amount,
            'is_deposit' => true,
            'allocations' => [],
        ]);

        return app(CustomerPaymentService::class)->post($payment, $this->user->id);
    }
}
