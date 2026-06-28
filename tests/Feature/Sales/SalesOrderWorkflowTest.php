<?php

namespace Tests\Feature\Sales;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_confirm_auto_releases_to_production_when_eligible(): void
    {
        [, , , $user, $salesOrder] = $this->context([
            'sales_orders.view', 'sales_orders.confirm', 'sales_orders.production',
        ]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.confirm', $salesOrder))
            ->assertRedirect()
            ->assertSessionHas('status');

        $salesOrder->refresh();
        $this->assertEquals(SalesOrderStatus::ReadyForProduction, $salesOrder->status);
        $this->assertDatabaseHas('production_job_cards', [
            'sales_order_id' => $salesOrder->id,
            'status' => ProductionJobCardStatus::Draft->value,
        ]);
    }

    public function test_release_to_production_creates_job_card(): void
    {
        [, , , $user, $salesOrder] = $this->context([
            'sales_orders.view', 'sales_orders.production',
        ]);

        $salesOrder->update(['status' => SalesOrderStatus::Confirmed]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.release-to-production', $salesOrder))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('production_job_cards', ['sales_order_id' => $salesOrder->id]);
        $this->assertEquals(SalesOrderStatus::ReadyForProduction, $salesOrder->fresh()->status);
    }

    public function test_release_to_production_blocked_without_approved_artwork(): void
    {
        [$company, $branch, $customer, $user] = $this->context([
            'sales_orders.view', 'sales_orders.production',
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Converted,
        ]);

        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Submitted,
            'current_version' => 1,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.release-to-production', $salesOrder))
            ->assertSessionHasErrors('artwork');

        $this->assertDatabaseMissing('production_job_cards', ['sales_order_id' => $salesOrder->id]);
    }

    public function test_legacy_manual_production_routes_are_removed(): void
    {
        [, , , $user, $salesOrder] = $this->context([
            'sales_orders.view', 'sales_orders.production',
        ]);

        $this->actingAs($user)
            ->post('/admin/sales-orders/list/'.$salesOrder->id.'/start-production')
            ->assertNotFound();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder}
     */
    protected function context(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'C-WF-01',
            'company_name' => 'Workflow Customer',
            'status' => CustomerStatus::Active,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Sales Workflow Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Converted,
        ]);

        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
            'current_version' => 1,
        ]);

        ArtworkVersion::query()->create([
            'artwork_request_id' => $artwork->id,
            'version_number' => 1,
            'file_path' => 'test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
        ]);

        ArtworkApproval::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'artwork_request_id' => $artwork->id,
            'artwork_version_id' => $artwork->versions()->first()->id,
            'approved_by' => $user->id,
            'decision' => ArtworkApprovalDecision::Approved,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'status' => SalesOrderStatus::Draft,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $salesOrder];
    }
}
