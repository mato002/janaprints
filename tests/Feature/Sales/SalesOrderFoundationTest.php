<?php

namespace Tests\Feature\Sales;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationConversion;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesOrderFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_company_isolation_for_sales_orders(): void
    {
        $companyA = Company::factory()->create(['code' => 'SOA']);
        $companyB = Company::factory()->create(['code' => 'SOB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'SBA']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'SBB']);

        $userA = $this->salesUser($companyA, $branchA, ['sales_orders.view']);
        $orderB = SalesOrder::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
        ]);

        $this->actingAs($userA)
            ->get(route('admin.sales-orders.show', $orderB))
            ->assertForbidden();
    }

    public function test_viewer_cannot_create_sales_order(): void
    {
        [, , , $user] = $this->salesContext(['sales_orders.view']);

        $this->actingAs($user)
            ->get(route('admin.sales-orders.create'))
            ->assertForbidden();
    }

    public function test_conversion_blocked_without_approved_artwork(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'quotations.convert', 'sales_orders.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $quotation = $this->makeAcceptedQuotation($company, $branch, $customer, $user);

        $this->actingAs($user)
            ->post(route('admin.quotations.convert', $quotation))
            ->assertSessionHasErrors('artwork');

        $this->assertDatabaseMissing('sales_orders', ['quotation_id' => $quotation->id]);
        $this->assertEquals(QuotationStatus::Accepted, $quotation->fresh()->status);
    }

    public function test_quotation_conversion_creates_sales_order_with_history(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'sales_orders.view', 'sales_orders.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $quotation = $this->makeAcceptedQuotation($company, $branch, $customer, $user);
        $this->attachApprovedArtwork($quotation, $user);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.sales-orders.store'), [
                'quotation_id' => $quotation->id,
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Sales order created from quotation.'), false);

        $this->assertDatabaseHas('sales_orders', [
            'quotation_id' => $quotation->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_create_form_only_lists_quotations_with_approved_artwork(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'sales_orders.view', 'sales_orders.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $readyQuotation = $this->makeAcceptedQuotation($company, $branch, $customer, $user);
        $this->attachApprovedArtwork($readyQuotation, $user);

        $blockedQuotation = $this->makeAcceptedQuotation($company, $branch, $customer, $user);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create'))
            ->assertOk()
            ->assertSee($readyQuotation->quotation_number, false)
            ->assertDontSee($blockedQuotation->quotation_number, false);
    }

    public function test_conversion_uses_approved_artwork_when_multiple_are_linked(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'sales_orders.view', 'sales_orders.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $quotation = $this->makeAcceptedQuotation($company, $branch, $customer, $user);

        ArtworkRequest::factory()->create([
            'company_id' => $quotation->company_id,
            'branch_id' => $quotation->branch_id,
            'customer_id' => $quotation->customer_id,
            'quotation_id' => $quotation->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::InDesign,
            'current_version' => 1,
        ]);

        $approvedArtwork = $this->attachApprovedArtwork($quotation, $user);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.sales-orders.store'), [
                'quotation_id' => $quotation->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('sales_orders', [
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $approvedArtwork->id,
        ]);
    }

    public function test_convert_shows_button_but_blocks_when_sales_order_already_exists(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'quotations.view', 'quotations.convert', 'sales_orders.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $quotation = $this->makeAcceptedQuotation($company, $branch, $customer, $user);
        $this->attachApprovedArtwork($quotation, $user);

        SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.quotations.show', $quotation))
            ->assertOk()
            ->assertSee(__('Convert to sales order'), false);

        $this->actingAs($user)
            ->post(route('admin.quotations.convert', $quotation))
            ->assertRedirect()
            ->assertSessionHasErrors('quotation');
    }

    public function test_confirm_blocked_when_already_confirmed(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'sales_orders.view', 'sales_orders.confirm',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.confirm', $order))
            ->assertRedirect()
            ->assertSessionHasErrors('workflow');

        $this->assertEquals(SalesOrderStatus::Confirmed, $order->fresh()->status);
    }

    public function test_status_transition_confirm(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'sales_orders.view', 'sales_orders.confirm',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Draft,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.confirm', $order))
            ->assertRedirect();

        $this->assertEquals(SalesOrderStatus::Confirmed, $order->fresh()->status);
    }

    public function test_close_blocked_until_invoiced_and_paid(): void
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'sales_orders.view', 'sales_orders.close',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Delivered,
            'total_amount' => 1000,
            'invoiced_total' => 0,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.sales-orders.show', $order))
            ->assertOk()
            ->assertSee(__('Close order'), false);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.close', $order))
            ->assertRedirect()
            ->assertSessionHasErrors('workflow');

        $this->assertEquals(SalesOrderStatus::Delivered, $order->fresh()->status);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User}
     */
    protected function salesContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-SO-01',
            'company_name' => 'SO Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['sales_orders.view', 'sales_orders.create', 'sales_orders.edit'];
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

    protected function makeAcceptedQuotation(Company $company, Branch $branch, Customer $customer, User $user): Quotation
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Accepted,
            'revision_number' => 2,
        ]);

        $quotation->items()->create([
            'item_type' => 'product',
            'item_name' => 'Banner',
            'quantity' => 1,
            'unit_price' => 5000,
            'discount' => 0,
            'tax_rate' => 0,
            'line_total' => 5000,
            'sort_order' => 0,
        ]);

        return $quotation;
    }

    protected function attachApprovedArtwork(Quotation $quotation, User $user): ArtworkRequest
    {
        $artworkRequest = ArtworkRequest::factory()->create([
            'company_id' => $quotation->company_id,
            'branch_id' => $quotation->branch_id,
            'customer_id' => $quotation->customer_id,
            'quotation_id' => $quotation->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
            'current_version' => 1,
        ]);

        $version = ArtworkVersion::query()->create([
            'artwork_request_id' => $artworkRequest->id,
            'version_number' => 1,
            'file_path' => 'artwork/test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
        ]);

        ArtworkApproval::query()->create([
            'company_id' => $quotation->company_id,
            'branch_id' => $quotation->branch_id,
            'artwork_request_id' => $artworkRequest->id,
            'artwork_version_id' => $version->id,
            'approved_by' => $user->id,
            'decision' => ArtworkApprovalDecision::Approved,
        ]);

        return $artworkRequest;
    }
}
