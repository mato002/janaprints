<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\ProductionJobCardService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionOperatorActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_pause_and_resume_job_from_production_floor_flow(): void
    {
        [$user, $jobCard] = $this->jobContext();

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.pause', $jobCard))
            ->assertRedirect();

        $jobCard->refresh();
        $this->assertSame(ProductionJobCardStatus::OnHold, $jobCard->status);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.resume', $jobCard))
            ->assertRedirect();

        $this->assertSame(ProductionJobCardStatus::InProduction, $jobCard->fresh()->status);
    }

    public function test_job_scan_label_renders_barcode_and_qr(): void
    {
        [$user, $jobCard] = $this->jobContext();

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.label', ['jobCard' => $jobCard, 'embedded' => 1]))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_scan_route_redirects_to_department_queue(): void
    {
        [$user, $jobCard] = $this->jobContext();

        $this->actingAs($user)
            ->get(route('admin.production.scan.show', ['code' => $jobCard->job_card_number]))
            ->assertRedirect();
    }

    public function test_status_transition_writes_audit_log(): void
    {
        [$user, $jobCard] = $this->jobContext();

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.hold', $jobCard))
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'production_job_status_changed',
            'model_id' => $jobCard->id,
        ]);
    }

    /**
     * @return array{0: User, 1: ProductionJobCard}
     */
    protected function jobContext(): array
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

        $role = Role::create(['name' => 'Production Ops '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions([
            'production.view', 'production.start', 'production.edit', 'production.complete', 'production.schedule',
        ]);
        $user->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

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
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $user->id);
        $jobCard->update([
            'status' => ProductionJobCardStatus::InProduction,
            'actual_start_date' => now(),
        ]);

        return [$user, $jobCard->fresh()];
    }
}
