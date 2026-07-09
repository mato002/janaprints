<?php

namespace Tests\Feature\Dispatch;

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
use App\Services\Dispatch\DeliveryNoteAuthority;
use App\Support\ProductionJobCardService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliveryNoteIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_duplicate_delivery_note_creation_returns_existing_note(): void
    {
        [$user, $jobCard] = $this->jobContext();
        $authority = app(DeliveryNoteAuthority::class);

        $first = $authority->createDraftFromJobCard($jobCard, ['recipient_name' => 'Jane Client'], [
            ['description' => 'Test deliverable', 'quantity' => 1, 'unit' => 'pcs'],
        ]);
        $second = $authority->createDraftFromJobCard($jobCard->fresh(), ['recipient_name' => 'Jane Client'], [
            ['description' => 'Test deliverable', 'quantity' => 1, 'unit' => 'pcs'],
        ]);

        $this->assertFalse($first->wasExisting);
        $this->assertTrue($second->wasExisting);
        $this->assertSame($first->note->id, $second->note->id);
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

        $role = Role::create(['name' => 'Dispatch Idempotency '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['dispatch.create', 'dispatch.view', 'sales_orders.production']);
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
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        return [$user, $jobCard->fresh()];
    }
}
