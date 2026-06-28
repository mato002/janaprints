<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationRevision;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_company_isolation_for_quotations(): void
    {
        $companyA = Company::factory()->create(['code' => 'QA']);
        $companyB = Company::factory()->create(['code' => 'QB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'BA']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'BB']);
        $customerB = Customer::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_code' => 'C-001',
            'company_name' => 'Other',
            'status' => CustomerStatus::Active,
        ]);

        $salesA = $this->salesUser($companyA, $branchA, ['quotations.view']);
        $quotationB = $this->makeQuotation($companyB, $branchB, $customerB);

        $this->actingAs($salesA)
            ->get(route('admin.quotations.show', $quotationB))
            ->assertForbidden();
    }

    public function test_sales_user_can_create_quotation(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('admin.quotations.store'), [
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'currency' => 'KES',
            'items' => [
                [
                    'item_type' => 'product',
                    'item_name' => 'Banner Print',
                    'quantity' => 2,
                    'unit_price' => 5000,
                    'discount' => 0,
                    'tax_rate' => 16,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotations', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::Sent->value,
        ]);
        $this->assertDatabaseHas('quotation_items', ['item_name' => 'Banner Print']);
        $this->assertDatabaseHas('quotation_revisions', ['revision_number' => 1]);
    }

    public function test_quotation_store_from_modal_returns_success_marker(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.quotations.store'), [
                '_erp_modal' => '1',
                'customer_id' => $customer->id,
                'quotation_date' => now()->toDateString(),
                'currency' => 'KES',
                'items' => [
                    [
                        'item_type' => 'product',
                        'item_name' => 'Modal Banner Print',
                        'quantity' => 1,
                        'unit_price' => 2500,
                        'discount' => 0,
                        'tax_rate' => 16,
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $this->assertDatabaseHas('quotations', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_viewer_cannot_create_quotation(): void
    {
        [$company, $branch, , $user] = $this->salesContext(['quotations.view']);

        $this->actingAs($user)
            ->get(route('admin.quotations.create'))
            ->assertForbidden();
    }

    public function test_status_transition_submit_and_approve(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'quotations.create', 'quotations.edit',
            'quotations.approve', 'quotations.send',
        ]);

        $quotation = $this->makeQuotation($company, $branch, $customer, $user);

        $this->actingAs($user)
            ->post(route('admin.quotations.submit-approval', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertEquals(QuotationStatus::PendingApproval, $quotation->status);

        $this->actingAs($user)
            ->post(route('admin.quotations.approve', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertEquals(QuotationStatus::Sent, $quotation->status);
        $this->assertNotNull($quotation->approved_at);
    }

    public function test_update_creates_new_revision(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'quotations.create', 'quotations.edit',
        ]);

        $quotation = $this->makeQuotation($company, $branch, $customer, $user);

        $this->actingAs($user)->put(route('admin.quotations.update', $quotation), [
            'customer_id' => $customer->id,
            'quotation_date' => $quotation->quotation_date->format('Y-m-d'),
            'currency' => 'KES',
            'items' => [
                [
                    'item_type' => 'service',
                    'item_name' => 'Updated Item',
                    'quantity' => 1,
                    'unit_price' => 10000,
                    'discount' => 0,
                    'tax_rate' => 0,
                ],
            ],
        ])->assertRedirect();

        $quotation->refresh();
        $this->assertEquals(2, $quotation->revision_number);
        $this->assertEquals(2, QuotationRevision::query()->where('quotation_id', $quotation->id)->count());
        $this->assertDatabaseHas('quotation_items', ['item_name' => 'Updated Item']);
    }

    protected function salesContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-00001',
            'company_name' => 'Test Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['quotations.view', 'quotations.create', 'quotations.edit'];
        $user = $this->salesUser($company, $branch, $permissions);

        return [$company, $branch, $customer, $user];
    }

    protected function salesUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return $user;
    }

    protected function makeQuotation(Company $company, Branch $branch, Customer $customer, ?User $preparer = null): Quotation
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $preparer?->id ?? User::factory()->create()->id,
            'status' => QuotationStatus::Draft,
            'revision_number' => 1,
        ]);

        $quotation->items()->create([
            'item_type' => 'product',
            'item_name' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 1000,
            'discount' => 0,
            'tax_rate' => 0,
            'line_total' => 1000,
            'sort_order' => 0,
        ]);

        return $quotation;
    }
}
