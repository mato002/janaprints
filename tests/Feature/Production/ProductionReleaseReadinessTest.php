<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\ProductionReleaseReadinessService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionReleaseReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_release_blocked_without_production_specification_source(): void
    {
        [$user, $order] = $this->bareOrder();

        $this->actingAs($user)
            ->post(route('admin.sales-orders.release-to-production', $order))
            ->assertSessionHasErrors('production_spec');

        $this->assertDatabaseMissing('production_job_cards', ['sales_order_id' => $order->id]);
    }

    public function test_release_blocked_when_spec_exists_but_not_approved(): void
    {
        [$user, $order] = $this->orderWithSpec(ProductionSpecificationApprovalStatus::Draft);

        $assessment = app(ProductionReleaseReadinessService::class)->assess($order->fresh(), $user);
        $this->assertFalse($assessment['ready']);
    }

    public function test_draft_job_without_materials_blocks_readiness(): void
    {
        [$user, $order] = $this->orderWithSpec(ProductionSpecificationApprovalStatus::Approved);

        ProductionJobCard::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
        ]);

        $assessment = app(ProductionReleaseReadinessService::class)->assess($order->fresh(['jobCard']), $user);

        $this->assertFalse($assessment['ready']);
        $materialsCheck = collect($assessment['checks'])->firstWhere('key', 'materials');
        $this->assertNotNull($materialsCheck);
        $this->assertFalse($materialsCheck['passed']);
    }

    public function test_assess_ready_when_prerequisites_pass_without_draft_job(): void
    {
        [$user, $order] = $this->orderWithSpec(ProductionSpecificationApprovalStatus::Approved);

        $assessment = app(ProductionReleaseReadinessService::class)->assess($order->fresh(), $user);

        $this->assertTrue($assessment['ready']);
        $materialsCheck = collect($assessment['checks'])->firstWhere('key', 'materials');
        $this->assertSame('warning', $materialsCheck['severity'] ?? null);
    }

    public function test_sales_desk_materials_handoff_returns_modal_panel(): void
    {
        [$user, $order] = $this->orderWithSpec(ProductionSpecificationApprovalStatus::Approved);

        ProductionJobCard::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get(route('admin.sales.desk.materials', $order))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Material shortages'), false);
    }

    /**
     * @return array{0: User, 1: SalesOrder}
     */
    protected function bareOrder(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Production Release '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['sales_orders.view', 'sales_orders.production']);
        $user->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => null,
            'artwork_request_id' => null,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        return [$user, $order];
    }

    protected function orderWithSpec(ProductionSpecificationApprovalStatus $approval): array
    {
        [$user, $order] = $this->bareOrder();

        $quotation = Quotation::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Converted,
        ]);

        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'quotation_id' => $quotation->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
        ]);

        $order->update([
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
        ]);

        ProductionSpecification::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'approval_status' => $approval,
        ]);

        return [$user, $order->fresh()];
    }
}
